<?php

use Illuminate\Support\Facades\Route;
use Monstrex\AveSite\Http\Controllers\PageController;

// Page routes
Route::get('/page/{slug}', [PageController::class, 'show'])
    ->name('ave-site.page.show')
    ->where('slug', '[a-zA-Z0-9\-_]+');

// TODO: Add Form submission route
// Route::post('/site/form-submit', [FormController::class, 'submit'])
//     ->name('ave-site.form.submit');
