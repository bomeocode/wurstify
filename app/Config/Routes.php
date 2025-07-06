<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/ratings', 'Ratings::new');
$routes->get('/ratings/new', 'Ratings::new');
$routes->post('ratings/create', 'Ratings::create', ['as' => 'rating_create']);
$routes->get('/feed', 'Feed::index');
$routes->get('/merch', 'Merch::index');
$routes->get('/settings', 'Settings::index');
$routes->get('vendor/(:segment)', 'Vendor::show/$1');
//$routes->get('api/vendors/(:segment)/ratings', 'Api\VendorRatings::index/$1');
$routes->get('feed', 'Feed::index', ['filter' => 'session']);
$routes->get('feedback', 'Feedback::index');
$routes->post('feedback/create', 'Feedback::create', ['as' => 'feedback_create']);
$routes->get('help/guide', 'Help::index');

// Leitet die Standard-Login-URL auf unsere neue Logik um.
$routes->get('login', 'AuthController::loginView');
// Verarbeitet die POST-Daten unseres neuen Formulars.
$routes->post('login', 'AuthController::handleLogin');
service('auth')->routes($routes);

// Profil bearbeiten
$routes->group('profile', ['filter' => 'session'], static function ($routes) {
  $routes->get('/', 'Profile::show', ['as' => 'profile_show']);
  $routes->post('update/details', 'Profile::updateDetails', ['as' => 'profile_update_details']);
  $routes->post('update/password', 'Profile::updatePassword', ['as' => 'profile_update_password']);
});

$routes->group('api', ['filter' => 'session'], static function ($routes) {
  $routes->post('avatar-upload', 'Api\AvatarUpload::upload');
  $routes->post('rating-image-upload', 'Api\RatingImageUpload::upload');
  $routes->post('rating-image-delete', 'Api\RatingImageUpload::delete');
  $routes->get('vendor-search', 'Api\VendorSearch::index');
  $routes->get('feed/ratings', 'Api\FeedController::index');
  $routes->get('ratings/(:num)', 'Api\RatingController::show/$1');
  $routes->get('vendors/(:segment)/ratings', 'Api\VendorController::ratings/$1');
  $routes->get('feed/new-count', 'Api\FeedController::newCount');
});

$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
  $routes->get('/', 'Admin\DashboardController::index', ['as' => 'admin_dashboard']);

  $routes->get('users', 'Admin\UserController::index');
  $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');
  $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
  $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');

  $routes->get('vendors', 'Admin\VendorController::index');
  $routes->get('vendors/edit/(:num)', 'Admin\VendorController::edit/$1');
  $routes->post('vendors/update/(:num)', 'Admin\VendorController::update/$1');
  $routes->get('vendors/delete/(:num)', 'Admin\VendorController::delete/$1');

  $routes->get('ratings', 'Admin\RatingController::index');
  $routes->get('ratings/edit/(:num)', 'Admin\RatingController::edit/$1');
  $routes->post('ratings/update/(:num)', 'Admin\RatingController::update/$1');
  $routes->get('ratings/delete/(:num)', 'Admin\RatingController::delete/$1');

  $routes->get('tools/cleanup_images', 'Admin\ToolsController::cleanupImages', ['as' => 'admin_cleanup_images']);
});
