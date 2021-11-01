<?php

namespace TFD\Redirects;

use Statamic\Facades\CP\Nav;
use Statamic\Facades\Permission;
use Statamic\Http\Controllers\API\NotFoundController;
use Statamic\Providers\AddonServiceProvider;
use TFD\Redirects\Facades\Module;
use TFD\Redirects\Http\Controllers\RedirectsController;
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
        $this->bootModules();
    }

    protected function bootCpNavigation()
    {
        Nav::extend(function ($nav) {
            $nav->tools('Redirects')
                ->route('statamic-redirects::index')
                ->icon('array')
                ->can('view redirects')
                ->active('statamic-redirects')
                ->children([
                    $nav->item(__('statamic-redirects::default.nav.redirects'))->route('statamic-redirects::redirects.index'),
                    $nav->item(__('statamic-redirects::default.nav.not_found'))->route('statamic-redirects::notfound.index'),
                ]);
        });

        // $nav->item(__('seo-pro::messages.reports'))->route('seo-pro.reports.index')->can('view seo reports'),
        // $nav->item(__('seo-pro::messages.site_defaults'))->route('seo-pro.site-defaults.edit')->can('edit seo site defaults'),
        // $nav->item(__('seo-pro::messages.section_defaults'))->route('seo-pro.section-defaults.index')->can('edit seo section defaults'),
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

    protected function bootModules()
    {
        Module::make('redirects')
                ->action([RedirectsController::class, 'index'])
                ->title('Redirects title')
                ->icon('cache')
                ->navTitle('Redirects navTitle')
                ->description('Redirects description')
                ->routes(function($router) {
                    
                })
                ->register();

        Module::make('notfound')
        ->action([NotFoundController::class, 'index'])
        ->title('Not Found title')
        ->icon('cache')
        ->navTitle('Not Found navTitle')
        ->description('Not Found description')
        ->routes(function($router) {

        })
        ->register();
    }
}
