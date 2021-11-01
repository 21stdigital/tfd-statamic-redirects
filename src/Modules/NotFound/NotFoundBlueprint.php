<?php

namespace TFD\Redirects\Modules\NotFound;

use Statamic\Facades\Blueprint;

class NotFoundBlueprint
{

    public static function get()
    {
        return Blueprint::makeFromFields([
            'url' => [
                'type' => 'text',
            ],
            'hits' => [
                'type' => 'text',
                'default' => 1,
            ],
        ]);
    }
}
