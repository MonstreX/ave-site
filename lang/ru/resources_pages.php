<?php

return [
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
];
