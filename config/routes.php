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

// Showing view in setup profile
$router->get('/u/setup-profile', 'User\\ProfileSetupController@SetupProfileView');
$router->get('/u/setup-profile-preferences', 'User\\ProfileSetupController@SetupProfilePreferencesView');

// Submitting preferences and basic user information
$router->post('/u/submit-setup', 'User\\ProfileSetupController@handleStepOneSetup');
$router->post('/u/submit-finish-setup', 'User\\ProfileSetupController@handleFinishSetup');

// Showing Profile Page
$router->get('/u/profile', 'User\\ProfileController@ProfileView');
$router->get('/u/profile-not-found', 'User\\ProfileController@ProfileNotFoundView');
$router->get('/u/profile-loading', 'User\\ProfileController@ProfileLoadingView');
