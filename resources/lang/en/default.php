<?php

return [
    'title' => 'Redirects',

    'headlines' => [
        'index' => 'Redirects',
        'create' => 'Create new redirect',
        'edit' => 'Edit redirect',
    ],

    'explanations' => [
        'index' => 'Redirects are used to direct users to content which may have been removed or deleted.',
    ],

    'actions' => [
        'create' => 'Create a redirect',
    ],

    'columns' => [
        'source' => 'Source URL',
        'target' => 'Target URL',
        'active' => 'Active',
        'hits' => 'Hits',
        'notes' => 'Notes',
    ],

    'exceptions' => [
        'not_found' => 'Entry does not exists.',
        'source_already_exists' => 'Entry for source already exists.',
        'different_source_and_target' => 'Source and target URLs have to be different.',
    ]
];
