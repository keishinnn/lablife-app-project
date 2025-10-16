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

// Edit Preferences
$router->get('/u/profile-preferences-edit', 'User\\Profile\\ProfilePreferencesController@View');
$router->post('/u/submit-edit-preferences', 'User\\Profile\\ProfilePreferencesController@Update');

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

// Find Potential Match
$router->post('/u/discover/find-match', 'User\\Discover\\DiscoverController@handleFindPotentialMatch');
$router->post('/u/discover/start-search', 'User\\Discover\\DiscoverController@handleFindPotentialMatch');
$router->get('/u/discover/check-match', 'User\\Discover\\DiscoverController@checkMatch');
$router->post('/u/discover/stop-search', 'User\\Discover\\DiscoverController@handleStopSearch');

// Like and Dislike Other User
$router->post('/u/discover/like', 'User\\Discover\\DiscoverController@handleLikeOtherUser');
$router->post('/u/discover/dislike', 'User\\Discover\\DiscoverController@handledisLikeOtherUser');

// Match Session Services
$router->post('/u/discover/set-expired-session', 'User\\Discover\\DiscoverController@handleSetExpiredSession');
$router->post('/u/discover/set-rejected-session', 'User\\Discover\\DiscoverController@handleSetRejectedSession');
$router->post('/u/discover/set-matched-session', 'User\\Discover\\DiscoverController@handleSetMatchedSession');

// Get Supabase Access token
$router->get('/u/get-access-token', 'Services\\SupabaseService@getUser');

