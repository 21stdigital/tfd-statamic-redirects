<?php

/*
 * Minimal Laravel-like environment for the storage layer tests:
 * a container with `config` and `log`, plus the storage_path()/config()
 * helpers that normally come from laravel/framework.
 *
 * Storage directory: env REDIRECTS_TEST_STORAGE (set per test / per worker).
 * Log file (optional): env REDIRECTS_TEST_LOG.
 */

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Config\Repository as Config;
use Illuminate\Container\Container;
use Illuminate\Support\Facades\Facade;
use TFD\Redirects\Tests\Support\TestLogger;

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        $base = rtrim(getenv('REDIRECTS_TEST_STORAGE') ?: sys_get_temp_dir(), '/');

        return $path === '' ? $base : $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('config')) {
    function config($key = null, $default = null)
    {
        $config = Container::getInstance()->make('config');

        if ($key === null) {
            return $config;
        }
        if (is_array($key)) {
            $config->set($key);

            return null;
        }

        return $config->get($key, $default);
    }
}

if (!function_exists('__')) {
    function __($key = null, $replace = [], $locale = null)
    {
        return $key;
    }
}

function redirects_test_boot_container(array $config = []): Container
{
    $container = new Container();
    Container::setInstance($container);

    $container->instance('config', new Config($config));
    $container->instance('log', new TestLogger(getenv('REDIRECTS_TEST_LOG') ?: null));

    Facade::clearResolvedInstances();
    Facade::setFacadeApplication($container);

    return $container;
}

redirects_test_boot_container();
