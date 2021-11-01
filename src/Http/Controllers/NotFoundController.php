<?php

namespace TFD\Redirects\Http\Controllers;

use Statamic\Http\Controllers\CP\CpController;
use TFD\Redirects\Modules\NotFound\NotFoundRepository;

class NotFoundController extends CpController
{

    protected $repository;

    public function __construct(NotFoundRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        $items = $this->repository->all()->sortByDesc('hits');

        return view('statamic-redirects::notfound.index', [
            'title' => __('statamic-redirects::default.nav.not_found'),
            'items' => $items,
        ]);
    }

}