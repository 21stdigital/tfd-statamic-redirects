<?php

namespace TFD\Redirects\Tests\Unit;

use TFD\Redirects\Modules\NotFound\NotFoundRepository;
use TFD\Redirects\Tests\TestCase;

/**
 * A storage file that exists but cannot be parsed must never be treated as an
 * empty list and overwritten.
 */
class CorruptFileTest extends TestCase
{
    private const CORRUPT = "- url: https://example.test/a\n  hits: 3\n- url: \"https://example.test/b\n  hits: [\n";

    public function test_hit_does_not_overwrite_a_corrupt_file(): void
    {
        file_put_contents($this->notFoundFile(), self::CORRUPT);

        (new NotFoundRepository())->hit('https://example.test/new');

        $this->assertSame(self::CORRUPT, file_get_contents($this->notFoundFile()));
        $this->assertNotEmpty($this->logger()->messages('error'), 'The aborted write must be logged');
    }

    public function test_delete_does_not_overwrite_a_corrupt_file(): void
    {
        file_put_contents($this->notFoundFile(), self::CORRUPT);

        try {
            (new NotFoundRepository())->delete('https://example.test/a');
        } catch (\Throwable $e) {
            // Throwing is fine (the CP shows an error), overwriting is not.
        }

        $this->assertSame(self::CORRUPT, file_get_contents($this->notFoundFile()));
    }
}
