<?php

$router->get('/', 'controllers/index.php');
$router->get('/login', 'controllers/auth/login.php');
$router->get('/register', 'controllers/auth/register.php');
