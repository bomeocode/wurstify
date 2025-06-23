<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/ratings', 'Ratings::new');
$routes->post('ratings/create', 'Ratings::create');
$routes->get('/feed', 'Feed::index');
$routes->get('/merch', 'Merch::index');
$routes->get('/settings', 'Settings::index');
$routes->get('/me', 'Settings::profile');
$routes->get('vendor/(:segment)', 'Vendor::show/$1');
$routes->get('api/vendors/(:segment)/ratings', 'Api\VendorRatings::index/$1');

service('auth')->routes($routes);

// app/Config/Routes.php
$routes->group('admin', ['filter' => 'admin'], static function ($routes) {
  $routes->get('/', 'Admin\DashboardController::index', ['as' => 'admin_dashboard']);

  // Zeigt die Liste aller Benutzer an
  $routes->get('users', 'Admin\UserController::index');

  // Zeigt das Formular zum Bearbeiten eines Benutzers an
  $routes->get('users/edit/(:num)', 'Admin\UserController::edit/$1');

  // Verarbeitet die Formulardaten vom Bearbeiten
  $routes->post('users/update/(:num)', 'Admin\UserController::update/$1');

  // Verarbeitet den Klick auf den "Löschen"-Button
  $routes->get('users/delete/(:num)', 'Admin\UserController::delete/$1');
});
