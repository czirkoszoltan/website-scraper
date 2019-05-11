#!/usr/bin/env php
<?php

set_include_path(__DIR__ . "/src/");
spl_autoload_register();
spl_autoload_extensions('.class.php');

try {
    $params = array_slice($argv, 1);
    if (count($params) < 2)
        throw new \RuntimeException("Please specify <config.json> and <import_files...>.");
    
    $config_file = $params[0];
    foreach (array_slice($params, 1) as $import_file) {
        $app = new App;
        $app->import($config_file, $import_file);
    }
} catch (\Throwable $e) {
    error_log(get_class($e) . ": " . $e->getMessage());
    exit(1);
}
