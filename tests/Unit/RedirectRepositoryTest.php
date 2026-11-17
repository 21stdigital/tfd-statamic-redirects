<?php

namespace TFD\Redirects\Tests\Unit;

use Symfony\Component\Yaml\Yaml;
use TFD\Redirects\Modules\Redirect\RedirectRepository;
use TFD\Redirects\Storage\StorageException;
use TFD\Redirects\Tests\TestCase;

class RedirectRepositoryTest extends TestCase
{
    private function seedRedirects(int $count): array
    {
        $redirects = [];
        for ($i = 0; $i < $count; $i++) {
            $redirects[] = [
                'source' => "old/{$i}", 'target' => "/new/{$i}", 'status' => 301,
                'active' => true, 'start' => null, 'end' => null, 'id' => "id-{$i}",
            ];
        }
        file_put_contents($this->redirectsFile(), Yaml::dump($redirects, 100, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        return $redirects;
    }

    public function test_crud_and_hits(): void
    {
        $this->seedRedirects(2);
        $repo = new RedirectRepository();

        $id = $repo->create(['source' => 'a', 'target' => '/b', 'status' => 302]);
        $repo->hit($id);
        $repo->hit($id);
        $repo->update('id-0', ['source' => 'old/0', 'target' => '/changed']);
        $repo->delete('id-1');

        $fresh = new RedirectRepository();
        $this->assertSame(['id-0', $id], $fresh->all()->pluck('id')->all());
        $this->assertSame(2, $fresh->get($id)['hits']);
        $this->assertSame('/changed', $fresh->getBySource('old/0')['target']);
        $this->assertSame(301, $fresh->get('id-0')['status']);
        $this->assertTrue($fresh->sourceExists('a'));
    }

    public function test_duplicate_source_is_rejected(): void
    {
        $this->seedRedirects(1);
        $this->expectExceptionMessage('source_already_exists');

        (new RedirectRepository())->create(['source' => 'old/0', 'target' => '/x']);
    }

    public function test_corrupt_file_is_not_overwritten(): void
    {
        $corrupt = "- source: \"old\n  target: [\n";
        file_put_contents($this->redirectsFile(), $corrupt);

        try {
            (new RedirectRepository())->create(['source' => 'a', 'target' => '/b']);
            $this->fail('Expected a StorageException');
        } catch (StorageException $e) {
        }

        $this->assertSame($corrupt, file_get_contents($this->redirectsFile()));
    }

    public function test_parallel_hits_on_redirects_are_not_lost(): void
    {
        $this->seedRedirects(200);
        $workers = 10;
        $hits = 10;

        $processes = [];
        for ($w = 0; $w < $workers; $w++) {
            $processes[$w] = proc_open(
                [PHP_BINARY, __DIR__ . '/../workers/redirect-hit.php', 'id-7', (string) $hits],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes[$w],
                null,
                ['REDIRECTS_TEST_STORAGE' => $this->storage, 'PATH' => getenv('PATH')]
            );
        }
        $failures = [];
        foreach ($processes as $w => $process) {
            $out = stream_get_contents($pipes[$w][1]) . stream_get_contents($pipes[$w][2]);
            if (proc_close($process) !== 0) {
                $failures[] = trim($out);
            }
        }

        $fresh = new RedirectRepository();
        $this->assertSame(
            ['failures' => [], 'redirects' => 200, 'hits' => $workers * $hits],
            ['failures' => $failures, 'redirects' => $fresh->all()->count(), 'hits' => $fresh->get('id-7')['hits'] ?? 0]
        );
    }
}
