<?php

namespace Statamic\Facades;

/**
 * Test stub mirroring the parts of Statamic's File facade the addon uses.
 * The real implementation (Statamic\Filesystem\Filesystem) returns the default
 * for missing files and throws for unreadable ones.
 */
class File
{
    public static function get($path, $default = null)
    {
        if (!file_exists($path)) {
            return $default;
        }

        $contents = @file_get_contents($path);
        if ($contents === false) {
            throw new \RuntimeException("Unable to read file at {$path}");
        }

        return $contents;
    }

    public static function put($path, $contents)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        return file_put_contents($path, $contents);
    }
}
