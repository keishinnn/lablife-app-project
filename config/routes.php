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
$router->get('/u', 'User\\Profile\\ProfileController@View');

// Showing view in setup profile
$router->get('/u/setup-profile', 'User\\Profile\\ProfileSetupController@SetupProfileView');
$router->get('/u/setup-profile-preferences', 'User\\Profile\\ProfileSetupController@SetupProfilePreferencesView');

// Submitting preferences and basic user information
$router->post('/u/submit-setup', 'User\\Profile\\ProfileSetupController@handleStepOneSetup');
$router->post('/u/submit-finish-setup', 'User\\Profile\\ProfileSetupController@handleFinishSetup');

// Showing Profile Page
$router->get('/u/profile', 'User\\Profile\\ProfileController@ProfileView');
$router->get('/u/profile-not-found', 'User\\Profile\\ProfileController@ProfileNotFoundView');
$router->get('/u/profile-loading', 'User\\Profile\\ProfileController@ProfileLoadingView');

// Show Edit Profile Page
$router->get('/u/profile-edit', 'User\\Profile\\ProfileEditController@View');
$router->post('/u/submit-edit-profile', 'User\\Profile\\ProfileEditController@handleEditProfile');
$router->post('/u/cancel-edit', 'User\\Profile\\ProfileEditController@handleCancelEdit');

$router->post('/u/submit-edit-avatar', 'User\\Profile\\ProfileEditController@handleAvatarUpload');

// Get Personality Types
$router->get('/u/get-ptypes', 'User\\Profile\\ProfileController@handleGetPTypes');
$router->post('/u/save-personality', 'User\\Profile\\ProfileController@handleSetPersonalityType');

//Get Hobbies
$router->get('/u/get-hobbies', 'User\\Profile\\ProfileController@handleGetHobbies');
$router->post('/u/save-hobbies', 'User\\Profile\\ProfileController@handleSetHobbies');

$router->post('/u/save-interests', 'User\\Profile\\ProfileController@handleSetInterests');

// Showing Discover Page
$router->get('/u/discover', 'User\\Discover\\DiscoverController@View');
$router->get('/u/discover/matched-user', 'User\\Discover\\DiscoverController@MatchedUserView');
