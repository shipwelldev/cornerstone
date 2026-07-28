<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function composerScript(string $name): array
{
    $contents = file_get_contents(dirname(__DIR__, 2) . '/composer.json');

    if ($contents === false) {
        throw new RuntimeException('Unable to read composer.json.');
    }

    $composer = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
    $scripts = is_array($composer) ? ($composer['scripts'] ?? null) : null;
    $commands = is_array($scripts) ? ($scripts[$name] ?? null) : null;

    if ( ! is_array($commands) || $commands === []) {
        throw new RuntimeException("Composer script [{$name}] is missing or empty.");
    }

    $validatedCommands = [];

    foreach ($commands as $command) {
        if ( ! is_string($command)) {
            throw new RuntimeException("Composer script [{$name}] contains a non-string command.");
        }

        $validatedCommands[] = $command;
    }

    return $validatedCommands;
}

function runComposerScript(string $name, string $workingDirectory, bool $nonInteractive = false, ?string $fakeArtisanExitCode = null): Process
{
    $process = null;
    $environment = [];

    if ($nonInteractive) {
        $environment['COMPOSER_NO_INTERACTION'] = '1';
    }

    if ($fakeArtisanExitCode !== null) {
        $environment['FAKE_ARTISAN_EXIT_CODE'] = $fakeArtisanExitCode;
    }

    foreach (composerScript($name) as $script) {
        if ( ! is_string($script)) {
            throw new RuntimeException("Composer script [{$name}] contains a non-string command.");
        }

        $command = preg_replace('/^@php\s+/', escapeshellarg(PHP_BINARY) . ' ', $script);

        if ($command === null) {
            throw new RuntimeException("Unable to prepare Composer script [{$name}].");
        }

        $process = Process::fromShellCommandline($command, $workingDirectory, $environment);
        $process->run();

        if ( ! $process->isSuccessful()) {
            return $process;
        }
    }

    if ( ! $process instanceof Process) {
        throw new RuntimeException("Composer script [{$name}] did not contain any commands.");
    }

    return $process;
}

function runComposerScriptCommandContaining(string $name, string $fragment, string $workingDirectory, ?string $fakeArtisanExitCode = null): Process
{
    $matchingScripts = [];

    foreach (composerScript($name) as $script) {
        if ( ! is_string($script)) {
            throw new RuntimeException("Composer script [{$name}] contains a non-string command.");
        }

        if (str_contains($script, $fragment)) {
            $matchingScripts[] = $script;
        }
    }

    if (count($matchingScripts) !== 1) {
        throw new RuntimeException(
            "Expected exactly one Composer script [{$name}] command containing [{$fragment}], found [" . count($matchingScripts) . '].',
        );
    }

    $script = $matchingScripts[0];
    $commandLine = preg_replace('/^@php\s+/', escapeshellarg(PHP_BINARY) . ' ', $script);

    if ($commandLine === null) {
        throw new RuntimeException("Unable to prepare Composer script [{$name}].");
    }

    $environment = $fakeArtisanExitCode === null ? [] : ['FAKE_ARTISAN_EXIT_CODE' => $fakeArtisanExitCode];
    $process = Process::fromShellCommandline($commandLine, $workingDirectory, $environment);
    $process->run();

    return $process;
}

function writeFakeArtisan(string $workingDirectory): void
{
    file_put_contents($workingDirectory . '/artisan', <<<'PHP'
<?php

$arguments = array_slice($argv, 1);
file_put_contents(__DIR__.'/artisan-invocations', implode(' ', $arguments).PHP_EOL, FILE_APPEND);

if (getenv('PARATEST') !== false) {
    file_put_contents(__DIR__.'/paratest-environment', getenv('PARATEST'));
}

$configuredExitCode = getenv('FAKE_ARTISAN_EXIT_CODE');
$exitCode = $configuredExitCode === false ? 0 : (int) $configuredExitCode;

if ($exitCode === 0 && ($arguments[0] ?? null) === 'key:generate') {
    $environmentPath = __DIR__.'/.env';
    $environment = file_get_contents($environmentPath);
    $key = 'base64:'.base64_encode(random_bytes(32));
    $environment = preg_replace('/^APP_KEY=.*$/m', 'APP_KEY='.$key, $environment);
    file_put_contents($environmentPath, $environment);
}

exit($exitCode);
PHP);
}

function withComposerFixture(Closure $callback): void
{
    $workingDirectory = sys_get_temp_dir() . '/cornerstone-composer-' . bin2hex(random_bytes(8));

    if ( ! mkdir($workingDirectory)) {
        throw new RuntimeException("Unable to create Composer fixture [{$workingDirectory}].");
    }

    try {
        $callback($workingDirectory);
    } finally {
        removeTemporaryDirectory($workingDirectory);
    }
}

test('post-update skips Boost successfully when Boost is not configured', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);

        $process = runComposerScriptCommandContaining('post-update-cmd', fragment: 'boost:update', workingDirectory: $workingDirectory, fakeArtisanExitCode: '23');

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and(file_exists($workingDirectory . '/artisan-invocations'))->toBeFalse();
    });
});

test('post-update runs Boost whenever its configuration exists and propagates its exit status', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/boost.json', 'not valid json');

        $process = runComposerScriptCommandContaining('post-update-cmd', fragment: 'boost:update', workingDirectory: $workingDirectory, fakeArtisanExitCode: '23');

        expect($process->getExitCode())->toBe(23)
            ->and(file_get_contents($workingDirectory . '/artisan-invocations'))->toBe("boost:update --ansi\n")
            ->and(file_get_contents($workingDirectory . '/paratest-environment'))->toBe('1');
    });
});

test('Composer script command lookup fails clearly when the command is absent', function (): void {
    runComposerScriptCommandContaining('post-update-cmd', 'missing-command', sys_get_temp_dir());
})->throws(
    RuntimeException::class,
    'Expected exactly one Composer script [post-update-cmd] command containing [missing-command], found [0].',
);

test('Composer script command lookup fails clearly when the command is ambiguous', function (): void {
    runComposerScriptCommandContaining('post-update-cmd', 'artisan', sys_get_temp_dir());
})->throws(
    RuntimeException::class,
    'Expected exactly one Composer script [post-update-cmd] command containing [artisan], found [2].',
);

test('install-boost skips with instructions under non-interactive input', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);

        $process = runComposerScript('install-boost', $workingDirectory, nonInteractive: true);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($process->getOutput())->toContain('Boost setup was skipped')
            ->and($process->getOutput())->toContain('composer setup')
            ->and(file_exists($workingDirectory . '/artisan-invocations'))->toBeFalse();
    });
});

test('install-boost short-circuits silently when Boost is already configured', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/boost.json', '{}');

        $process = runComposerScript('install-boost', $workingDirectory, nonInteractive: true);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($process->getOutput())->toBe('')
            ->and($process->getErrorOutput())->toBe('')
            ->and(file_exists($workingDirectory . '/artisan-invocations'))->toBeFalse();
    });
});

test('configure-boost enables Pest Agent guidance in existing Boost configuration', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/boost.json', json_encode([
            'agents' => ['opencode'],
            'packages' => ['example/package'],
            'skills' => ['pest-testing'],
        ], JSON_THROW_ON_ERROR));

        $process = runComposerScript('configure-boost', $workingDirectory);
        $contents = file_get_contents($workingDirectory . '/boost.json');

        if ($contents === false) {
            throw new RuntimeException('Unable to read configured Boost fixture.');
        }

        $config = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($config)->toMatchArray([
                'agents' => ['opencode'],
                'packages' => ['example/package', 'pestphp/pest-plugin-agent'],
                'skills' => ['pest-testing', 'pest-plugin-agent'],
            ])
            ->and(file_get_contents($workingDirectory . '/artisan-invocations'))->toBe("boost:update --ansi --no-interaction\n")
            ->and(file_get_contents($workingDirectory . '/paratest-environment'))->toBe('1');
    });
});

test('configure-boost skips successfully when Boost is not configured', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);

        $process = runComposerScript('configure-boost', $workingDirectory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and(file_exists($workingDirectory . '/artisan-invocations'))->toBeFalse();
    });
});

test('configure-boost fails clearly when Boost configuration is invalid', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/boost.json', 'not valid json');

        $process = runComposerScript('configure-boost', $workingDirectory);

        expect($process->isSuccessful())->toBeFalse()
            ->and($process->getErrorOutput())->toContain('boost.json is invalid')
            ->and(file_exists($workingDirectory . '/artisan-invocations'))->toBeFalse();
    });
});

test('configure-boost propagates Boost update failures', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/boost.json', '{}');

        $process = runComposerScript('configure-boost', $workingDirectory, fakeArtisanExitCode: '23');

        expect($process->getExitCode())->toBe(23)
            ->and(file_get_contents($workingDirectory . '/artisan-invocations'))->toBe("boost:update --ansi --no-interaction\n");
    });
});

test('prepare-environment creates an environment file and application key without changing unrelated values', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/.env.example', "APP_NAME=Cornerstone\nAPP_KEY=\nCUSTOM_VALUE=preserved\n");

        $process = runComposerScript('prepare-environment', $workingDirectory);
        $environment = file_get_contents($workingDirectory . '/.env');

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($environment)->toContain("APP_NAME=Cornerstone\n")
            ->and($environment)->toContain("CUSTOM_VALUE=preserved\n")
            ->and($environment)->toMatch('/^APP_KEY=base64:.+$/m')
            ->and(file_get_contents($workingDirectory . '/artisan-invocations'))->toBe("key:generate --ansi --no-interaction\n");
    });
});

test('rerunning prepare-environment preserves the existing environment file and valid application key', function (): void {
    withComposerFixture(function (string $workingDirectory): void {
        writeFakeArtisan($workingDirectory);
        file_put_contents($workingDirectory . '/.env.example', "APP_NAME=Cornerstone\nAPP_KEY=\nCUSTOM_VALUE=preserved\n");

        $firstProcess = runComposerScript('prepare-environment', $workingDirectory);
        $firstEnvironment = file_get_contents($workingDirectory . '/.env');
        $secondProcess = runComposerScript('prepare-environment', $workingDirectory);

        expect($firstProcess->isSuccessful())->toBeTrue($firstProcess->getErrorOutput())
            ->and($secondProcess->isSuccessful())->toBeTrue($secondProcess->getErrorOutput())
            ->and(file_get_contents($workingDirectory . '/.env'))->toBe($firstEnvironment)
            ->and(file_get_contents($workingDirectory . '/artisan-invocations'))->toBe("key:generate --ansi --no-interaction\n");
    });
});
