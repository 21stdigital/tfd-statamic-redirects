<?php

namespace TFD\Redirects;

use Statamic\Facades\Blueprint;

class RedirectBlueprint
{

    public static function get()
    {
        return Blueprint::makeFromFields([
            'source' => [
                'type' => 'text',
                'validate' => 'required',
            ],
            'target' => [
                'type' => 'link',
                'validate' => 'required',
            ],
            'active' => [
                'type' => 'toggle',
                'width' => 50,
            ],
            'status' => [
                'type' => 'radio',
                'inline' => true,
                'options' => ['301', '302'],
                'default' => '301',
                'width' => 50,
            ],
            'notes' => [
                'type' => 'textarea',
            ],
            'hits' => [
                'type' => 'hidden',
                'default' => 0,
            ],
        ]);
    }
}
