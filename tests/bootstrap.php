<?php

// Load vendor autoload
require __DIR__ . '/../../ave.local/vendor/autoload.php';

// Manually register package source namespace
spl_autoload_register(function ($class) {
    $prefix = 'Monstrex\\AveSite\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Manually register database seeders namespace
spl_autoload_register(function ($class) {
    $prefix = 'Monstrex\\AveSite\\Database\\Seeders\\';
    $baseDir = __DIR__ . '/../database/seeders/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Manually register test namespace
spl_autoload_register(function ($class) {
    $prefix = 'Monstrex\\AveSite\\Tests\\';
    $baseDir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});
