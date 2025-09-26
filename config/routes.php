<?php

// file path = root/config/routes.php

// Home
$router->get('/', 'HomeController@View');

// Auth
$router->get('/login', 'Auth\\LoginController@View');
$router->post('/login', 'Auth\\LoginController@handleLogin');

$router->get('/register', 'Auth\\RegisterController@View');
$router->post('/register', 'Auth\\RegisterController@handleRegister');

$router->post('/logout', 'Auth\\LogoutController@logout');

// Authenticated User
$router->get('/u', 'User\\ProfileController@View');


$router->get('/u/setup-profile', 'User\\ProfileController@SetUpProfileView');
$router->post('/u/submit-setup', 'User\\ProfileController@handleSetupProfile');

$router->get('/u/profile', 'User\\ProfileController@ProfileView');
