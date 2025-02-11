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

        // Nouvelle route pour afficher une confirmation de succès
        '/payment-success' => [ReservationsController::class, 'handlePaymentSuccess'],

        // Nouvelle route pour gérer l'annulation du paiement
        '/payment-cancel' => function () {
            echo "Paiement annulé.";
        },
    ],
    'POST' => [
        // Nouvelle route pour créer une session de paiement Stripe
        '/create-checkout-session/{reservation_id}' => [ReservationsController::class, 'createCheckoutSession'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}

$router->dispatch();
