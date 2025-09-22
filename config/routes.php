<?php

// file path = root/config/routes.php

use Controllers\Auth;
use Controllers\User;

// Home
$router->get('/', 'controllers/HomeController.php');

// Auth
$router->get('/login', 'Auth\\LoginController@View');
$router->post('/login', 'Auth\\LoginController@handleLogin');

$router->get('/register', 'Auth\\RegisterController@View');
$router->post('/register', 'Auth\\RegisterController@handleRegister');

$router->post('/logout', 'Auth\\LogoutController@logout');

// Authenticated User
$router->get('/u', 'User\\UserController@View');
