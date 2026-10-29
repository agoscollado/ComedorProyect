<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
//Home
$routes->get('/', function () {
    return redirect()->to('/login');
});
//Autenticacion
$routes->get('/login', 'Auth::mostrarFormLogin');
$routes->post('/login', 'Auth::procesarLogin');
$routes->post('/logout', 'Auth::logout');
//Panel Estudiante
$routes->get('/panel-estudiante', 'PanelEstudiante::index', ['filter' => 'auth:estudiante']);
$routes->get('/tramites', 'TramitesPersonales::index', ['filter' => 'auth:estudiante']);
$routes->get('/chequera', 'HistorialChequera::index', ['filter' => 'auth:estudiante']);
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
//Reportes (Responsable)
$routes->get('/reportes/exportar', 'Reportes::exportarCsv', ['filter' => 'auth:responsable']);
$routes->get('/reportes', 'Reportes::index', ['filter' => 'auth:responsable']);
//Gestión de Beneficiarios (Responsable)
$routes->group('gestionBeneficiarios', ['filter' => 'auth:responsable'], function($routes) {
    $routes->get('listarUsuarios', 'GestionBeneficiarios::listarUsuarios');
    $routes->post('darDeBaja/(:num)', 'GestionBeneficiarios::darDeBaja/$1');
});
//Gestión de Chequeras (Responsable)
$routes->group('gestionChequera', ['filter' => 'auth:responsable'], function($routes) {
    $routes->post('registrarAcreditacion/(:num)', 'GestionChequera::registrarAcreditacion/$1');
});
//Reclamos (Estudiante)
$routes->get('/reclamos', 'Reclamo::index', ['filter' => 'auth:estudiante']);
$routes->group('reclamos', ['filter' => 'auth:estudiante'], function($routes) {
    $routes->get('nuevo', 'Reclamo::mostrarFormulario');
    $routes->post('crear', 'Reclamo::crear');
});
//Gestión de Reclamos (Responsable)
$routes->group('gestionReclamos', ['filter' => 'auth:responsable'], function($routes) {
    $routes->get('listar', 'GestionReclamo::index');
    $routes->get('verDetalle/(:num)', 'GestionReclamo::verDetalle/$1');
    $routes->post('responder/(:num)', 'GestionReclamo::responder/$1');
});