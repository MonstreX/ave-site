<?php

// Load vendor autoload
require __DIR__ . '/../../ave.local/vendor/autoload.php';

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
