<?php

declare(strict_types=1);

namespace Cornerstone\Scripts;

use JsonException;
use RuntimeException;
use stdClass;

class ProjectSetup
{
    public static function copyEnvironment(): int
    {
        if ( ! file_exists('.env') && ! copy('.env.example', '.env')) {
            throw new RuntimeException('Unable to create .env from .env.example.');
        }

        return 0;
    }

    public static function prepareEnvironment(): int
    {
        self::copyEnvironment();
        $environment = file_get_contents('.env');

        if ($environment === false) {
            throw new RuntimeException('Unable to read .env.');
        }

        $emptyKeyPattern = '/^APP_KEY=[\t ]*(?:"[\t ]*"|\'[\t ]*\')?[\t ]*(#[^\r\n]*)?(\r?)$/m';

        if (preg_match($emptyKeyPattern, $environment, $matches) !== 1) {
            return preg_match('/^APP_KEY=/m', $environment) === 1
                ? 0
                : self::artisan('key:generate', '--ansi', '--no-interaction');
        }

        // Laravel replaces the APP_KEY= prefix for an empty key. Remove empty
        // quotes/whitespace first so they cannot become part of the generated key.
        $comment = $matches[1] === '' ? '' : ' ' . $matches[1];
        $normalized = preg_replace_callback(
            $emptyKeyPattern,
            fn (): string => 'APP_KEY=' . $comment . $matches[2],
            $environment,
            1,
        );

        if ($normalized === null || ($normalized !== $environment && file_put_contents('.env', $normalized) === false)) {
            throw new RuntimeException('Unable to prepare the empty application key in .env.');
        }

        return self::artisan('key:generate', '--ansi', '--no-interaction');
    }

    public static function installBoost(): int
    {
        if (file_exists('boost.json')) {
            return 0;
        }

        if (filter_var(getenv('COMPOSER_NO_INTERACTION'), FILTER_VALIDATE_BOOL) || ! stream_isatty(STDIN)) {
            fwrite(STDOUT, PHP_EOL . 'Boost setup was skipped because Composer is running non-interactively.' . PHP_EOL);
            fwrite(STDOUT, 'Run composer setup in an interactive terminal to finish setup.' . PHP_EOL);

            return 0;
        }

        return self::artisan('boost:install', '--ansi');
    }

    public static function configureBoost(): int
    {
        if ( ! file_exists('boost.json')) {
            return 0;
        }

        $contents = file_get_contents('boost.json');

        if ($contents === false) {
            throw new RuntimeException('Unable to read boost.json.');
        }

        try {
            $configuration = json_decode($contents, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw new RuntimeException('Unable to configure Boost because boost.json is invalid.');
        }

        if ( ! $configuration instanceof stdClass) {
            throw new RuntimeException('Unable to configure Boost because boost.json is invalid.');
        }

        foreach (['packages' => 'pestphp/pest-plugin-agent', 'skills' => 'pest-plugin-agent'] as $key => $value) {
            $values = $configuration->{$key} ?? [];

            if ( ! is_array($values) || ! array_is_list($values)) {
                throw new RuntimeException("Unable to configure Boost because [{$key}] must be a list of strings.");
            }

            foreach ($values as $entry) {
                if ( ! is_string($entry)) {
                    throw new RuntimeException("Unable to configure Boost because [{$key}] must be a list of strings.");
                }
            }

            if ( ! in_array($value, $values, true)) {
                $values[] = $value;
            }

            $configuration->{$key} = array_values(array_unique($values));
        }

        $updated = json_encode($configuration, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . PHP_EOL;

        if (file_put_contents('boost.json', $updated) === false) {
            throw new RuntimeException('Unable to write boost.json.');
        }

        return self::updateBoost('--no-interaction');
    }

    public static function updateBoost(string ...$arguments): int
    {
        if ( ! file_exists('boost.json')) {
            return 0;
        }

        // Boost discovers Pest guidance without starting its browser services.
        putenv('PARATEST=1');

        return self::artisan('boost:update', '--ansi', ...$arguments);
    }

    private static function artisan(string ...$arguments): int
    {
        $process = proc_open([PHP_BINARY, 'artisan', ...array_values($arguments)], [STDIN, STDOUT, STDERR], $pipes);

        if ( ! is_resource($process)) {
            throw new RuntimeException('Unable to start Artisan.');
        }

        return proc_close($process);
    }
}
