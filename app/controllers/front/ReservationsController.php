<?php

namespace App\Controllers;

use Models\Reservations;
use App\Core\View;
use Core\Router;

class ReservationsController {
    private $view;
    private $router;

    public function __construct() {
        $this->view = new View();
    }

    public function setRouter(Router $router) {
        $this->router = $router;
    }

    public function reservations() {
        // Simulez les données pour tester (remplacez par votre modèle)
        $data = [
            'title' => 'Reservations',
            'reservations' => [
                // ['id' => 1, 'user_id' => 1, 'event_id' => 1, 'ticket_type' => 'VIP', 'quantity' => 2, 'total_price' => 100],
                // ['id' => 2, 'user_id' => 2, 'event_id' => 2, 'ticket_type' => 'Paid', 'quantity' => 1, 'total_price' => 50],
            ],
        ];

        // Rendu de la vue reservations.twig
        $this->view->render('front/reservations', $data);
    }
}
