<?php

/*
 * Worker for the concurrency test. Behaves like one PHP-FPM worker handling
 * several 404 requests in a row: for each request a fresh NotFoundRepository
 * is resolved (as the container does per request) and hit() is called with a
 * URL unique to this worker.
 *
 * Usage: php hit.php <worker-id> <hits>
 * Env:   REDIRECTS_TEST_STORAGE, REDIRECTS_TEST_LOG, REDIRECTS_TEST_CONFIG (JSON)
 */

require __DIR__ . '/../bootstrap.php';

use TFD\Redirects\Modules\NotFound\NotFoundRepository;

redirects_test_boot_container(json_decode(getenv('REDIRECTS_TEST_CONFIG') ?: '[]', true));

[$script, $worker, $hits] = $argv;

for ($i = 0; $i < (int) $hits; $i++) {
    try {
        (new NotFoundRepository())->hit("https://example.test/worker-{$worker}/request-{$i}");
    } catch (\Throwable $e) {
        file_put_contents('php://stderr', get_class($e) . ': ' . $e->getMessage() . PHP_EOL);
        exit(1);
    }
}
