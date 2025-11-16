<?php

return [
    'label' => 'Blocks',
    'singular' => 'Block',
    'rules_show' => 'Show on specified pages',
    'rules_hide' => 'Hide on specified pages',
    'columns' => [
        'title' => 'Title',
        'key' => 'Key',
        'region' => 'Region',
        'status' => 'Active',
        'order' => 'Order',
    ],
    'fields' => [
        'title' => 'Title',
        'key' => 'Key',
        'region' => 'Region',
        'status' => 'Active',
        'order' => 'Sort Order',
        'content' => 'Content',
        'urls' => 'URL Rules (one per line)',
        'rules' => 'Display Rule',
        'options' => 'Options (JSON)',
    ],
];
