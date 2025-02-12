<?php
namespace App\Config;

use Core\Router;
use App\Controllers\ReservationsController;
use Core\View;


$router = new Router();

$routes = [
    'GET' => [
        // Route existante pour afficher les réservations
        '/reservations' => [ReservationsController::class, 'reservations'],

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
