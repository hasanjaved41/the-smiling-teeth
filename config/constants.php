<?php


return [
    'enable_query_logging' => env('ENABLE_QUERY_LOGGING', false),
    'app_log_channel' => 'applog',
    'custom_encryption_key' => config('app.env') == 'production' ? 'qtp9gXdjwgaS6v9fEB2anhjsvsAJ86Fy' : 'c7NXhXMR4HnhqbuJK3pzCZKKFHBTCbDN', // For Web encryption
];