<?php
spl_autoload_register(function ($class_name) {
    $parts = explode('\\', $class_name);
    $parts = array_map(function ($part) {
        return strtolower($part);
    }, $parts);

    $supported = array('gw', 'tds');
    if (in_array($parts[0], $supported)) {
        // We're only listening for our known namespaces
        $filename = implode('/', array_merge(array(__DIR__, 'lib'), $parts)) . '.php';
        include_once $filename;
    }
});
