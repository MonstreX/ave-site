<?php

return [
    'label' => 'Scripts',
    'singular' => 'Script',
    'columns' => [
        'id' => 'ID',
        'status' => 'Status',
        'title' => 'Title',
        'key' => 'Key',
        'position' => 'Position',
        'order' => 'Order',
    ],
    'fields' => [
        'status' => 'Active',
        'title' => 'Script Title',
        'key' => 'Unique Key',
        'position' => 'Output Position',
        'content' => 'Script Code',
        'options' => 'Options (JSON)',
        'order' => 'Sort Order',
    ],
    'positions' => [
        'head' => 'In <head>',
        'body_start' => 'At <body> start',
        'body_end' => 'Before </body>',
    ],
];
