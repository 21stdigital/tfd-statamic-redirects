<?php

namespace TFD\Redirects;

use Exception;
use Illuminate\Support\Collection;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Support\Str;

class RedirectRepository
{

    /**
     * @var Collection
     */
    protected $redirects;

    /**
     * @var string
     */
    protected $storage_key;

    public function __construct(string $storage_key = 'redirects')
    {
        $this->storage_key = $storage_key;
        $this->redirects = $this->getRedirectsFromFile();
    }

    protected function storagePath()
    {
        return storage_path('statamic/addons/statamic-redirects/' . $this->storage_key . '.yaml');
    }

    public function get($id)
    {
        return $this->redirects->firstWhere('id', $id);
    }

    public function getBySource($source)
    {
        return $this->redirects->firstWhere('source', $source);
    }

    public function all()
    {
        return collect($this->redirects);
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

        $this->redirects->push($data);
        $this->writeToFile();

        return $data['id'];
    }

    public function exists($id)
    {
        return $this->redirects->contains('id', $id);
    }

    public function sourceExists($source)
    {
        return $this->redirects->contains('source', $source);
    }

    public function update($id, $data)
    {
        $redirect = $this->get($id);

        $existing_data = collect($redirect);
        $combined_data = $existing_data->merge(collect($data));

        $this->redirects = $this->redirects->map(function ($redirect) use ($id, $combined_data) {
            if ($redirect['id'] === $id) {
                $redirect = $combined_data;
            }

            return $redirect;
        });

        $this->writeToFile();
    }

    public function delete($id)
    {
        if (!$this->exists($id)) {
            throw new Exception(__('statamic-redirects::default.exceptions.not_found'));
        }

        $this->redirects = $this->redirects->reject(function ($redirect) use ($id) {
            return $redirect['id'] === $id;
        })->values();

        $this->writeToFile();
    }

    // Increase hit counter - simple statistics
    public function hit($id)
    {
        $redirect  = $this->get($id);
        $hits = $redirect['hits'] ?? 0;

        $redirect['hits'] = ++$hits;
        
        $this->update($id, $redirect);
    }

    public function getRedirectsFromFile()
    {
        return collect(YAML::parse(File::get($this->storagePath())));
    }

    private function writeToFile()
    {
        File::put($this->storagePath(), YAML::dump($this->redirects->toArray()));
    }
}
