<?php

namespace TFD\Redirects\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Symfony\Component\Yaml\Yaml;
use TFD\Redirects\Tests\Support\TestLogger;

abstract class TestCase extends BaseTestCase
{
    protected string $storage;

    protected string $addonDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storage = sys_get_temp_dir() . '/tfd-redirects-test-' . bin2hex(random_bytes(6));
        $this->addonDir = $this->storage . '/statamic/addons/statamic-redirects';
        mkdir($this->addonDir, 0755, true);
        putenv('REDIRECTS_TEST_STORAGE=' . $this->storage);
        putenv('REDIRECTS_TEST_LOG');

        redirects_test_boot_container($this->config());
    }

    protected function tearDown(): void
    {
        if (is_dir($this->storage)) {
            exec('chmod -R u+rwx ' . escapeshellarg($this->storage) . ' && rm -rf ' . escapeshellarg($this->storage));
        }
        putenv('REDIRECTS_TEST_STORAGE');

        parent::tearDown();
    }

    /** Config passed to the container (and to worker processes). */
    protected function config(): array
    {
        return [];
    }

    protected function logger(): TestLogger
    {
        return \Illuminate\Container\Container::getInstance()->make('log');
    }

    protected function notFoundFile(): string
    {
        return $this->addonDir . '/not_found_pages.yaml';
    }

    protected function redirectsFile(): string
    {
        return $this->addonDir . '/redirects.yaml';
    }

    /** Writes a not-found list the way the addon (Statamic YAML::dump) does. */
    protected function seedNotFound(int $count, int $hits = 1): array
    {
        $pages = [];
        for ($i = 0; $i < $count; $i++) {
            $pages[] = ['url' => "https://example.test/seed/{$i}", 'hits' => $hits];
        }
        file_put_contents($this->notFoundFile(), Yaml::dump($pages, 100, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK));

        return $pages;
    }

    protected function readNotFound(): array
    {
        return Yaml::parse(file_get_contents($this->notFoundFile())) ?? [];
    }
}
