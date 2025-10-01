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

// Show Edit Profile Page
$router->get('/u/profile-edit', 'User\\ProfileEditController@View');
$router->post('/u/submit-edit-profile', 'User\\ProfileEditController@handleEditProfile');
$router->post('/u/cancel-edit', 'User\\ProfileEditController@handleCancelEdit');

$router->post('/u/submit-edit-avatar', 'User\\ProfileEditController@handleAvatarUpload');

// Get Personality Types
$router->get('/u/get-ptypes', 'User\\ProfileController@handleGetPTypes');
$router->post('/u/save-personality', 'User\\ProfileController@handleSetPersonalityType');

//Get Hobbies
$router->get('/u/get-hobbies', 'User\\ProfileController@handleGetHobbies');
$router->post('/u/save-hobbies', 'User\\ProfileController@handleSetHobbies');
