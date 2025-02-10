<?php
namespace Config;

use Core\Router;

$router = new Router();

$routes = [
    'GET' => [
        '/' => [\Controller_front\HomeController::class, 'index'],

    ],
    'POST' => [
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
