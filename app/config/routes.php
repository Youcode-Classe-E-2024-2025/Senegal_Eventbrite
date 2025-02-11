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
        '/logout'=> [AuthController::class, 'logout'],

    ],
    'POST' => [
        '/signup' => [AuthController::class, 'signupPost'],
        '/login' => [AuthController::class, 'loginPost'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}
$router->dispatch();
