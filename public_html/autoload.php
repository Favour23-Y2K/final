<?php
require_once('../assets/apache/constants.php');
spl_autoload_register(function ($class) {
    // Base directory for the namespace prefix
    $base_dir = MEDIA_LOCALE . 'vendor/intervention/image/src/';

    // Replace namespace separator with directory separator
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});