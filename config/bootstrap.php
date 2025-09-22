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

$container->bind('Core\Supabase', function () {
    $config = require base_path('config/supabase.php');
    return new \Core\Supabase($config);
});



App::setContainer($container);
