<?php


namespace App\Controllers;

use Models\Reservations;
use Models\Payments;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Core\View;
use Core\Router;
use Core\Database;
use Config; 
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Dompdf\Dompdf;
use Stripe\Refund;

class ReservationsController {
    private $view;
    private $router;
    private $reservationsModel;
    private $paymentsModel;

    public function __construct() {
        $this->view = new View();
        $db = Database::getInstance()->getConnection();

    }

    public function reservations() {
        try {

            $data = [
                'title' => 'Réservations',
                
            ];

            // Rendu de la vue reservations.twig
            $this->view->render('front/reservations', $data);
        } catch (\Exception $e) {
            die("Erreur lors du chargement des réservations : " . $e->getMessage());
        }
    }

   
    public function setRouter(Router $router) {
        $this->router = $router;
    }
}