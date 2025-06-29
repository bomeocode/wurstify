<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/ratings', 'Ratings::new');
$routes->get('/ratings/new', 'Ratings::new');
$routes->post('ratings/create', 'Ratings::create');
$routes->get('/feed', 'Feed::index');
$routes->get('/merch', 'Merch::index');
$routes->get('/settings', 'Settings::index');
$routes->get('vendor/(:segment)', 'Vendor::show/$1');
$routes->get('api/vendors/(:segment)/ratings', 'Api\VendorRatings::index/$1');
$routes->get('api/vendor-search', 'Api\VendorSearch::index');
$routes->post('api/rating-image-upload', 'Api\RatingImageUpload::upload');
$routes->post('api/rating-image-delete', 'Api\RatingImageUpload::delete');

service('auth')->routes($routes);

// Profil bearbeiten
$routes->group('profile', ['filter' => 'session'], static function ($routes) {
  $routes->get('/', 'Profile::show', ['as' => 'profile_show']);
  $routes->post('update/details', 'Profile::updateDetails', ['as' => 'profile_update_details']);
  $routes->post('update/password', 'Profile::updatePassword', ['as' => 'profile_update_password']);
});
$routes->post('api/avatar-upload', 'Api\AvatarUpload::upload', ['filter' => 'session']);

$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
  $routes->get('/', 'Admin\DashboardController::index', ['as' => 'admin_dashboard']);
  $routes->get('users', 'Admin\UserController::index');
  $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');
  $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');
  $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');
});
