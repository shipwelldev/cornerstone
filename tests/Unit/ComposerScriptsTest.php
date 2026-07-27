<?php

declare(strict_types=1);

use Symfony\Component\Process\Process;

function runBoostUpdateComposerHook(string $workingDirectory): Process
{
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $script = collect($composer['scripts']['post-update-cmd'])
        ->first(fn (string $script): bool => str_contains($script, 'boost:update'));

    expect($script)->toBeString();

    $command = preg_replace('/^@php\s+/', escapeshellarg(PHP_BINARY) . ' ', $script);

    $process = Process::fromShellCommandline($command, $workingDirectory);
    $process->run();

    return $process;
}

test('composer updates succeed before Boost is configured', function (): void {
    $workingDirectory = sys_get_temp_dir() . '/cornerstone-composer-' . bin2hex(random_bytes(8));
    mkdir($workingDirectory);

    try {
        $process = runBoostUpdateComposerHook($workingDirectory);

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput());
    } finally {
        rmdir($workingDirectory);
    }
});

test('composer updates still run Boost whenever its configuration exists', function (): void {
    $workingDirectory = sys_get_temp_dir() . '/cornerstone-composer-' . bin2hex(random_bytes(8));
    mkdir($workingDirectory);
    file_put_contents($workingDirectory . '/boost.json', 'invalid');

    try {
        $process = runBoostUpdateComposerHook($workingDirectory);

        expect($process->isSuccessful())->toBeFalse()
            ->and($process->getErrorOutput())->toContain('artisan');
    } finally {
        unlink($workingDirectory . '/boost.json');
        rmdir($workingDirectory);
    }
});

test('project creation updates support and publishes stubs before setup', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['scripts']['post-create-project-cmd'])->toBe([
        '@composer update shipwelldev/cornerstone-support --no-interaction @no_additional_args',
        '@php artisan cornerstone:stubs --ansi --no-interaction @no_additional_args',
        '@setup @no_additional_args',
    ]);
});

test('non-interactive setup skips Boost with instructions', function (): void {
    $composer = json_decode(file_get_contents(dirname(__DIR__, 2) . '/composer.json'), true, flags: JSON_THROW_ON_ERROR);
    $script = $composer['scripts']['install-boost'][0];
    $command = preg_replace('/^@php\s+/', escapeshellarg(PHP_BINARY) . ' ', $script);
    $workingDirectory = sys_get_temp_dir() . '/cornerstone-composer-' . bin2hex(random_bytes(8));
    mkdir($workingDirectory);

    try {
        $process = Process::fromShellCommandline($command, $workingDirectory);
        $process->setInput(null);
        $process->run();

        expect($process->isSuccessful())->toBeTrue($process->getErrorOutput())
            ->and($process->getOutput())->toContain('Boost setup was skipped')
            ->and($process->getOutput())->toContain('composer setup');
    } finally {
        rmdir($workingDirectory);
    }
});
