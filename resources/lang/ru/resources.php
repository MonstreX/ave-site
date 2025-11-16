<?php

return [
    // Groups
    'resources_groups' => [
        'content' => 'Контент',
        'settings' => 'Настройки',
    ],

    // Pages
    'resources_pages' => [
        'label' => 'Страницы',
        'singular' => 'Страница',
        'no_parent' => '(Нет родителя)',
        'columns' => [
            'title' => 'Заголовок',
            'slug' => 'URL',
            'status' => 'Опубликована',
            'created_at' => 'Создана',
        ],
        'fields' => [
            'title' => 'Заголовок',
            'slug' => 'URL (slug)',
            'parent' => 'Родительская страница',
            'status' => 'Опубликована',
            'content' => 'Содержимое',
            'options' => 'Опции (JSON)',
            'seo_title' => 'SEO заголовок',
            'seo_keywords' => 'SEO ключевые слова',
            'seo_description' => 'SEO описание',
        ],
    ],

    // Blocks
    'resources_blocks' => [
        'label' => 'Блоки',
        'singular' => 'Блок',
        'rules_show' => 'Показывать на указанных страницах',
        'rules_hide' => 'Скрывать на указанных страницах',
        'columns' => [
            'title' => 'Название',
            'key' => 'Ключ',
            'region' => 'Регион',
            'status' => 'Активен',
            'order' => 'Порядок',
        ],
        'fields' => [
            'title' => 'Название',
            'key' => 'Ключ',
            'region' => 'Регион',
            'status' => 'Активен',
            'order' => 'Порядок сортировки',
            'content' => 'Содержимое',
            'urls' => 'URL правила (один на строку)',
            'rules' => 'Правило отображения',
            'options' => 'Опции (JSON)',
        ],
    ],

    // Block Regions
    'resources_block_regions' => [
        'label' => 'Регионы блоков',
        'singular' => 'Регион',
        'columns' => [
            'name' => 'Название',
            'key' => 'Ключ',
            'created_at' => 'Создан',
        ],
        'fields' => [
            'name' => 'Название',
            'key' => 'Ключ',
        ],
    ],

    // Chunks
    'resources_chunks' => [
        'label' => 'Чанки',
        'singular' => 'Чанк',
        'columns' => [
            'key' => 'Ключ',
            'value' => 'Значение',
            'created_at' => 'Создан',
        ],
        'fields' => [
            'key' => 'Ключ',
            'value' => 'Значение',
        ],
    ],

    // Settings
    'resources_settings' => [
        'label' => 'Настройки сайта',
        'singular' => 'Группа настроек',
        'columns' => [
            'group' => 'Группа',
            'name' => 'Название',
            'created_at' => 'Создана',
        ],
        'fields' => [
            'group' => 'Группа (ключ)',
            'name' => 'Название',
            'fields' => 'Поля (JSON Schema)',
        ],
    ],
];
