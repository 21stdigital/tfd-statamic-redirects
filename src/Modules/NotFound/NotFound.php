<?php

namespace TFD\Redirects\Modules\NotFound;

class NotFound
{
    protected $data;

    protected $repository;

    public function __construct($data, NotFoundRepository $repository)
    {
        $this->data = $data;
        $this->repository = $repository;
    }

    public function getUrl()
    {
        return $this->data['url'];
    }

    public function hit()
    {
        $this->repository->hit($this->getUrl());
    }
}