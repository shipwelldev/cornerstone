<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function runReleaseArchiveCommand(array $command, string $workingDirectory, array $environment = []): string
{
    $validatedCommand = [];

    foreach ($command as $argument) {
        if ( ! is_string($argument)) {
            throw new RuntimeException('Release archive commands may only contain strings.');
        }

        $validatedCommand[] = $argument;
    }

    $validatedEnvironment = [];

    foreach ($environment as $name => $value) {
        if ( ! is_string($name) || ( ! is_string($value) && ! $value instanceof Stringable && $value !== false)) {
            throw new RuntimeException('Release archive environment variables must be valid process values.');
        }

        $validatedEnvironment[$name] = $value;
    }

    $process = new Process($validatedCommand, $workingDirectory, $validatedEnvironment);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());

    return mb_trim($process->getOutput());
}

function buildReleaseArchiveFromWorkingTree(): string
{
    $repository = dirname(__DIR__, 2);
    $temporaryDirectory = sys_get_temp_dir() . '/cornerstone-release-' . bin2hex(random_bytes(8));
    $exportDirectory = $temporaryDirectory . '/export';
    $index = $temporaryDirectory . '/index';
    $archive = $temporaryDirectory . '/release.tar';

    mkdir($temporaryDirectory, 0700);
    mkdir($exportDirectory);

    $environment = ['GIT_INDEX_FILE' => $index];

    runReleaseArchiveCommand(['git', 'read-tree', 'HEAD'], $repository, $environment);
    runReleaseArchiveCommand(['git', 'add', '--all'], $repository, $environment);
    $tree = runReleaseArchiveCommand(['git', 'write-tree'], $repository, $environment);
    runReleaseArchiveCommand(['git', 'archive', '--format=tar', '--output=' . $archive, $tree], $repository, $environment);
    runReleaseArchiveCommand(['tar', '--extract', '--file=' . $archive, '--directory=' . $exportDirectory], $repository);

    return $exportDirectory;
}

test('release archives contain application files and exclude repository maintenance files', function (): void {
    $exportDirectory = buildReleaseArchiveFromWorkingTree();

    try {
        $includedFiles = [
            'README.md',
            'LICENSE.md',
            'composer.json',
            'composer.lock',
            'CODING_STANDARDS.md',
            'phpunit.xml',
            'tests/Pest.php',
            'tests/TestCase.php',
            'tests/Browser/CanonicalExampleTest.php',
            'tests/Feature/HomePageTest.php',
            'tests/Unit/ArchitectureTest.php',
            '.ai/skills/remove-example/SKILL.md',
            '.github/dependabot.yml',
            '.github/workflows/tests.yml',
        ];

        foreach ($includedFiles as $file) {
            expect($exportDirectory . '/' . $file)->toBeFile("Expected {$file} to be included in the release archive.");
        }

        $excludedPaths = [
            'CHANGELOG.md',
            'CONTRIBUTING.md',
            'phpunit.maintenance.xml',
            'tests/Maintenance',
            '.github/workflows/maintenance.yml',
            '.github/workflows/issues.yml',
            '.github/workflows/pull-requests.yml',
            '.github/workflows/release-smoke.yml',
            '.github/workflows/update-changelog.yml',
        ];

        foreach ($excludedPaths as $path) {
            expect(file_exists($exportDirectory . '/' . $path))->toBeFalse("Expected {$path} to be excluded from the release archive.");
        }
    } finally {
        removeTemporaryDirectory(dirname($exportDirectory));
    }
});

test('relative Markdown file links in the exported README resolve', function (): void {
    $exportDirectory = buildReleaseArchiveFromWorkingTree();

    try {
        $readme = file_get_contents($exportDirectory . '/README.md');

        if ($readme === false) {
            throw new RuntimeException('Unable to read the exported README.');
        }

        preg_match_all('/\[[^]]*]\(([^)]+)\)/', $readme, $matches);

        $relativeLinks = collect($matches[1])
            ->reject(fn (string $link): bool => str_starts_with($link, '#') || preg_match('/^[a-z][a-z0-9+.-]*:/i', $link) === 1)
            ->map(fn (string $link): string => rawurldecode(explode('#', explode('?', $link, 2)[0], 2)[0]))
            ->filter();

        expect($relativeLinks)->not->toBeEmpty();

        foreach ($relativeLinks as $link) {
            expect(file_exists($exportDirectory . '/' . $link))->toBeTrue("README link does not resolve in the release archive: {$link}");
        }
    } finally {
        removeTemporaryDirectory(dirname($exportDirectory));
    }
});

test('exported applications publish Pest Agent guidance without starting browser services', function (): void {
    $exportDirectory = buildReleaseArchiveFromWorkingTree();

    try {
        $repository = dirname(__DIR__, 2);

        if ( ! symlink($repository . '/vendor', $exportDirectory . '/vendor')) {
            throw new RuntimeException('Unable to link installed dependencies into the release archive.');
        }

        foreach (['packages.php', 'services.php'] as $cacheFile) {
            if ( ! copy($repository . '/bootstrap/cache/' . $cacheFile, $exportDirectory . '/bootstrap/cache/' . $cacheFile)) {
                throw new RuntimeException("Unable to copy the installed package cache [{$cacheFile}].");
            }
        }

        file_put_contents($exportDirectory . '/boost.json', json_encode([
            'agents' => ['opencode'],
            'guidelines' => true,
            'packages' => ['pestphp/pest-plugin-agent'],
            'skills' => ['pest-plugin-agent'],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        $process = new Process(
            [PHP_BINARY, 'artisan', 'boost:update', '--no-interaction'],
            $exportDirectory,
            [
                'APP_DEBUG' => 'true',
                'APP_ENV' => 'local',
                'PARATEST' => '1',
            ],
        );
        $process->setTimeout(15);
        $process->run();

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput() . $process->getOutput());
        expect($exportDirectory . '/AGENTS.md')->toBeFile();
        expect($exportDirectory . '/.agents/skills/pest-plugin-agent/SKILL.md')->toBeFile();

        $guidelines = file_get_contents($exportDirectory . '/AGENTS.md');

        if ($guidelines === false) {
            throw new RuntimeException('Unable to read generated Agent guidelines.');
        }

        expect($guidelines)->toContain('## Pest Agent Plugin');
    } finally {
        removeTemporaryDirectory(dirname($exportDirectory));
    }
});
