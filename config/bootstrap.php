<?php

// file path = root/config/bootstrap.php

use Core\App;
use Core\Container;
use Core\Database;

require base_path('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

$container = new Container();

$container->bind('Core\Database', function () {
    $config = require base_path('config/config.php');
    return new Database($config['database']);
});

$container->bind('Services\SupabaseService', function () {
    $config = require base_path('config/supabase.php');
    return new \Services\SupabaseService($config);
});

$container->bind('Services\TurnstileService', function () {
    $config = require base_path('config/turnstile.php');
    return new \Services\TurnstileService($config['secret_key']);
});

$container->bind('redis', function () {
    $config = require base_path('config/redis.php');
    return new Predis\Client($config);
});

$container->bind('Services\StreamService', function () {
    $config = require base_path('config/stream.php');
    return new \Services\StreamService($config);
});

App::setContainer($container);
