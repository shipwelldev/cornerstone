import { spawn } from 'node:child_process';

const [php, ...testArguments] = process.argv.slice(2);

if (!php) {
    console.error('Usage: node scripts/TestBrowser.mjs <php executable> [test arguments]');
    process.exit(1);
}

// Pest can leave browser services running after the tests finish.
// https://github.com/pestphp/pest/issues/1754
// A separate process group lets us clean up descendants on Linux and macOS.
const separateProcessGroup = process.platform !== 'win32';
const child = spawn(php, ['artisan', 'test', '--ci', '--compact', '--testsuite=Browser', ...testArguments], {
    detached: separateProcessGroup,
    stdio: 'inherit',
});

function stopBrowserProcesses() {
    if (!child.pid) {
        return;
    }

    try {
        if (separateProcessGroup) {
            process.kill(-child.pid, 'SIGKILL');
        } else {
            child.kill('SIGTERM');
        }
    } catch (error) {
        if (error.code !== 'ESRCH') {
            console.error(`Unable to stop browser processes: ${error.message}`);
            process.exitCode = 1;
        }
    }
}

process.on('exit', stopBrowserProcesses);

for (const [signal, exitCode] of [['SIGINT', 130], ['SIGTERM', 143]]) {
    process.on(signal, () => process.exit(exitCode));
}

child.on('error', (error) => {
    console.error(`Unable to start browser tests: ${error.message}`);
    process.exitCode = 1;
});

child.on('exit', (code, signal) => {
    process.exitCode = code ?? (signal === 'SIGINT' ? 130 : 1);
    stopBrowserProcesses();
});
