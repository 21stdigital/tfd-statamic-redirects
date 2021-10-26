<?php

namespace TFD\Redirects;

use Carbon\Carbon;
use Statamic\Fieldtypes\Link;

class Redirect
{

    protected $data;

    protected $repository;

    public function __construct($data, RedirectRepository $redirectRepository)
    {
        $this->data = $data;
        $this->repository = $redirectRepository;
    }

    public function getId()
    {
        return $this->data['id'];
    }

    public function generateTargetUrl()
    {
        $fields = RedirectBlueprint::get()->fields()->addValues($this->data)->process();
        $targetField = $fields->get('target');
        $targetValue = $targetField->value();

        $link = new Link();
        $link->setField($targetField);

        return $link->augment($targetValue);
    }

    public function hit()
    {
        $this->repository->hit($this->getId());
    }

    public function isActive()
    {
        if (!$this->data['active']) {
            return false;
        }

        $start = $this->data['start'];
        $end = $this->data['end'];

        $afterStart = ($start === null) ? true : now()->greaterThan(Carbon::createFromTimeString($start));
        $beforeEnd = ($end === null) ? true : now()->lessThan(Carbon::createFromTimeString($end));

        return $afterStart && $beforeEnd;
    }
}
