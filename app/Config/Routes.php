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
