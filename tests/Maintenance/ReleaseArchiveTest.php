<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function runReleaseArchiveCommand(array $command, string $workingDirectory, array $environment = []): string
{
    $process = new Process($command, $workingDirectory, $environment);
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

function removeReleaseArchive(string $exportDirectory): void
{
    $temporaryDirectory = dirname($exportDirectory);
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($temporaryDirectory, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($files as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }

    rmdir($temporaryDirectory);
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
        removeReleaseArchive($exportDirectory);
    }
});

test('relative Markdown file links in the exported README resolve', function (): void {
    $exportDirectory = buildReleaseArchiveFromWorkingTree();

    try {
        $readme = file_get_contents($exportDirectory . '/README.md');

        expect($readme)->toBeString();

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
        removeReleaseArchive($exportDirectory);
    }
});
