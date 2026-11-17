<?php

namespace TFD\Redirects\Modules\Redirect;

use Exception;
use Illuminate\Support\Collection;
use Statamic\Support\Str;
use TFD\Redirects\Storage\YamlFileStore;

class RedirectRepository
{

    /**
     * Loaded lazily: the repository is resolved on every request via the
     * middleware, but the redirects are only needed when a 404 is handled or
     * in the CP.
     *
     * @var Collection|null
     */
    protected $redirects;

    /**
     * @var string
     */
    protected $storage_key;

    /**
     * @var YamlFileStore|null
     */
    protected $store;

    public function __construct(string $storage_key = 'redirects')
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

    protected function redirects(): Collection
    {
        return $this->redirects ?? $this->redirects = $this->getRedirectsFromFile();
    }

    public function get($id)
    {
        return $this->redirects()->firstWhere('id', $id);
    }

    public function getBySource($source)
    {
        return $this->redirects()->firstWhere('source', $source);
    }

    public function all()
    {
        return collect($this->redirects());
    }

    public function create($data)
    {
        if ($this->sourceExists($data['source'])) {
            throw new Exception(__('statamic-redirects::default.exceptions.source_already_exists'));
        }

        if ($data['source'] === $data['target']) {
            throw new Exception(__('statamic-redirects::default.exceptions.different_source_and_target'));
        }

        $data['id'] = Str::uuid()->toString();

        $this->mutate(function (Collection $redirects) use ($data) {
            // Re-check against the fresh file: another request may have added it meanwhile.
            if ($redirects->contains('source', $data['source'])) {
                throw new Exception(__('statamic-redirects::default.exceptions.source_already_exists'));
            }

            return $redirects->push($data);
        });

        return $data['id'];
    }

    public function exists($id)
    {
        return $this->redirects()->contains('id', $id);
    }

    public function sourceExists($source)
    {
        return $this->redirects()->contains('source', $source);
    }

    public function update($id, $data)
    {
        if ($data['source'] === $data['target']) {
            throw new Exception(__('statamic-redirects::default.exceptions.different_source_and_target'));
        }

        $this->mutate(function (Collection $redirects) use ($id, $data) {
            return $redirects->map(function ($redirect) use ($id, $data) {
                if (($redirect['id'] ?? null) === $id) {
                    $redirect = collect($redirect)->merge(collect($data))->toArray();
                }

                return $redirect;
            });
        });
    }

    public function delete($id)
    {
        if (!$this->exists($id)) {
            throw new Exception(__('statamic-redirects::default.exceptions.not_found'));
        }

        $this->mutate(function (Collection $redirects) use ($id) {
            return $redirects->reject(function ($redirect) use ($id) {
                return ($redirect['id'] ?? null) === $id;
            })->values();
        });
    }

    // Increase hit counter - simple statistics
    public function hit($id)
    {
        // Incremented inside the lock on the fresh file so concurrent hits are not lost.
        $this->mutate(function (Collection $redirects) use ($id) {
            return $redirects->map(function ($redirect) use ($id) {
                if (($redirect['id'] ?? null) === $id) {
                    $redirect['hits'] = ($redirect['hits'] ?? 0) + 1;
                }

                return $redirect;
            });
        });
    }

    public function getRedirectsFromFile()
    {
        return collect($this->store()->read());
    }

    /**
     * Read-modify-write under an exclusive lock, starting from the current
     * file. A corrupt file raises a StorageException and is not overwritten.
     */
    protected function mutate(callable $callback): void
    {
        $stored = $this->store()->transaction(function (array $redirects) use ($callback) {
            return $callback(collect($redirects))->values()->toArray();
        });

        $this->redirects = collect($stored);
    }
}
