<?php

if (file_exists(__DIR__ . $_SERVER["REQUEST_URI"])) {
    return false;
}

if (isset($_SERVER['PATH_INFO'])) {
    $_GET['_url'] = $_SERVER['PATH_INFO'];
} else {
    $_GET['_url'] = $_SERVER['REQUEST_URI'];
}

include 'index.php';
