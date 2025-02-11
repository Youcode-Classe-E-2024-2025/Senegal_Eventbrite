<?php
namespace Config;

use Controller_front\UserController;
use Core\Router;

$router = new Router();

$routes = [
    'GET' => [
        '/userDashboard' => [UserController::class, 'index'],
        '/createEvent' => [UserController::class, 'event'],
    ],
    'POST' => [
        // '/login' => [AuthController::class, 'login'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
