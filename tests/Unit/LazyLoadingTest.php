<?php

namespace TFD\Redirects\Tests\Unit;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use TFD\Redirects\Http\Middleware\Redirects;
use TFD\Redirects\Modules\NotFound\NotFoundRepository;
use TFD\Redirects\Modules\Redirect\RedirectRepository;
use TFD\Redirects\Tests\TestCase;

/**
 * The middleware runs on every request of the statamic.web group. It must not
 * read (let alone parse) the storage files unless a 404 is actually handled.
 *
 * Reads are detected by making the files unreadable: any read attempt either
 * throws or gets logged as an error.
 */
class LazyLoadingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (function_exists('posix_geteuid') && posix_geteuid() === 0) {
            $this->markTestSkipped('File permissions are not enforced for root.');
        }

        $this->seedNotFound(10);
        file_put_contents($this->redirectsFile(), "[]\n");
        chmod($this->notFoundFile(), 0000);
        chmod($this->redirectsFile(), 0000);
    }

    public function test_a_200_response_does_not_touch_the_storage_files(): void
    {
        // Resolved like the container does for every request.
        $middleware = new Redirects(new RedirectRepository(), new NotFoundRepository());

        $response = $middleware->handle(Request::create('/some-page'), fn () => new Response('ok', 200));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([], $this->logger()->messages(), 'Storage was read for a 200 response');
    }

    public function test_constructing_the_repositories_does_not_read_the_files(): void
    {
        new NotFoundRepository();
        new RedirectRepository();

        $this->assertSame([], $this->logger()->messages());
    }
}
