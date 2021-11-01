<?php

namespace TFD\Redirects\Modules\Redirect;

use Statamic\Facades\Blueprint;

class RedirectBlueprint
{

    public static function get()
    {
        return Blueprint::makeFromFields([
            'source' => [
                'type' => 'text',
                'validate' => 'required',
                'instructions' => __('statamic-redirects::default.field_instructions.source'),
                'instructions_position' => 'below',
            ],
            'target' => [
                'type' => 'link',
                'validate' => 'required',
                'instructions' => __('statamic-redirects::default.field_instructions.target'),
                'instructions_position' => 'below',
            ],
            'active' => [
                'type' => 'toggle',
                'instructions' => __('statamic-redirects::default.field_instructions.active'),
                'instructions_position' => 'below',
            ],
            'start' => [
                'type' => 'date',
                'time_enabled' => true,
                'time_required' => true,
                'width' => 50,
                'if' => [
                    'active' => 'equals true',
                ],
                'instructions' => __('statamic-redirects::default.field_instructions.start'),
                'instructions_position' => 'below',
            ],
            'end' => [
                'type' => 'date',
                'time_enabled' => true,
                'time_required' => true,
                'width' => 50,
                'if' => [
                    'active' => 'equals true',
                ],
                'instructions' => __('statamic-redirects::default.field_instructions.end'),
                'instructions_position' => 'below',
            ],
            'status' => [
                'type' => 'radio',
                'inline' => true,
                'options' => ['301', '302'],
                'default' => '301',
                'width' => 50,
                'instructions' => __('statamic-redirects::default.field_instructions.status'),
                'instructions_position' => 'below',
            ],
            'notes' => [
                'type' => 'textarea',
                'instructions' => __('statamic-redirects::default.field_instructions.notes'),
                'instructions_position' => 'below',
            ],
            'hits' => [
                'type' => 'hidden',
                'default' => 0,
                'instructions' => __('statamic-redirects::default.field_instructions.hits'),
                'instructions_position' => 'below',
            ],
        ]);
    }
}
