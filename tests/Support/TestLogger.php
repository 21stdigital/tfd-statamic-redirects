<?php

namespace TFD\Redirects\Tests\Support;

use Psr\Log\AbstractLogger;

/**
 * Records log entries in memory and, if a file is given, appends them as
 * single lines so that entries written by child processes can be inspected.
 */
class TestLogger extends AbstractLogger
{
    public array $records = [];

    public function __construct(private ?string $file = null)
    {
    }

    public function log($level, $message, array $context = []): void
    {
        $this->records[] = ['level' => $level, 'message' => (string) $message, 'context' => $context];

        if ($this->file) {
            file_put_contents(
                $this->file,
                strtoupper($level) . ' ' . str_replace("\n", ' ', (string) $message) . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }
    }

    public function messages(?string $level = null): array
    {
        return array_values(array_map(
            fn ($r) => $r['message'],
            array_filter($this->records, fn ($r) => $level === null || $r['level'] === $level)
        ));
    }
}
