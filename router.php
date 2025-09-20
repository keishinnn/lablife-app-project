<?php

define('ROOT', __DIR__);

// load composer + .env
require ROOT . '/config/bootstrap.php';

function normalizeUri(string $uri, string $base): string
{
    if (strpos($uri, $base) === 0) {
        $uri = substr($uri, strlen($base));
    }

    $uri = rtrim($uri, '/');

    return $uri === '' ? '/' : $uri;
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = '/lablife-app-project';

$uri = normalizeUri($uri, $base);

$routes = [
    '/' => 'controllers/index.php',
    '/login' => 'controllers/auth/login.php',
    '/register' => 'controllers/auth/register.php'
];

function routeToController($uri, $routes)
{
    if (array_key_exists($uri, $routes)) {
        require $routes[$uri];
    } else {
        abort();
    }
}
function abort($code = 404)
{
    http_response_code($code);

    require 'views/404.php';
}

routeToController($uri, $routes);
