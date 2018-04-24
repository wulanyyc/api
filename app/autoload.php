<?php

function load_config()
{
    $filename = __DIR__ . '/configs/main.php';
    $config = new Phalcon\Config(require $filename);
    return $config;
}

function init_loader()
{
    $loader = new Phalcon\Loader;

    $loader->registerDirs([
        __DIR__ . '/tasks/',
        __DIR__ . '/vendors/',
        __DIR__ . '/models/',
        __DIR__ . '/libs/',
    ], true);

    $loader->registerNamespaces([
        'Biaoye\Controller' => __DIR__ . '/controllers/',
        'Biaoye\Model' => __DIR__ . '/models/',
    ]);

    $loader->register();
}

function init_dependency_injection($config, $isCLI = false)
{
    if ($isCLI) {
        $di = new Phalcon\DI\FactoryDefault\CLI;
    } else {
        $di = new Phalcon\DI\FactoryDefault;
    }

    $di->set('config', $config);

    $di->set('view', function () use ($di) {
        $view = new Phalcon\Mvc\View\Simple;
        $view->setViewsDir(__DIR__ . '/views/');
        $view->registerEngines([
            '.phtml' => 'Phalcon\Mvc\View\Engine\Php',
            '.html' => function ($view, $di) {
                $volt = new Phalcon\Mvc\View\Engine\Volt($view, $di);
                $volt->setOptions([
                    'compiledPath' => __DIR__ . '/cache/',
                    'compiledExtension' => '.compiled',
                ]);
                return $volt;
            }
        ]);
        return $view;
    });

    if ($config->logger) {
        $di->set('logger', function () use ($config) {
            return new Phalcon\Logger\Adapter\File($config->logger->path);
        });
    }

    if (isset($config->db)) {
        $di->set('db', function () use ($config) {
            return new Phalcon\Db\Adapter\Pdo\Mysql($config['db']->toArray());
        });
    }

    if ($config->redis) {
        $di->set('redis', function () use ($config) {
            if (!class_exists('\Predis\Client')) {
                include __DIR__ . '/vendors/predis/autoload.php';
            }
            return new \Predis\Client($config->redis->toArray());
        });
    }

    $di->set('util', function () {
        return new Util();
    }, true);
    
    // $di->set('curl', function () {
    //     return new Curl();
    // }, true);

    $di->set('product', function () {
        return new ProductHelper();
    }, true);

    $di->set('data', function () {
        return new DataHelper();
    }, true);

    $di->set('pay', function () {
        return new PayHelper();
    }, true);

    return $di;
}
