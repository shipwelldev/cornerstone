<?php

declare(strict_types=1);

// Use Composer's PHP executable even when it differs from the shell's default.
$process = proc_open(
    ['node', __DIR__ . '/TestBrowser.mjs', PHP_BINARY, ...array_slice($argv ?? [], 1)],
    [STDIN, STDOUT, STDERR],
    $pipes,
);

if ( ! is_resource($process)) {
    fwrite(STDERR, 'Unable to start the browser test runner.' . PHP_EOL);
    exit(1);
}

exit(proc_close($process));
