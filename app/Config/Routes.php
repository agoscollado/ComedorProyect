<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Home::index');
$routes->get('/login', 'Auth::mostrarFormLogin');
$routes->post('/login', 'Auth::procesarLogin');
$routes->get('/logout', 'Auth::logout');
$routes->get('/panel-estudiante', 'PanelEstudiante::index', ['filter' => 'auth:estudiante']);
$routes->get('/panel-responsable', 'PanelResponsable::index', ['filter' => 'auth:responsable']);
$routes->get('/solicitud/nueva', 'Solicitud::mostrarFormulario', ['filter' => 'auth:estudiante']);
$routes->post('/solicitud/crear', 'Solicitud::crear', ['filter' => 'auth:estudiante']);