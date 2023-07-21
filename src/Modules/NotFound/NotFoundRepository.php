<?php

namespace TFD\Redirects\Modules\NotFound;

use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

class NotFoundRepository
{

    /**
     * @var Collection
     */
    protected $pages;

    /**
     * @var string
     */
    protected $storage_key;

    public function __construct(string $storage_key = 'not_found_pages')
    {
        $this->storage_key = $storage_key;
        $this->pages = $this->getFromFile();
    }

    protected function storagePath()
    {
        return storage_path('statamic/addons/statamic-redirects/' . $this->storage_key . '.yaml');
    }

    public function get($url)
    {
        return $this->pages->firstWhere('url', $url);
    }

    public function all()
    {
        return collect($this->pages);
    }

    public function create($data)
    {
        $this->pages->push($data);
        $this->writeToFile();
    }

    public function exists($url)
    {
        return $this->pages->contains('url', $url);
    }

    public function update($url, $data)
    {
        if (!$this->exists($url)) {
            $this->create(['url' => $url]);
        }
        $page = $this->get($url);

        $existing_data = collect($page);
        $combined_data = $existing_data->merge(collect($data));

        $this->pages = $this->pages->map(function ($page) use ($url, $combined_data) {
            if ($page['url'] === $url) {
                $page = $combined_data;
            }

            return $page;
        });

        $this->writeToFile();
    }

    public function delete($url)
    {
        if (!$this->exists($url)) {
            throw new Exception(__('statamic-redirects::default.exceptions.not_found'));
        }

        $this->pages = $this->pages->reject(function ($page) use ($url) {
            return $page['url'] === $url;
        })->values();

        $this->writeToFile();
    }

    // Increase hit counter - simple statistics
    public function hit($url)
    {
        $page  = $this->get($url);
        $hits = $page['hits'] ?? 0;

        $page['hits'] = ++$hits;

        $this->update($url, $page);
    }

    public function getFromFile()
    {
        try {
            return collect(YAML::parse(File::get($this->storagePath())));
        } catch (\Throwable $th) {
            Log::error("Error reading from the not_found_pages yaml file at `{$this->storagePath()}`.");
        }

        return collect();
    }

    private function writeToFile()
    {
        /**
         * There were several problems with invalid yaml in the past.
         * Therefore we first create the yaml string from the pages and then try to parse the string.
         * If it fails, a log entry is created and the faulty yaml string is not written into the storage file.
         */
        $newYaml = YAML::dump($this->pages->toArray());

        try {
            SymfonyYaml::parse($newYaml);

            File::put($this->storagePath(), $newYaml);
        } catch (\Throwable $th) {
            Log::error('Error adding a new not found entry to the yaml file. The last 5 entries:', $this->pages->take(-5)->toArray());
        }
    }
}
