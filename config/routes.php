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

// Mark as offline if user leaves page without signing out
$router->post('/u/set-offline', 'User\\Profile\\ProfileController@handleSetOffline');
$router->post('/u/set-online', 'User\\Profile\\ProfileController@handleSetOnline');

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

// Active Match Services
$router->post('/u/discover/stop-search', 'User\\Discover\\SearchController@handleStopSearch');
$router->post('/u/discover/set-search-in-match', 'User\\Discover\\SearchController@handleSetSearchInMatch');
$router->post('/u/discover/set-search-expired', 'User\\Discover\\SearchController@handleSetSearchExpired');
$router->post('/u/discover/set-search-active', 'User\\Discover\\SearchController@handleSetSearchActive');

// Like and Dislike Other User
$router->post('/u/discover/like', 'User\\Discover\\ReactionController@handleLikeOtherUser');
$router->post('/u/discover/dislike', 'User\\Discover\\ReactionController@handledisLikeOtherUser');

// Match Session Services
$router->post('/u/discover/set-expired-session', 'User\\Discover\\MatchSessionController@handleSetExpiredSession');
$router->post('/u/discover/set-rejected-session', 'User\\Discover\\MatchSessionController@handleSetRejectedSession');
$router->post('/u/discover/set-matched-session', 'User\\Discover\\MatchSessionController@handleSetMatchedSession');

// Get Supabase Access token
$router->get('/u/get-access-token', 'Services\\SupabaseService@getUser');

// Showing Matches Page
$router->get('/u/matches', 'User\\Matches\\MatchesController@View');

// Privacy and Terms 
$router->get('/privacy-policy', 'PagesController@privacyPolicy');
$router->get('/terms', 'PagesController@terms');

// Bug Report
$router->get('/bug-report', 'BugReportController@showForm');
$router->post('/submit-bug', 'BugReportController@submit');

// User Report
$router->get('/u/report-user', 'User\\UserReportController@showForm');
$router->post('/u/submit-user-report', 'User\\UserReportController@submit');


//admins
$router->get('/admin/login', 'Admin\AdminLoginController@showLogin');
$router->post('/admin/login', 'Admin\AdminLoginController@login');
$router->get('/admin/logout', 'Admin\AdminLoginController@logout');

//admin dashboard
$router->get('/admin/dashboard', 'Admin\AdminDashboardController@index');
$router->get('/admin/api/dashboard/matches', 'Admin\AdminDashboardDataController@loadMatchesChart');
$router->get('/admin/api/dashboard/active-users', 'Admin\AdminDashboardDataController@loadActiveUsers');

//admin dashboard: bug reports
$router->get('/admin/bug-reports', 'Admin\\AdminBugReportController@index');
$router->get('/admin/api/bug-reports', 'Admin\\AdminBugReportController@listJson');
$router->get('/admin/api/bug-report', 'Admin\\AdminBugReportController@detailJson');

//admin dashboard: user reports
$router->get('/admin/user-reports', 'Admin\AdminUserReportController@index');
$router->get('/admin/api/user-reports', 'Admin\AdminUserReportController@listJson');
$router->get('/admin/api/user-report', 'Admin\AdminUserReportController@detailJson');

//Notification
$router->get('/admin/api/check-new-reports', 'Admin\AdminNotificationController@checkNewReports');

// Show Messages Page
$router->get('/u/messages', 'User\\Messages\\MessagesController@View');

// Create Channel Message
$router->post('/u/matches/create-channel', 'User\\Matches\\MatchesController@handleCreateOrGetChannel');

// Video Call
$router->post('/u/video/initiate-video-call', 'User\\Messages\\VideoCallController@handleInitiateCall');
$router->post('/u/video/get-video-token', 'User\\Messages\\VideoCallController@handleGetStreamVideoToken');
$router->post('/u/video/receive-call', 'User\\Messages\\VideoCallController@handleReceiveVideoCall');
$router->post('/u/video/end-video-call', 'User\\Messages\\VideoCallController@handleEndVideoCall');

// Block Other User 
$router->post('/u/block-other-user', 'User\\Messages\\BlockController@blockOtherUser');
$router->post('/u/unblock-other-user', 'User\\Messages\\BlockController@unblockOtherUser');
$router->get('/u/profile/blocked-users', 'User\\Messages\\BlockController@View');

// Verify
$router->get('/u/verify-next', 'User\\Discover\\VerifyController@View');
$router->get('/u/verify', 'User\\Discover\\VerifyController@VerifyView');
$router->post('/u/account/set-verified', 'User\\Discover\\VerifyController@handleSetVerified');

$router->post('/u/account/increment-fail', 'User\\Discover\\VerifyController@handleIncrementFail');
$router->get('/u/account/fail-status', 'User\\Discover\\VerifyController@getFailStatus');
