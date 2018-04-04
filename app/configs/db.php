<?php

return [
    'host' => '127.0.0.1',
    'port' => '3306',
    'username' => 'root',
    'password' => 'testmysql123',
    'dbname' => 'shops',
    'options' => [
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ],
];