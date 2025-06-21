<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Dashboard::index');
$routes->get('/dashboard', 'Dashboard::index');
$routes->get('/screens', 'Screens::index');
$routes->get('/layouts', 'Layouts::index');
$routes->get('/media', 'Media::index');
$routes->get('/settings', 'Settings::index');

service('auth')->routes($routes);

// filter 'session' stellt sicher, dass nur eingeloggte Benutzer zugreifen können
$routes->group('media', ['filter' => 'session'], static function ($routes) {
  $routes->get('/', 'Media::index');
  $routes->post('upload', 'Media::upload');
  $routes->get('serve/(:alphanum)', 'Media::serve/$1');
  $routes->get('delete/(:alphanum)', 'Media::delete/$1');
});

$routes->group('layouts', ['filter' => 'session'], static function ($routes) {
  $routes->get('/', 'Layouts::index');
  $routes->get('new', 'Layouts::new');
  $routes->post('create', 'Layouts::create');

  // Platzhalter für die zukünftigen Funktionen
  $routes->get('edit/(:alphanum)', 'Layouts::edit/$1');
  $routes->get('display/(:alphanum)', 'Layouts::display/$1');
  $routes->post('delete/(:alphanum)', 'Layouts::delete/$1');
  $routes->post('update_slot/(:alphanum)/(:any)', 'Layouts::updateSlot/$1/$2');
});

$routes->group('screens', ['filter' => 'session'], static function ($routes) {
  $routes->get('/', 'Screens::index');

  // API für die Gruppenverwaltung
  $routes->get('groups', 'Screens::groupsApi');
  $routes->post('groups/create', 'Screens::createGroup');
  $routes->post('groups/update', 'Screens::updateGroup');
  $routes->post('groups/delete', 'Screens::deleteGroup');

  // ... später folgen hier noch Routen für einzelne Bildschirme ...
});
