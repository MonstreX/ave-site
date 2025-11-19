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
        'elements' => 'Elements',
        'elements_help' => 'Gallery of elements with images, links and metadata',
    ],
    'element_fields' => [
        'title' => 'Title',
        'image' => 'Image',
        'alt' => 'Alt Text',
        'subtitle' => 'Subtitle',
        'link' => 'Link',
        'html' => 'HTML Code',
    ],
    'add_element' => 'Add Element',
    'delete_element' => 'Delete Element',
];
