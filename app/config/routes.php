<?php
namespace Config;

use Controller_back\categoryController;
use Controller_back\dashboardController;
use Core\Router;

$router = new Router();

$routes = [
    'GET' => [
        // '/' => [HomeController::class, 'index'],
        '/admin' => [dashboardController::class, 'dashboard'],
    ],
    'POST' => [
        '/createCtaegory' => [categoryController::class, 'createCategory'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
