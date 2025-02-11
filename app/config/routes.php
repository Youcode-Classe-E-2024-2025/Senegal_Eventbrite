<?php
namespace Config;

use Controller_front\HomeController;
use Core\Router;
use Controller_front\AuthController;

$router = new Router();

$routes = [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/signup' => [AuthController::class, 'signup'],
        '/login' => [AuthController::class, 'login'],

    ],
    'POST' => [
        '/signup' => [AuthController::class, 'signupPost'],
        
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
