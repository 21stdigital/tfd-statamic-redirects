<?php

namespace TFD\Redirects\Storage;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

/**
 * A YAML file holding a list, safe for concurrent PHP processes.
 *
 * - Readers never see a partially written file: writes go to a temp file in
 *   the same directory which is then rename()d over the target (atomic on
 *   POSIX filesystems).
 * - Read-modify-write runs under an exclusive flock() on a separate lock file
 *   (the data file itself is replaced on every write, so it cannot carry the
 *   lock) and always starts from a fresh read.
 * - A file that exists but cannot be read or parsed raises a StorageException
 *   instead of being treated as empty, so it is never overwritten.
 */
class YamlFileStore
{
    /** @var string */
    protected $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @throws StorageException if the file exists but is unreadable or not a valid YAML list
     */
    public function read(): array
    {
        clearstatcache(true, $this->path);

        if (!file_exists($this->path)) {
            return [];
        }

        $contents = @file_get_contents($this->path);
        if ($contents === false) {
            throw new StorageException("Unable to read `{$this->path}`.");
        }

        if (trim($contents) === '') {
            return [];
        }

        try {
            $data = Yaml::parse($contents);
        } catch (ParseException $e) {
            throw new StorageException("Unable to parse `{$this->path}`: {$e->getMessage()}", 0, $e);
        }

        if ($data === null) {
            return [];
        }

        if (!is_array($data)) {
            throw new StorageException("Unexpected content in `{$this->path}`: expected a list.");
        }

        return $data;
    }

    /**
     * Runs a read-modify-write cycle under an exclusive lock.
     *
     * $callback receives the current file contents (freshly read inside the
     * lock) and returns the new contents, or null to leave the file untouched.
     * Exceptions thrown by the callback release the lock and propagate.
     *
     * @param float|null $timeout seconds to wait for the lock; null waits indefinitely
     * @return array|null the data now stored, or null if the lock was not acquired in time
     * @throws StorageException if the current file is corrupt or the write fails
     */
    public function transaction(callable $callback, ?float $timeout = null): ?array
    {
        $this->ensureDirectory();

        $lock = @fopen($this->path . '.lock', 'c');
        if ($lock === false) {
            throw new StorageException("Unable to open lock file `{$this->path}.lock`.");
        }

        try {
            if (!$this->acquire($lock, $timeout)) {
                return null;
            }

            $current = $this->read();
            $new = $callback($current);

            if ($new === null) {
                return $current;
            }

            $this->write($new);

            return $new;
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    protected function acquire($lock, ?float $timeout): bool
    {
        if ($timeout === null) {
            return flock($lock, LOCK_EX);
        }

        $deadline = microtime(true) + $timeout;
        do {
            if (flock($lock, LOCK_EX | LOCK_NB)) {
                return true;
            }
            usleep(random_int(2000, 10000));
        } while (microtime(true) < $deadline);

        return false;
    }

    protected function write(array $data): void
    {
        // Same format as Statamic's YAML::dump(). The round trip guards against
        // ever persisting YAML that cannot be read back.
        $yaml = Yaml::dump($data, 100, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);

        try {
            Yaml::parse($yaml);
        } catch (ParseException $e) {
            throw new StorageException("Refusing to write unparsable YAML to `{$this->path}`: {$e->getMessage()}", 0, $e);
        }

        $dir = dirname($this->path);
        $tmp = @tempnam($dir, '.' . basename($this->path) . '.');
        if ($tmp === false) {
            throw new StorageException("Unable to create a temporary file in `{$dir}`.");
        }

        try {
            if (@file_put_contents($tmp, $yaml) !== strlen($yaml)) {
                throw new StorageException("Unable to write temporary file `{$tmp}`.");
            }

            // tempnam() creates the file with 0600; keep the previous mode so
            // other users (CLI vs. PHP-FPM) can still read it.
            $mode = file_exists($this->path) ? (fileperms($this->path) & 0777) : (0666 & ~umask());
            @chmod($tmp, $mode);

            if (!@rename($tmp, $this->path)) {
                throw new StorageException("Unable to move `{$tmp}` to `{$this->path}`.");
            }
        } finally {
            if (file_exists($tmp)) {
                @unlink($tmp);
            }
        }
    }

    protected function ensureDirectory(): void
    {
        $dir = dirname($this->path);

        if (!is_dir($dir) && !@mkdir($dir, 0755, true) && !is_dir($dir)) {
            throw new StorageException("Unable to create directory `{$dir}`.");
        }
    }
}
