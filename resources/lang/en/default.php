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

    'field_instructions' => [
        'source' => 'The URL to redirect from.',
        'target' => 'The URL/Entry to redirect to.',
        'active' => 'Is this redirect active?',
        'start' => 'The redirect will be applied only after the start date. Leave blank to not set a start date.',
        'end' => 'The redirect will be applied only before the end date. Leave blank to not set an end date.',
        'status' => 'A **301** redirect will be permanent and cached by the user\'s browser. Use a **302** redirect to create a temporary redirect.',
        'notes' => 'What is this redirect used for?',
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
    ],
];
