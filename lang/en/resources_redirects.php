<?php

return [
    'label' => 'Redirects',
    'singular' => 'Redirect',
    'columns' => [
        'id' => 'ID',
        'is_active' => 'Active',
        'from_url' => 'From',
        'to_url' => 'To',
        'status_code' => 'Code',
        'hits' => 'Hits',
    ],
    'fields' => [
        'is_active' => 'Active',
        'from_url' => 'Source URL',
        'to_url' => 'Destination URL',
        'status_code' => 'Status Code',
        'hits' => 'Hit Count',
        'last_hit_at' => 'Last Hit',
    ],
    'status_codes' => [
        '301' => 'Permanent',
        '302' => 'Temporary',
        '307' => 'Temporary (preserve method)',
        '308' => 'Permanent (preserve method)',
    ],
];
