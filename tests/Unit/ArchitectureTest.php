<?php

declare(strict_types=1);

use Glhd\Bits\Database\HasSnowflakes;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;
use PHPUnit\Framework\TestCase as PhpUnitTestCase;
use Tests\TestCase as ApplicationTestCase;

function applicationDeclarationsIn(string $relativeDirectory = ''): array
{
    return declarationsInDirectory(
        dirname(__DIR__, 2) . '/app/' . $relativeDirectory,
        'App' . ($relativeDirectory === '' ? '' : '\\' . str_replace('/', '\\', $relativeDirectory)),
    );
}

function declarationsInDirectory(string $directory, string $namespace): array
{
    if ( ! is_dir($directory)) {
        return [];
    }

    $declarations = [];
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

        $relativePath = mb_substr($file->getPathname(), mb_strlen(mb_rtrim($directory, DIRECTORY_SEPARATOR)) + 1, -4);
        $class = $namespace . '\\' . str_replace(DIRECTORY_SEPARATOR, '\\', $relativePath);

        if ( ! class_exists($class) && ! interface_exists($class) && ! trait_exists($class)) {
            throw new RuntimeException("[STRUCT-01] Application declaration [{$class}] could not be loaded from its expected path.");
        }

        $declarations[$class] = new ReflectionClass($class);
    }

    ksort($declarations);

    return $declarations;
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

arch('[STRUCT-01] application classes match their paths and casing')
    ->expect('App')
    ->toBeCasedCorrectly();

arch('[STRUCT-02] services follow the service convention')
    ->expect('App\Services')
    ->toBeClasses()
    ->toHaveSuffix('Service');

arch('[STRUCT-02] application action classes are prohibited')
    ->expect('App')
    ->not->toHaveSuffix('Action');

arch('[STRUCT-02] the Actions namespace contains no classes')
    ->expect('App\Actions')
    ->not->toBeClasses();

arch('[STRUCT-03] data objects follow the data convention')
    ->expect('App\Data')
    ->toBeClasses()
    ->toHaveSuffix('Data')
    ->toBeReadonly();

arch('[STRUCT-01] controllers follow Laravel placement and naming')
    ->expect('App\Http\Controllers')
    ->toBeClasses()
    ->toHaveSuffix('Controller');

arch('[STRUCT-01] controllers are confined to their namespace')
    ->expect('App')
    ->not->toHaveSuffix('Controller')
    ->ignoring('App\Http\Controllers');

arch('[STRUCT-01] models follow Laravel placement')
    ->expect('App\Models')
    ->toBeClasses()
    ->toExtend(Model::class);

arch('[DATA-02] models use snowflake identifiers')
    ->expect('App\Models')
    ->toUseTrait(HasSnowflakes::class);

arch('[UI-01] Livewire components use their required namespace')
    ->expect('App\Livewire')
    ->toBeClasses()
    ->toExtend(Component::class);

arch('[UI-01] Livewire components are confined to their namespace')
    ->expect('App')
    ->not->toExtend(Component::class)
    ->ignoring('App\Livewire');

arch('[BOUNDARY-02] environment variables are not read by application classes')
    ->expect('env')
    ->not->toBeUsed();

arch('[TEST-01] application tests use Pest rather than PHPUnit classes')
    ->expect('Tests')
    ->not->toExtend(PhpUnitTestCase::class)
    ->ignoring(ApplicationTestCase::class);

test('[DATA-01] models declare mass-assignment metadata', function (): void {
    foreach (applicationDeclarationsIn('Models') as $reflection) {
        if ( ! $reflection instanceof ReflectionClass) {
            throw new RuntimeException('Architecture checks require reflected declarations.');
        }

        $model = $reflection->getName();
        $attributes = [
            ...$reflection->getAttributes(Fillable::class),
            ...$reflection->getAttributes(Guarded::class),
            ...$reflection->getAttributes(Unguarded::class),
        ];

        expect($attributes)->not->toBeEmpty($model);
    }
});

test('[DATA-02] model stubs use snowflake identifiers', function (): void {
    foreach (['model.stub', 'model.pivot.stub', 'model.morph-pivot.stub'] as $stub) {
        $contents = readArchitectureFile(dirname(__DIR__, 2) . '/stubs/' . $stub);

        expect($contents)
            ->toContain('use ' . HasSnowflakes::class . ';')
            ->toContain('use HasSnowflakes;');
    }
});

test('[STRUCT-03] data properties are typed and promoted', function (): void {
    foreach (applicationDeclarationsIn('Data') as $reflection) {
        if ( ! $reflection instanceof ReflectionClass) {
            throw new RuntimeException('Architecture checks require reflected declarations.');
        }

        $data = $reflection->getName();

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

test('[UI-02] Livewire public properties have native types', function (): void {
    foreach (applicationDeclarationsIn('Livewire') as $reflection) {
        if ( ! $reflection instanceof ReflectionClass) {
            throw new RuntimeException('Architecture checks require reflected declarations.');
        }

        $component = $reflection->getName();

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->getDeclaringClass()->getName() === $component) {
                $propertyName = $property->getName();

                expect($property->hasType())->toBeTrue("{$component}::\${$propertyName}");
            }
        }
    }
});

function nonCamelCaseMethods(string $class): array
{
    if ( ! class_exists($class) && ! interface_exists($class) && ! trait_exists($class)) {
        throw new RuntimeException("Application declaration [{$class}] could not be loaded.");
    }

    $declaration = new ReflectionClass($class);
    $violations = [];

    foreach ($declaration->getMethods() as $method) {
        if ($method->getDeclaringClass()->getName() !== $declaration->getName() || str_starts_with($method->getName(), '__')) {
            continue;
        }

        if (preg_match('/^[a-z][A-Za-z0-9]*$/', $method->getName()) !== 1) {
            $violations[] = $declaration->getName() . '::' . $method->getName();
        }
    }

    return $violations;
}

test('[STRUCT-01] application declaration methods use camelCase names', function (): void {
    foreach (applicationDeclarationsIn() as $declaration) {
        if ( ! $declaration instanceof ReflectionClass) {
            throw new RuntimeException('Method architecture checks require reflected declarations.');
        }

        expect(nonCamelCaseMethods($declaration->getName()))->toBeEmpty($declaration->getName());
    }
});

test('architecture discovery checks methods on classes, interfaces, traits, and enums', function (): void {
    $directory = sys_get_temp_dir() . '/cornerstone-architecture-' . bin2hex(random_bytes(8));
    $namespace = 'ArchitectureFixture' . bin2hex(random_bytes(8));
    mkdir($directory);
    $loader = static function (string $class) use ($namespace, $directory): void {
        if (str_starts_with($class, $namespace . '\\')) {
            require_once $directory . '/' . mb_substr($class, mb_strlen($namespace) + 1) . '.php';
        }
    };
    spl_autoload_register($loader);

    try {
        expect(declarationsInDirectory($directory, $namespace))->toBeEmpty();

        foreach (['class', 'interface', 'trait', 'enum'] as $kind) {
            foreach (['Valid' => 'calculatePlan', 'Invalid' => 'calculate_plan'] as $label => $method) {
                $name = $label . ucfirst($kind);
                $body = $kind === 'interface' ? ';' : ' {}';
                file_put_contents($directory . '/' . $name . '.php', "<?php declare(strict_types=1); namespace {$namespace}; {$kind} {$name} { public function {$method}(): void{$body} }");
            }
        }

        $declarations = declarationsInDirectory($directory, $namespace);
        expect($declarations)->toHaveCount(8);

        foreach ($declarations as $name => $declaration) {
            if ( ! is_string($name) || ! $declaration instanceof ReflectionClass) {
                throw new RuntimeException('Fixtures must resolve to named declarations.');
            }

            expect(nonCamelCaseMethods($declaration->getName()))->toBe(
                str_contains($name, '\\Invalid') ? [$name . '::calculate_plan'] : [],
            );
        }

        file_put_contents($directory . '/WrongPath.php', "<?php declare(strict_types=1); namespace {$namespace}; class DifferentName {}");
        expect(fn (): array => declarationsInDirectory($directory, $namespace))
            ->toThrow(RuntimeException::class, '[STRUCT-01] Application declaration');
    } finally {
        spl_autoload_unregister($loader);

        foreach (glob($directory . '/*.php') ?: [] as $file) {
            unlink($file);
        }

        rmdir($directory);
    }
});

test('[LANG-04, STRUCT-01] Blade views use kebab-case filenames and contain no raw PHP', function (): void {
    foreach (bladeViewFiles() as $view) {
        if ( ! is_string($view)) {
            throw new RuntimeException('Blade architecture checks require string paths.');
        }

        expect(basename($view))->toMatch('/^[a-z0-9]+(?:-[a-z0-9]+)*\.blade\.php$/', $view)
            ->and(readArchitectureFile($view))->not->toMatch('/@php\b|<\?(?:php|=)?/i', $view);
    }
});
