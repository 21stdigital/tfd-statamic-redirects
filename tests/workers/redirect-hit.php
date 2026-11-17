<?php

/*
 * Worker for the redirect concurrency test: hits one redirect N times, each
 * time through a freshly resolved RedirectRepository (one per request).
 *
 * Usage: php redirect-hit.php <redirect-id> <hits>
 */

require __DIR__ . '/../bootstrap.php';

use TFD\Redirects\Modules\Redirect\RedirectRepository;

[$script, $id, $hits] = $argv;

for ($i = 0; $i < (int) $hits; $i++) {
    try {
        (new RedirectRepository())->hit($id);
    } catch (\Throwable $e) {
        file_put_contents('php://stderr', get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
