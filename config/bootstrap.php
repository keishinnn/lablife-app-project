<?php

// file path = root/config/bootstrap.php

use Core\App;
use Core\Container;
use Core\Database;
use Predis\Client as RedisClient;

require base_path('vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

$container = new Container();

$container->bind('Core\Database', function () {
    $config = require base_path('config/config.php');
    return new Database($config['database']);
});

$container->bind('Core\Supabase', function () {
    $config = require base_path('config/supabase.php');
    return new \Core\Supabase($config);
});

$container->bind('Core\Turnstile', function () {
    $config = require base_path('config/turnstile.php');
    return new \Core\Turnstile($config['secret_key']);
});

$container->bind('redis', function () {
    $config = require base_path('config/redis.php');
    return new RedisClient($config);
});

App::setContainer($container);
