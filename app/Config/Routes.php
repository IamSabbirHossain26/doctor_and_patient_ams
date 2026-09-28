<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ---------------------------------------------------------------------
// Public
// ---------------------------------------------------------------------
$routes->get('/', 'Home::index');
$routes->get('about', 'Home::about');

// ---------------------------------------------------------------------
// Authentication
// ---------------------------------------------------------------------
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::attemptRegister');
$routes->get('logout', 'Auth::logout');
$routes->post('logout', 'Auth::logout');

// ---------------------------------------------------------------------
// Dashboard (any authenticated user)
// ---------------------------------------------------------------------
$routes->get('dashboard', 'Dashboard::index');

// ---------------------------------------------------------------------
// Doctors (browse by all roles, manage by manager)
// ---------------------------------------------------------------------
$routes->get('doctors', 'Doctors::index');
$routes->get('doctors/view/(:num)', 'Doctors::view/$1');

$routes->group('doctors', ['filter' => 'role:manager'], static function ($routes) {
    $routes->get('create', 'Doctors::create');
    $routes->post('store', 'Doctors::store');
    $routes->get('edit/(:num)', 'Doctors::edit/$1');
    $routes->post('update/(:num)', 'Doctors::update/$1');
    $routes->post('delete/(:num)', 'Doctors::delete/$1');
});

// ---------------------------------------------------------------------
// Patients
// ---------------------------------------------------------------------
$routes->get('patients', 'Patients::index', ['filter' => 'role:manager,nurse,doctor']);
$routes->get('patients/view/(:num)', 'Patients::view/$1', ['filter' => 'role:manager,nurse,doctor']);

$routes->group('patients', ['filter' => 'role:manager,nurse'], static function ($routes) {
    $routes->get('create', 'Patients::create');
    $routes->post('store', 'Patients::store');
    $routes->get('edit/(:num)', 'Patients::edit/$1');
    $routes->post('update/(:num)', 'Patients::update/$1');
    $routes->post('delete/(:num)', 'Patients::delete/$1');
});

// Patient self profile
$routes->get('my-profile', 'Patients::myProfile', ['filter' => 'role:patient']);
$routes->post('my-profile', 'Patients::updateMyProfile', ['filter' => 'role:patient']);

// ---------------------------------------------------------------------
// Appointments
// ---------------------------------------------------------------------
$routes->get('appointments', 'Appointments::index');
$routes->get('appointments/view/(:num)', 'Appointments::view/$1');

$routes->group('appointments', ['filter' => 'auth'], static function ($routes) {
    $routes->get('book', 'Appointments::create');
    $routes->post('store', 'Appointments::store');
});

$routes->group('appointments', ['filter' => 'role:manager,nurse,doctor'], static function ($routes) {
    $routes->post('status/(:num)', 'Appointments::updateStatus/$1');
    $routes->post('bill/(:num)', 'Appointments::updateBill/$1');
    $routes->post('test/(:num)', 'Appointments::updateTest/$1');
    $routes->post('delete/(:num)', 'Appointments::delete/$1');
});

// ---------------------------------------------------------------------
// Reports
// ---------------------------------------------------------------------
$routes->get('reports', 'Reports::index');
$routes->get('reports/view/(:num)', 'Reports::view/$1');
$routes->get('reports/download/(:num)', 'Reports::download/$1');

$routes->group('reports', ['filter' => 'role:manager,nurse'], static function ($routes) {
    $routes->get('upload', 'Reports::create');
    $routes->post('store', 'Reports::store');
    $routes->get('edit/(:num)', 'Reports::edit/$1');
    $routes->post('update/(:num)', 'Reports::update/$1');
    $routes->post('delete/(:num)', 'Reports::delete/$1');
});

// ---------------------------------------------------------------------
// Staff management (manager only)
// ---------------------------------------------------------------------
$routes->group('staff', ['filter' => 'role:manager'], static function ($routes) {
    $routes->get('/', 'Staff::index');
    $routes->get('create', 'Staff::create');
    $routes->post('store', 'Staff::store');
    $routes->get('edit/(:num)', 'Staff::edit/$1');
    $routes->post('update/(:num)', 'Staff::update/$1');
    $routes->post('delete/(:num)', 'Staff::delete/$1');
    $routes->post('reset-password/(:num)', 'Staff::resetPassword/$1');
});
