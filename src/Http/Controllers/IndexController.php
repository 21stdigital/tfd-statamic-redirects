<?php

namespace TFD\Redirects\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use TFD\Redirects\Facades\Module;

class IndexController extends CpController
{
    public function index()
    {
        return view('statamic-redirects::index', [
            'modules' => Module::all(),
        ]);
    }

    protected function getModules()
    {

        // url, icon, title, description   
    }
}
