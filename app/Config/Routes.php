<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//Home
$routes->get('/', 'Home::index');
//Autenticacion
$routes->get('/login', 'Auth::mostrarFormLogin');
$routes->post('/login', 'Auth::procesarLogin');
$routes->get('/logout', 'Auth::logout');
//Panel Estudiante
$routes->get('/panel-estudiante', 'PanelEstudiante::index', ['filter' => 'auth:estudiante']);
//Solicitudes (Estudiante)
$routes->get('/solicitud/nueva', 'Solicitud::mostrarFormulario', ['filter' => 'auth:estudiante']);
$routes->post('/solicitud/crear', 'Solicitud::crear', ['filter' => 'auth:estudiante']);
//Gestión de Solicitudes (Responsable)
$routes->group('gestionSolicitudes', ['filter' => 'auth:responsable'], function($routes) {
    $routes->get('listarPendientes', 'GestionSolicitud::listarPendientes');
    $routes->get('verDetalle/(:num)', 'GestionSolicitud::verDetalle/$1');
    $routes->get('verDocumento/(:num)', 'GestionSolicitud::verDocumento/$1');
    $routes->post('aprobar/(:num)', 'GestionSolicitud::aprobar/$1');
    $routes->post('rechazar/(:num)', 'GestionSolicitud::rechazar/$1');
});