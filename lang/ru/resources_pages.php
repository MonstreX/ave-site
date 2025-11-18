<?php

return [
    'label' => 'Страницы',
    'singular' => 'Страница',
    'no_parent' => '(Без родителя)',
    'columns' => [
        'id' => 'ID',
        'title' => 'Заголовок',
        'slug' => 'URL',
        'status' => 'Опубликована',
    ],
    'tabs' => [
        'main' => 'Основное',
        'media' => 'Медиа',
        'seo' => 'SEO',
        'options' => 'Опции',
        'additional' => 'Дополнительно',
    ],
    'fields' => [
        'title' => 'Заголовок',
        'slug' => 'URL (slug)',
        'parent' => 'Родительская страница',
        'status' => 'Опубликована',
        'menu' => 'Показывать в меню',
        'content' => 'Содержимое',
        'image' => 'Основное изображение',
        'images' => 'Галерея изображений',
        'details' => 'Опции (JSON)',
        'seo_title' => 'SEO заголовок',
        'seo_keywords' => 'SEO ключевые слова',
        'seo_description' => 'SEO описание',
        'order' => 'Порядок',
        'published_at' => 'Дата публикации',
    ],
];
