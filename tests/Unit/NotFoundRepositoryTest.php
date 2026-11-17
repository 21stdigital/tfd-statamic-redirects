<?php

namespace TFD\Redirects\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TFD\Redirects\Http\Middleware\Redirects;
use TFD\Redirects\Modules\NotFound\NotFoundRepository;
use TFD\Redirects\Modules\Redirect\RedirectRepository;
use TFD\Redirects\Tests\TestCase;

class NotFoundRepositoryTest extends TestCase
{
    private function setConfig(array $notFound): void
    {
        Container::getInstance()->make('config')->set('statamic-redirects.not_found', $notFound);
    }

    public function test_public_api_stays_compatible(): void
    {
        $repo = new NotFoundRepository();

        $repo->hit('https://example.test/a');
        $repo->hit('https://example.test/a');
        $repo->hit('https://example.test/b');
        $repo->update('https://example.test/c', ['hits' => 7]);
        $repo->create(['url' => 'https://example.test/d', 'hits' => 1]);

        $this->assertTrue($repo->exists('https://example.test/a'));
        $this->assertSame(2, $repo->get('https://example.test/a')['hits']);
        $this->assertSame(7, $repo->get('https://example.test/c')['hits']);
        $this->assertCount(4, $repo->all());

        $repo->delete('https://example.test/b');
        $this->assertFalse($repo->exists('https://example.test/b'));

        // A fresh instance (next request) sees the same state.
        $fresh = new NotFoundRepository();
        $this->assertSame(
            ['https://example.test/a', 'https://example.test/c', 'https://example.test/d'],
            $fresh->all()->pluck('url')->all()
        );
        $this->assertSame([], $this->logger()->messages('error'));
    }

    public function test_delete_of_unknown_url_throws(): void
    {
        $this->expectException(\Exception::class);

        (new NotFoundRepository())->delete('https://example.test/unknown');
    }

    public function test_hit_increments_entries_written_by_older_versions(): void
    {
        $this->seedNotFound(3, 5);

        (new NotFoundRepository())->hit('https://example.test/seed/1');

        $this->assertSame(6, (new NotFoundRepository())->get('https://example.test/seed/1')['hits']);
    }

    public function test_entry_count_is_capped_and_low_hit_entries_are_evicted_first(): void
    {
        $this->setConfig(['max_entries' => 3]);
        $repo = new NotFoundRepository();

        $repo->update('https://example.test/popular', ['hits' => 50, 'last_hit' => 1]);
        $repo->update('https://example.test/old-single', ['hits' => 1, 'last_hit' => 100]);
        $repo->update('https://example.test/newer-single', ['hits' => 1, 'last_hit' => 200]);

        $repo->hit('https://example.test/new');

        $this->assertSame(
            ['https://example.test/popular', 'https://example.test/newer-single', 'https://example.test/new'],
            (new NotFoundRepository())->all()->pluck('url')->all()
        );
    }

    public function test_a_new_url_is_not_evicted_by_its_own_hit(): void
    {
        $this->setConfig(['max_entries' => 2]);
        $repo = new NotFoundRepository();
        $repo->update('https://example.test/x', ['hits' => 10]);
        $repo->update('https://example.test/y', ['hits' => 10]);

        $repo->hit('https://example.test/new');
        $repo->hit('https://example.test/new');

        $this->assertSame(2, (new NotFoundRepository())->get('https://example.test/new')['hits']);
        $this->assertCount(2, (new NotFoundRepository())->all());
    }

    public function test_an_oversized_legacy_file_is_trimmed_on_the_next_write(): void
    {
        $this->setConfig(['max_entries' => 100]);
        $this->seedNotFound(500);

        (new NotFoundRepository())->hit('https://example.test/new');

        $this->assertCount(100, $this->readNotFound());
        $this->assertTrue((new NotFoundRepository())->exists('https://example.test/new'));
    }

    public function test_tracking_can_be_disabled(): void
    {
        $this->setConfig(['enabled' => false]);

        (new NotFoundRepository())->hit('https://example.test/a');

        $middleware = new Redirects(new RedirectRepository(), new NotFoundRepository());
        $response = $middleware->handle(Request::create('/missing'), fn () => new Response('', 404));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertFileDoesNotExist($this->notFoundFile());
    }

    public function test_middleware_records_a_404(): void
    {
        $middleware = new Redirects(new RedirectRepository(), new NotFoundRepository());

        $response = $middleware->handle(Request::create('http://example.test/missing'), fn () => new Response('', 404));

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame(1, (new NotFoundRepository())->get('http://example.test/missing')['hits']);
    }

    public function test_hit_is_dropped_when_the_lock_is_held_longer_than_the_timeout(): void
    {
        $this->setConfig(['lock_timeout' => 0.1]);
        $lock = fopen($this->notFoundFile() . '.lock', 'c');
        flock($lock, LOCK_EX);

        // flock() locks are per open file description, so a second fopen in
        // the same process contends like another process would.
        $start = microtime(true);
        (new NotFoundRepository())->hit('https://example.test/a');
        $elapsed = microtime(true) - $start;

        flock($lock, LOCK_UN);
        fclose($lock);

        $this->assertLessThan(1.0, $elapsed);
        $this->assertFileDoesNotExist($this->notFoundFile());
        $this->assertSame([], $this->logger()->messages('error'));
    }

    public function test_written_file_keeps_its_permissions_and_no_temp_files_remain(): void
    {
        $this->seedNotFound(1);
        chmod($this->notFoundFile(), 0664);

        (new NotFoundRepository())->hit('https://example.test/a');

        clearstatcache();
        $this->assertSame(0664, fileperms($this->notFoundFile()) & 0777);
        $this->assertSame(
            ['not_found_pages.yaml', 'not_found_pages.yaml.lock'],
            array_values(array_diff(scandir($this->addonDir), ['.', '..']))
        );
    }
}
