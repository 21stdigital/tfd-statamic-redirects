<?php

namespace TFD\Redirects\Tests\Unit;

use TFD\Redirects\Tests\TestCase;

/**
 * Regression for the 2026-11-17 incident: parallel 404 hits overwrote each
 * other (last writer wins) and readers that caught a half-written file treated
 * the list as empty, which then got written back, shrinking the list.
 */
class NotFoundConcurrencyTest extends TestCase
{
    private const WORKERS = 20;
    private const HITS_PER_WORKER = 10;
    private const SEED = 3000;

    protected function config(): array
    {
        // The cap is not what this test is about; lift it above the seed size
        // and wait for the lock as long as needed.
        return ['statamic-redirects' => ['not_found' => [
            'max_entries' => 100000,
            'lock_timeout' => 60,
        ]]];
    }

    public function test_parallel_hits_lose_no_entries_and_cause_no_read_errors(): void
    {
        $this->seedNotFound(self::SEED);
        $log = $this->storage . '/workers.log';

        $env = [
            'REDIRECTS_TEST_STORAGE' => $this->storage,
            'REDIRECTS_TEST_LOG' => $log,
            'REDIRECTS_TEST_CONFIG' => json_encode($this->config()),
            'PATH' => getenv('PATH'),
        ];

        $processes = [];
        for ($w = 0; $w < self::WORKERS; $w++) {
            $processes[$w] = proc_open(
                [PHP_BINARY, __DIR__ . '/../workers/hit.php', (string) $w, (string) self::HITS_PER_WORKER],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes[$w],
                null,
                $env
            );
        }

        $failures = [];
        foreach ($processes as $w => $process) {
            $out = stream_get_contents($pipes[$w][1]) . stream_get_contents($pipes[$w][2]);
            if (proc_close($process) !== 0 || trim($out) !== '') {
                $failures[] = "worker {$w}: " . trim($out);
            }
        }

        $errors = file_exists($log) ? array_filter(file($log), fn ($l) => str_starts_with($l, 'ERROR')) : [];
        $urls = array_column($this->readNotFound(), 'url');

        // One assertion over everything, so a failure shows the full picture.
        $this->assertSame([
            'worker failures' => [],
            'logged errors' => [],
            'entries' => self::SEED + self::WORKERS * self::HITS_PER_WORKER,
            'unique entries' => self::SEED + self::WORKERS * self::HITS_PER_WORKER,
        ], [
            'worker failures' => $failures,
            'logged errors' => array_values(array_unique($errors)),
            'entries' => count($urls),
            'unique entries' => count(array_unique($urls)),
        ]);
    }
}
