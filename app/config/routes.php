<?php
namespace Config;

use Controller_front\UserController;
use Controller_back\categoryController;
use Controller_back\dashboardController;
use Core\Router;

$router = new Router();

$routes = [
    'GET' => [
        '/userDash' => [UserController::class, 'index'],
        '/createEvent' => [UserController::class, 'event'],
        '/profileInfo' => [UserController::class, 'info'],
        // '/' => [HomeController::class, 'index'],
        '/admin' => [dashboardController::class, 'dashboard'],
    ],
    'POST' => [
        '/createCtaegory' => [categoryController::class, 'createCategory'],
        '/' => [\Controller_front\HomeController::class, 'index'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
