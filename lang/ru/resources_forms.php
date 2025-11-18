<?php

return [
    'label' => 'Формы',
    'singular' => 'Форма',
    'columns' => [
        'title' => 'Название',
        'key' => 'Ключ',
        'status' => 'Статус',
        'order' => 'Порядок',
    ],
    'fields' => [
        'title' => 'Название',
        'key' => 'Ключ',
        'status' => 'Статус',
        'order' => 'Порядок',
        'content' => 'Шаблон формы (Liquid)',
        'details' => 'Опции (JSON: validator, messages, to_address)',
    ],
];
