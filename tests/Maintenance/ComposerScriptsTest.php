<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function runComposerScript(string $name, string $workingDirectory, string $failure = ''): Process
{
    $process = new Process(
        ['composer', '--no-interaction', 'run-script', $name],
        $workingDirectory,
        [
            'COMPOSER_NO_INTERACTION' => '1',
            'COMPOSER_DISABLE_NETWORK' => '1',
            'COMPOSER' => false,
            'PATH' => $workingDirectory . '/bin' . PATH_SEPARATOR . (getenv('PATH') ?: ''),
            'FAKE_FAILURE_COMMAND' => $failure,
        ],
    );
    $process->setTimeout(30);
    $process->run();

    return $process;
}

function withComposerFixture(Closure $callback): void
{
    $workingDirectory = sys_get_temp_dir() . '/cornerstone-composer-' . bin2hex(random_bytes(8));
    mkdir($workingDirectory, 0700);

    try {
        $repository = dirname(__DIR__, 2);
        $contents = file_get_contents($repository . '/composer.json');

        if ($contents === false) {
            throw new RuntimeException('Unable to read composer.json.');
        }

        $composer = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if ( ! is_array($composer) || ! is_array($composer['scripts'] ?? null)) {
            throw new RuntimeException('Unable to read Composer scripts.');
        }

        // Real Composer owns script expansion, nested scripts, events, and exit status.
        // An offline metapackage replaces the external support-package dependency.
        file_put_contents($workingDirectory . '/composer.json', json_encode([
            'name' => 'cornerstone/lifecycle-fixture',
            'require' => ['shipwelldev/cornerstone-support' => '^0.3'],
            'repositories' => [
                ['type' => 'package', 'package' => ['name' => 'shipwelldev/cornerstone-support', 'version' => '0.3.0', 'type' => 'metapackage']],
                ['packagist.org' => false],
            ],
            'autoload' => ['classmap' => ['FakeComposerHooks.php']],
            'scripts' => $composer['scripts'],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

        mkdir($workingDirectory . '/scripts');

        foreach (new DirectoryIterator($repository . '/scripts') as $file) {
            if ($file->isFile()) {
                copy($file->getPathname(), $workingDirectory . '/scripts/' . $file->getFilename());
            }
        }

        file_put_contents($workingDirectory . '/FakeComposerHooks.php', <<<'PHPFILE'
<?php

declare(strict_types=1);

namespace Illuminate\Foundation;

class ComposerScripts
{
    public static function postAutoloadDump(): void {}
}
PHPFILE);
        file_put_contents($workingDirectory . '/artisan', <<<'PHPFILE'
<?php

declare(strict_types=1);

$arguments = array_slice($argv, 1);
$command = implode(' ', $arguments);
file_put_contents(__DIR__ . '/invocations', 'artisan ' . $command . PHP_EOL, FILE_APPEND);

if (getenv('PARATEST') !== false) {
    file_put_contents(__DIR__ . '/paratest-environment', getenv('PARATEST'));
}

if (($arguments[0] ?? '') === getenv('FAKE_FAILURE_COMMAND')) {
    exit(23);
}

if (($arguments[0] ?? '') === 'key:generate') {
    $environmentPath = __DIR__ . '/.env';
    $environment = file_get_contents($environmentPath);
    $key = 'base64:' . base64_encode(random_bytes(32));
    file_put_contents($environmentPath, preg_replace('/^APP_KEY=.*$/m', 'APP_KEY=' . $key, $environment));
}

if (($arguments[0] ?? '') === 'cornerstone:stubs') {
    if (! is_dir(__DIR__ . '/stubs')) {
        mkdir(__DIR__ . '/stubs');
    }

    if (! file_exists(__DIR__ . '/stubs/data.stub') || in_array('--force', $arguments, true)) {
        file_put_contents(__DIR__ . '/stubs/data.stub', 'initial stub');
    }
}
PHPFILE);
        file_put_contents($workingDirectory . '/.env.example', "APP_NAME=Cornerstone\nAPP_KEY=\nCUSTOM_VALUE=preserved\n");
        mkdir($workingDirectory . '/bin');
        mkdir($workingDirectory . '/vendor/bin', recursive: true);

        $fakeTool = <<<'PHPFILE'
#!/usr/bin/env php
<?php

declare(strict_types=1);

$command = basename($argv[0]) . ' ' . implode(' ', array_slice($argv, 1));
file_put_contents(getcwd() . '/invocations', $command . PHP_EOL, FILE_APPEND);

if ($command === getenv('FAKE_FAILURE_COMMAND')) {
    exit(23);
}

if ($command === 'npm run build') {
    if (! is_dir('public/build')) {
        mkdir('public/build', recursive: true);
    }

    file_put_contents('public/build/manifest.json', '{}');
}
PHPFILE;

        foreach (['bin/npm', 'bin/npx', 'vendor/bin/pint', 'vendor/bin/phpstan'] as $tool) {
            file_put_contents($workingDirectory . '/' . $tool, $fakeTool);
            chmod($workingDirectory . '/' . $tool, 0755);
        }

        // Produce an actual lock and autoloader before exercising lifecycle hooks.
        $install = new Process(['composer', 'update', '--no-scripts', '--no-interaction'], $workingDirectory, ['COMPOSER_DISABLE_NETWORK' => '1', 'COMPOSER' => false]);
        $install->mustRun();
        $callback($workingDirectory);
    } finally {
        removeTemporaryDirectory($workingDirectory);
    }
}

function composerFixtureInvocations(string $workingDirectory): array
{
    $path = $workingDirectory . '/invocations';

    if ( ! file_exists($path)) {
        return [];
    }

    $invocations = file($path, FILE_IGNORE_NEW_LINES);

    if ($invocations === false) {
        throw new RuntimeException('Unable to read fixture invocations.');
    }

    return $invocations;
}

test('post-update succeeds without Boost configuration and only publishes Laravel assets', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('post-update-cmd', $directory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and(composerFixtureInvocations($directory))->toBe(['artisan vendor:publish --tag=laravel-assets --ansi --force']);
    });
});

test('post-update invokes configured Boost and propagates failures', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/boost.json', '{}');
        $process = runComposerScript('post-update-cmd', $directory, 'boost:update');

        expect($process->getExitCode())->toBe(23)
            ->and(composerFixtureInvocations($directory))->toBe([
                'artisan vendor:publish --tag=laravel-assets --ansi --force',
                'artisan boost:update --ansi',
            ])
            ->and(file_get_contents($directory . '/paratest-environment'))->toBe('1');
    });
});

test('install-boost explains the interactive setup step without invoking Artisan', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('install-boost', $directory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($process->getOutput())->toContain('Boost setup was skipped', 'composer setup')
            ->and(composerFixtureInvocations($directory))->toBe([]);
    });
});

test('install-boost leaves configured tooling alone', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/boost.json', '{}');
        $process = runComposerScript('install-boost', $directory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($process->getOutput())->toBe('')
            ->and(composerFixtureInvocations($directory))->toBe([]);
    });
});

test('configure-boost preserves developer choices and adds guidance idempotently', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/boost.json', json_encode([
            'agents' => ['opencode'],
            'packages' => ['example/package', 'example/package'],
            'skills' => ['pest-testing'],
        ], JSON_THROW_ON_ERROR));

        $first = runComposerScript('configure-boost', $directory);
        $firstConfiguration = file_get_contents($directory . '/boost.json');
        $second = runComposerScript('configure-boost', $directory);

        expect($firstConfiguration)->toBeString();
        $configuration = json_decode($firstConfiguration, true, flags: JSON_THROW_ON_ERROR);

        expect($first->isSuccessful())->toBeTrue($first->getErrorOutput())
            ->and($second->isSuccessful())->toBeTrue($second->getErrorOutput())
            ->and(file_get_contents($directory . '/boost.json'))->toBe($firstConfiguration)
            ->and($configuration)->toBe([
                'agents' => ['opencode'],
                'packages' => ['example/package', 'pestphp/pest-plugin-agent'],
                'skills' => ['pest-testing', 'pest-plugin-agent'],
            ])
            ->and(composerFixtureInvocations($directory))->toBe(array_fill(0, 2, 'artisan boost:update --ansi --no-interaction'))
            ->and(file_get_contents($directory . '/paratest-environment'))->toBe('1');
    });
});

test('configure-boost skips successfully without configuration', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('configure-boost', $directory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and(composerFixtureInvocations($directory))->toBe([]);
    });
});

test('configure-boost rejects malformed configuration without rewriting it', function (string $configuration): void {
    withComposerFixture(function (string $directory) use ($configuration): void {
        file_put_contents($directory . '/boost.json', $configuration);
        $process = runComposerScript('configure-boost', $directory);

        expect($process->isSuccessful())->toBeFalse()
            ->and(file_get_contents($directory . '/boost.json'))->toBe($configuration)
            ->and(composerFixtureInvocations($directory))->toBe([]);
    });
})->with(['not valid json', '[]', '{"packages":"invalid"}', '{"skills":[{}]}']);

test('configure-boost propagates update failures', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/boost.json', '{}');
        $process = runComposerScript('configure-boost', $directory, 'boost:update');

        expect($process->getExitCode())->toBe(23);
    });
});

test('environment preparation creates a key once and preserves unrelated values', function (): void {
    withComposerFixture(function (string $directory): void {
        $first = runComposerScript('prepare-environment', $directory);
        $environment = file_get_contents($directory . '/.env');
        $second = runComposerScript('prepare-environment', $directory);

        expect($first->isSuccessful())->toBeTrue($first->getErrorOutput())
            ->and($second->isSuccessful())->toBeTrue($second->getErrorOutput())
            ->and($environment)->toContain("APP_NAME=Cornerstone\n", "CUSTOM_VALUE=preserved\n")
            ->and($environment)->toMatch('/^APP_KEY=base64:.+$/m')
            ->and(file_get_contents($directory . '/.env'))->toBe($environment)
            ->and(composerFixtureInvocations($directory))->toBe(['artisan key:generate --ansi --no-interaction']);
    });
});

test('environment preparation propagates key generation failures', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('prepare-environment', $directory, 'key:generate');

        expect($process->getExitCode())->toBe(23);
    });
});

test('initial environment preparation works before dependencies exist', function (): void {
    withComposerFixture(function (string $directory): void {
        removeTemporaryDirectory($directory . '/vendor');
        $first = runComposerScript('post-root-package-install', $directory);
        file_put_contents($directory . '/.env', 'custom environment');
        $second = runComposerScript('post-root-package-install', $directory);

        expect($first->isSuccessful())->toBeTrue($first->getErrorOutput())
            ->and($second->isSuccessful())->toBeTrue($second->getErrorOutput())
            ->and(file_get_contents($directory . '/.env'))->toBe('custom environment')
            ->and(composerFixtureInvocations($directory))->toBe([]);
    });
});

test('project creation publishes stubs once and repeatable setup completes unattended steps', function (): void {
    withComposerFixture(function (string $directory): void {
        $first = runComposerScript('post-create-project-cmd', $directory);
        $applicationEnvironment = file_get_contents($directory . '/.env');
        file_put_contents($directory . '/stubs/data.stub', 'customized stub');
        $second = runComposerScript('setup', $directory);

        expect($first->isSuccessful())->toBeTrue($first->getErrorOutput())
            ->and($second->isSuccessful())->toBeTrue($second->getErrorOutput())
            ->and(file_get_contents($directory . '/.env'))->toBe($applicationEnvironment)
            ->and(file_get_contents($directory . '/stubs/data.stub'))->toBe('customized stub')
            ->and($directory . '/public/build/manifest.json')->toBeFile()
            ->and(file_exists($directory . '/database/database.sqlite'))->toBeFalse()
            ->and(composerFixtureInvocations($directory))->toBe([
                'artisan package:discover --ansi',
                'artisan vendor:publish --tag=laravel-assets --ansi --force',
                'artisan cornerstone:stubs --ansi --no-interaction',
                'artisan package:discover --ansi',
                'artisan key:generate --ansi --no-interaction',
                'npm ci',
                'npx playwright install chromium',
                'npm run build',
                'artisan package:discover --ansi',
                'npm ci',
                'npx playwright install chromium',
                'npm run build',
            ]);
    });
});

test('setup stops when dependency tooling fails', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('setup', $directory, 'npm ci');

        expect($process->getExitCode())->toBe(23)
            ->and(composerFixtureInvocations($directory))->toBe([
                'artisan package:discover --ansi',
                'artisan key:generate --ansi --no-interaction',
                'npm ci',
            ])
            ->and(file_exists($directory . '/public/build/manifest.json'))->toBeFalse();
    });
});

test('verification builds before running the application and browser suites', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('verify', $directory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and(composerFixtureInvocations($directory))->toBe([
                'pint --test --format agent',
                'phpstan analyse --memory-limit=2G',
                'npm run build',
                'artisan test --ci --parallel --compact --exclude-testsuite=Browser',
                'artisan test --ci --compact --testsuite=Browser',
            ]);
    });
});

test('verification stops before tests when the build fails', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('verify', $directory, 'npm run build');

        expect($process->getExitCode())->toBe(23)
            ->and(composerFixtureInvocations($directory))->toBe([
                'pint --test --format agent',
                'phpstan analyse --memory-limit=2G',
                'npm run build',
            ]);
    });
});

test('browser test failures propagate through Composer', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = runComposerScript('test:browser', $directory, 'test');

        expect($process->getExitCode())->toBe(23);
    });
});

test('browser runner reports failure when its PHP executable cannot start', function (): void {
    withComposerFixture(function (string $directory): void {
        $process = new Process(['node', 'scripts/TestBrowser.mjs', $directory . '/missing-php'], $directory);
        $process->setTimeout(5);
        $process->run();

        expect($process->getExitCode())->toBe(1)
            ->and($process->getErrorOutput())->toContain('Unable to start browser tests');
    });
});

test('browser runner releases inherited output streams when its test process leaves descendants', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/artisan', <<<'PHPFILE'
<?php

declare(strict_types=1);

$process = proc_open([PHP_BINARY, 'browser-service.php'], [STDIN, STDOUT, STDERR], $pipes);
$deadline = microtime(true) + 5;

while (! file_exists('browser-service-started') && microtime(true) < $deadline) {
    usleep(10000);
}

exit(file_exists('browser-service-started') ? 0 : 1);
PHPFILE);
        file_put_contents($directory . '/browser-service.php', <<<'PHPFILE'
<?php

declare(strict_types=1);

file_put_contents('browser-service-started', 'ready');
// A leaked child keeps Composer's output pipes open until its bounded deadline.
usleep(10000000);
PHPFILE);
        $process = new Process(['composer', '--no-interaction', 'test:browser'], $directory, ['COMPOSER' => false]);
        $process->setTimeout(5);
        $process->run();

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($directory . '/browser-service-started')->toBeFile();
    });
});

test('browser runner forwards interruption status and releases its process group', function (): void {
    withComposerFixture(function (string $directory): void {
        file_put_contents($directory . '/artisan', <<<'PHPFILE'
<?php

declare(strict_types=1);

file_put_contents('browser-tests-started', 'ready');
usleep(10000000);
PHPFILE);
        $process = new Process(['node', 'scripts/TestBrowser.mjs', PHP_BINARY], $directory);
        $process->setTimeout(5);
        $process->start();
        $deadline = microtime(true) + 3;

        while ( ! file_exists($directory . '/browser-tests-started') && microtime(true) < $deadline) {
            usleep(10000);
        }

        try {
            expect($directory . '/browser-tests-started')->toBeFile();
            $process->signal(15);
            $process->wait();

            expect($process->getExitCode())->toBe(143);
        } finally {
            $process->stop(0);
        }
    });
});
