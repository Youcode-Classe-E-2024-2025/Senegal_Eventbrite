<?php
namespace Config;

use Core\Router;

$router = new Router();

$routes = [
    'GET' => [
        // '/' => [HomeController::class, 'index'],
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
