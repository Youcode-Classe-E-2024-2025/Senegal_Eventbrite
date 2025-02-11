<?php
namespace App\Config;

use Core\Router;
use App\Controllers\ReservationsController;
use Core\View;


$router = new Router();

$routes = [
    'GET' => [
        // '/' => [HomeController::class, 'index'],
        '/reservations' => [ReservationsController::class, 'reservations'],
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
