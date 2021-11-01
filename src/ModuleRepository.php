<?php

namespace TFD\Redirects;

use TFD\Redirects\Module as RedirectsModule;

class ModuleRepository
{
    protected $modules;

    public function __construct()
    {
        $this->modules = collect([]);
    }

    public function make($handle)
    {
        return (new RedirectsModule)->handle($handle);
    }

    public function push(RedirectsModule $module)
    {
        $this->modules[$module->handle()] = $module;

        return $this;
    }

    public function all()
    {
        return $this->modules;
    }
}