<?php

namespace TFD\Redirects\Modules\NotFound;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use TFD\Redirects\Storage\StorageException;
use TFD\Redirects\Storage\YamlFileStore;

class NotFoundRepository
{
    public const DEFAULT_MAX_ENTRIES = 1000;

    public const DEFAULT_LOCK_TIMEOUT = 0.25;

    /**
     * Loaded lazily: the repository is resolved on every request via the
     * middleware, but the list is only needed when a 404 is handled or the
     * CP listing is shown.
     *
     * @var Collection|null
     */
    protected $pages;

    /**
     * @var string
     */
    protected $storage_key;

    /**
     * @var YamlFileStore|null
     */
    protected $store;

    public function __construct(string $storage_key = 'not_found_pages')
    {
        $this->storage_key = $storage_key;
    }

    protected function storagePath()
    {
        return storage_path('statamic/addons/statamic-redirects/' . $this->storage_key . '.yaml');
    }

    protected function store(): YamlFileStore
    {
        return $this->store ?? $this->store = new YamlFileStore($this->storagePath());
    }

    protected function pages(): Collection
    {
        return $this->pages ?? $this->pages = $this->getFromFile();
    }

    public static function trackingEnabled(): bool
    {
        return (bool) config('statamic-redirects.not_found.enabled', true);
    }

    public function get($url)
    {
        return $this->pages()->firstWhere('url', $url);
    }

    public function all()
    {
        return collect($this->pages());
    }

    public function create($data)
    {
        $this->mutate(function (Collection $pages) use ($data) {
            return $pages->push($data);
        }, $data['url'] ?? null);
    }

    public function exists($url)
    {
        return $this->pages()->contains('url', $url);
    }

    public function update($url, $data)
    {
        $this->mutate(function (Collection $pages) use ($url, $data) {
            if (!$pages->contains('url', $url)) {
                $pages->push(['url' => $url]);
            }

            return $pages->map(function ($page) use ($url, $data) {
                if (($page['url'] ?? null) === $url) {
                    $page = collect($page)->merge(collect($data))->toArray();
                }

                return $page;
            });
        }, $url);
    }

    public function delete($url)
    {
        if (!$this->exists($url)) {
            throw new Exception(__('statamic-redirects::default.exceptions.not_found'));
        }

        $this->mutate(function (Collection $pages) use ($url) {
            if (!$pages->contains('url', $url)) {
                return null;
            }

            return $pages->reject(function ($page) use ($url) {
                return ($page['url'] ?? null) === $url;
            })->values();
        });
    }

    // Increase hit counter - simple statistics
    public function hit($url)
    {
        if (!static::trackingEnabled()) {
            return;
        }

        // Increment inside the lock, based on the freshly read file, so that
        // concurrent hits are never lost. Under heavy contention (e.g. a
        // scanner flood) the hit is dropped after the lock timeout instead of
        // tying up PHP workers: it is statistics, not content.
        $this->mutate(function (Collection $pages) use ($url) {
            $found = false;
            $pages = $pages->map(function ($page) use ($url, &$found) {
                if (($page['url'] ?? null) === $url) {
                    $found = true;
                    $page['hits'] = ($page['hits'] ?? 0) + 1;
                    $page['last_hit'] = time();
                }

                return $page;
            });

            if (!$found) {
                $pages->push(['url' => $url, 'hits' => 1, 'last_hit' => time()]);
            }

            return $pages;
        }, $url, (float) config('statamic-redirects.not_found.lock_timeout', static::DEFAULT_LOCK_TIMEOUT));
    }

    public function getFromFile()
    {
        try {
            return collect($this->store()->read());
        } catch (\Throwable $th) {
            Log::error("Error reading from the not_found_pages yaml file at `{$this->storagePath()}`.", [
                'exception' => $th->getMessage(),
            ]);
        }

        return collect();
    }

    /**
     * Read-modify-write under an exclusive lock. If the file on disk cannot be
     * parsed, nothing is written.
     *
     * @param callable $callback receives the fresh list, returns the new list or null for no change
     * @param string|null $keep URL that must survive the size limit (the entry just touched)
     * @param float|null $timeout lock timeout in seconds, null waits indefinitely
     */
    protected function mutate(callable $callback, $keep = null, $timeout = null): bool
    {
        try {
            $stored = $this->store()->transaction(function (array $pages) use ($callback, $keep) {
                $result = $callback(collect($pages));

                return $result === null ? null : $this->limit(collect($result), $keep)->values()->toArray();
            }, $timeout);
        } catch (StorageException $e) {
            Log::error('Not found entry was not saved, the not_found_pages yaml file was left untouched.', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }

        if ($stored === null) {
            Log::debug("Not found entry for `{$keep}` was dropped: timed out waiting for the file lock.");

            return false;
        }

        $this->pages = collect($stored);

        return true;
    }

    /**
     * Caps the number of stored entries (statamic-redirects.not_found.max_entries).
     *
     * Evicts the entries with the fewest hits first, among those the least
     * recently hit (entries from before `last_hit` existed count as oldest).
     * Frequently requested 404s are the actionable ones (candidates for a
     * redirect) while scanners produce masses of unique one-hit URLs; evicting
     * by age alone would let such a burst flush out the important entries.
     * The entry just hit is never evicted, so a new URL always gets a chance
     * to collect a second hit.
     */
    protected function limit(Collection $pages, $keep = null): Collection
    {
        $max = (int) config('statamic-redirects.not_found.max_entries', static::DEFAULT_MAX_ENTRIES);
        $excess = $pages->count() - $max;

        if ($max <= 0 || $excess <= 0) {
            return $pages;
        }

        $evict = $pages
            ->map(function ($page, $index) {
                return [
                    'index' => $index,
                    'url' => $page['url'] ?? null,
                    'hits' => (int) ($page['hits'] ?? 0),
                    'last_hit' => (int) ($page['last_hit'] ?? 0),
                ];
            })
            ->reject(function ($candidate) use ($keep) {
                return $keep !== null && $candidate['url'] === $keep;
            })
            ->sort(function ($a, $b) {
                return [$a['hits'], $a['last_hit'], $a['index']] <=> [$b['hits'], $b['last_hit'], $b['index']];
            })
            ->take($excess)
            ->pluck('index')
            ->flip();

        return $pages->reject(function ($page, $index) use ($evict) {
            return $evict->has($index);
        });
    }
}
