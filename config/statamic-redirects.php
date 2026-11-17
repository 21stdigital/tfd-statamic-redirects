<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 404 tracking
    |--------------------------------------------------------------------------
    |
    | Every URL answered with a 404 is recorded (with a hit counter) in
    | storage/statamic/addons/statamic-redirects/not_found_pages.yaml and
    | listed in the CP under Tools > Redirects > Not Found.
    |
    | enabled:       Set to false to disable the tracking entirely.
    |
    | max_entries:   Upper bound for the number of stored URLs; every 404 rewrites
    |                the whole file, so this bounds the cost per request. When the
    |                limit is exceeded, the entries with the fewest hits are
    |                removed first (least recently hit among equals). Frequently
    |                requested URLs are kept, one-off URLs (e.g. from scanners)
    |                are dropped. 0 disables the limit (not recommended).
    |
    | lock_timeout:  Seconds a request waits for the file lock before the hit is
    |                dropped. Keeps PHP workers from piling up during 404 floods.
    |
    */

    'not_found' => [
        'enabled' => env('STATAMIC_REDIRECTS_NOT_FOUND_ENABLED', true),
        'max_entries' => (int) env('STATAMIC_REDIRECTS_NOT_FOUND_MAX_ENTRIES', 1000),
        'lock_timeout' => (float) env('STATAMIC_REDIRECTS_NOT_FOUND_LOCK_TIMEOUT', 0.25),
    ],

];
