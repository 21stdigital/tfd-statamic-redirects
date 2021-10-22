<?php

namespace TFD\Redirects;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Providers\AddonServiceProvider;
use TFD\Redirects\Http\Middleware\Redirects;

class ServiceProvider extends AddonServiceProvider
{

    protected $routes = [
        'cp' => __DIR__ . '/../routes/cp.php',
    ];

    protected $middlewareGroups = [
        'statamic.web' => [
            Redirects::class,
        ]
    ];

    protected $scripts = [
        __DIR__ . '/../public/js/cp.js',
    ];

    public function boot()
    {
        parent::boot();

        $this->bootCpNavigation();
        $this->bootPermissions();
    }

    protected function bootCpNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->tools('Redirects')
                ->route('statamic-redirects.index')
                ->icon('array')
                ->can('view redirects')
                ->active('statamic-redirects');
        });
    }

    protected function bootPermissions()
    {
        $this->app->booted(function () {
            Permission::group('statamic-redirects', 'Redirects', function () {
                Permission::register('view redirects')->label(__('view'))
                ->children([
                    Permission::make('create redirects')->label(__('create')),
                    Permission::make('delete redirects')->label(__('delete')),
                ]);
            });
        });
    }
}
