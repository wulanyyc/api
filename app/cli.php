<?php

require __DIR__ . '/autoload.php';

function init_console($di)
{
    $console = new Phalcon\CLI\Console($di);

    $arguments = array();
    $argv = $_SERVER['argv'];
    foreach ($argv as $k => $arg) {
        if ($k == 1) {
            $arguments['task'] = $arg;
        } elseif ($k == 2) {
            $arguments['action'] = $arg;
        } elseif ($k >= 3) {
            $arguments['params'][] = $arg;
        }
    }
    try {
        // handle incoming arguments
        $console->handle($arguments);
    } catch (\Phalcon\Exception $e) {
        echo $e->getMessage();
        exit(255);
    }
}

$config = load_config();
init_loader();
$di = init_dependency_injection($config, true);
init_console($di);
