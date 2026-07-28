<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Tests\TestCase as ApplicationTestCase;

function applicationClassesIn(string $relativeDirectory = ''): array
{
    $appDirectory = dirname(__DIR__, 2) . '/app/';
    $directory = $appDirectory . $relativeDirectory;

    if ( ! is_dir($directory)) {
        return [];
    }

    $classes = [];
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ( ! $file instanceof SplFileInfo) {
            continue;
        }

        if ( ! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relativePath = mb_substr($file->getPathname(), mb_strlen($appDirectory), -4);
        $class = 'App\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        if ( ! class_exists($class)) {
            throw new RuntimeException("Application class [{$class}] could not be loaded.");
        }

        $classes[] = $class;
    }

    sort($classes);

    return $classes;
}

function bladeViewFiles(): array
{
    $viewsDirectory = dirname(__DIR__, 2) . '/resources/views/';
    $views = [];
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($viewsDirectory, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($files as $file) {
        if ( ! $file instanceof SplFileInfo) {
            continue;
        }

        if ($file->isFile() && str_ends_with($file->getFilename(), '.blade.php')) {
            $views[] = $file->getPathname();
        }
    }

    sort($views);

    return $views;
}

function readArchitectureFile(string $path): string
{
    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new RuntimeException("Unable to read architecture fixture [{$path}].");
    }

    return $contents;
}

arch('application classes match their paths and casing')
    ->expect('App')
    ->toBeCasedCorrectly();

arch('services follow the service convention')
    ->expect('App\Services')
    ->toBeClasses()
    ->toHaveSuffix('Service');

arch('application action classes are prohibited')
    ->expect('App')
    ->not->toHaveSuffix('Action');

arch('the Actions namespace contains no classes')
    ->expect('App\Actions')
    ->not->toBeClasses();

arch('data objects follow the data convention')
    ->expect('App\Data')
    ->toBeClasses()
    ->toHaveSuffix('Data')
    ->toBeReadonly();

arch('controllers follow Laravel placement and naming')
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->toHaveSuffix('Controller');

arch('controllers are confined to their namespace')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');

arch('models follow Laravel placement')
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend(Model::class);

arch('Livewire components use their required namespace')
    ->expect('App\Livewire')
    ->toBeClasses()
    ->toExtend(Component::class);

arch('Livewire components are confined to their namespace')
    ->expect('App')
    ->not->toExtend(Component::class)
    ->ignoring('App\Livewire');

arch('environment variables are not read by application classes')
    ->expect('env')
    ->not->toBeUsed();

arch('application tests use Pest rather than PHPUnit classes')
    ->expect('Tests')
    ->not->toExtend(PhpUnitTestCase::class)
    ->ignoring(ApplicationTestCase::class);

test('models declare mass-assignment metadata', function (): void {
    foreach (applicationClassesIn('Models') as $model) {
        if ( ! is_string($model) || ! class_exists($model)) {
            throw new RuntimeException('Model architecture checks require loadable class names.');
        }

        $reflection = new ReflectionClass($model);
        $attributes = [
            ...$reflection->getAttributes(Fillable::class),
            ...$reflection->getAttributes(Guarded::class),
            ...$reflection->getAttributes(Unguarded::class),
        ];

        expect($attributes)->not->toBeEmpty($model);
    }
});

test('data properties are typed and promoted', function (): void {
    $dataClasses = applicationClassesIn('Data');

    if ($dataClasses === []) {
        expect(is_dir(dirname(__DIR__, 2) . '/app/Data'))->toBeFalse();

        return;
    }

    foreach ($dataClasses as $data) {
        if ( ! is_string($data) || ! class_exists($data)) {
            throw new RuntimeException('Data architecture checks require loadable class names.');
        }

        $reflection = new ReflectionClass($data);

        foreach ($reflection->getProperties() as $property) {
            if ($property->getDeclaringClass()->getName() !== $data) {
                continue;
            }

            $propertyName = $property->getName();

            expect($property->hasType())->toBeTrue("{$data}::\${$propertyName}")
                ->and($property->isPromoted())->toBeTrue("{$data}::\${$propertyName}");
        }
    }
});

test('Livewire public properties have native types', function (): void {
    foreach (applicationClassesIn('Livewire') as $component) {
        if ( ! is_string($component) || ! class_exists($component)) {
            throw new RuntimeException('Livewire architecture checks require loadable class names.');
        }

        $reflection = new ReflectionClass($component);

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() === $component) {
                $propertyName = $property->getName();

                expect($property->hasType())->toBeTrue("{$component}::\${$propertyName}");
            }
        }
    }
});

test('application methods use camelCase names', function (): void {
    foreach (applicationClassesIn() as $class) {
        if ( ! is_string($class) || ! class_exists($class)) {
            throw new RuntimeException('Method architecture checks require loadable class names.');
        }

        $reflection = new ReflectionClass($class);

        foreach ($reflection->getMethods() as $method) {
            if ($method->getDeclaringClass()->getName() === $class && ! str_starts_with($method->getName(), '__')) {
                $methodName = $method->getName();

                expect($methodName)
                    ->toMatch('/^[a-z][A-Za-z0-9]*$/', "{$class}::{$methodName}");
            }
        }
    }
});

test('Blade views use kebab-case filenames and contain no raw PHP', function (): void {
    foreach (bladeViewFiles() as $view) {
        if ( ! is_string($view)) {
            throw new RuntimeException('Blade architecture checks require string paths.');
        }

        expect(basename($view))->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*\.blade\.php$/', $view)
            ->and(readArchitectureFile($view))->not->toMatch('/@php\b|<\?(?:php|=)?/i', $view);
    }
});
