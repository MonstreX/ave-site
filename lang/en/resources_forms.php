<?php

return [
    'label' => 'Forms',
    'singular' => 'Form',
    'columns' => [
        'title' => 'Title',
        'key' => 'Key',
        'status' => 'Status',
        'order' => 'Order',
    ],
    'fields' => [
        'title' => 'Title',
        'key' => 'Key',
        'status' => 'Status',
        'order' => 'Order',
        'content' => 'Form Template (Liquid)',
        'details' => 'Options (JSON: validator, messages, to_address)',
    ],
];
