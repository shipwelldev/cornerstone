<?php

declare(strict_types=1);

use Cornerstone\Scripts\ProjectSetup;

require __DIR__ . '/ProjectSetup.php';

try {
    $exitCode = match ($argv[1] ?? '') {
        'copy-environment' => ProjectSetup::copyEnvironment(),
        'prepare-environment' => ProjectSetup::prepareEnvironment(),
        'install-boost' => ProjectSetup::installBoost(),
        'configure-boost' => ProjectSetup::configureBoost(),
        'update-boost' => ProjectSetup::updateBoost(),
        default => throw new InvalidArgumentException('Unknown setup operation.'),
    };
} catch (Throwable $exception) {
    fwrite(STDERR, $exception->getMessage() . PHP_EOL);
    $exitCode = 1;
}

exit($exitCode);
