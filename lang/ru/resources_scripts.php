<?php

return [
    'label' => 'Скрипты',
    'singular' => 'Скрипт',
    'columns' => [
        'id' => 'ID',
        'status' => 'Статус',
        'title' => 'Название',
        'key' => 'Ключ',
        'position' => 'Позиция',
        'order' => 'Порядок',
    ],
    'fields' => [
        'status' => 'Активен',
        'title' => 'Название скрипта',
        'key' => 'Уникальный ключ',
        'position' => 'Позиция вывода',
        'content' => 'Код скрипта',
        'options' => 'Опции (JSON)',
        'order' => 'Порядок сортировки',
    ],
    'positions' => [
        'head' => 'В <head>',
        'body_start' => 'В начале <body>',
        'body_end' => 'Перед </body>',
    ],
];
