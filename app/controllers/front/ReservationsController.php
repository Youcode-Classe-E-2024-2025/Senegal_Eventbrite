<?php

namespace App\Controllers;

use Models\Reservations;
use Models\Payments;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Core\View;
use Core\Router;
use Core\Database;
use Config; // Hypothétique classe de configuration

class ReservationsController {
    private $view;
    private $router;
    private $reservationsModel;
    private $paymentsModel;

    public function __construct() {
        $this->view = new View();
        $db = Database::getInstance()->getConnection();

        // Initialiser les modèles avec la connexion PDO
        $this->reservationsModel = new Reservations($db);
        $this->paymentsModel = new Payments($db); // Hypothétique modèle Payments
    }

    public function reservations() {
        try {
            // Récupérer toutes les réservations avec pagination (par exemple, 10 par page)
            $limit = 10;
            $offset = isset($_GET['page']) ? ($_GET['page'] - 1) * $limit : 0;
            $reservations = $this->reservationsModel->getAllReservations($limit, $offset);

            // Compter le nombre total de réservations pour la pagination
            $totalReservations = $this->reservationsModel->countReservations();

            // Préparer les données pour la vue
            $data = [
                'title' => 'Réservations',
                'reservations' => $reservations,
                'totalReservations' => $totalReservations,
                'currentPage' => isset($_GET['page']) ? $_GET['page'] : 1,
                'limit' => $limit,
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

    public function createCheckoutSession($reservation_id) {
        try {
            // Vérifier si l'ID de réservation est valide
            if (!is_numeric($reservation_id)) {
                die('ID de réservation invalide.');
            }

            // Récupérer les détails de la réservation
            $reservation = $this->reservationsModel->getReservationById($reservation_id);
            if (!$reservation) {
                die('Réservation introuvable.');
            }

            // Configurer Stripe avec la clé secrète
            Stripe::setApiKey(Config::get('STRIPE_SECRET_KEY'));

            // Créer une session de paiement Stripe
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Pass ' . $reservation['ticket_type'] . ' - Festival International de Jazz 2025',
                        ],
                        'unit_amount' => (int) ($reservation['total_price'] * 100), // Montant en centimes
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->router->generateUrl('payment_success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->router->generateUrl('payment_cancel'),
            ]);

            // Enregistrer la session de paiement dans la base de données
            $this->paymentsModel->addPayment(
                $reservation['id'],
                'stripe',
                $session->id,
                $reservation['total_price'],
                'pending'
            );

            // Rediriger l'utilisateur vers la page de paiement Stripe
            header("Location: {$session->url}");
            exit();
        } catch (\Exception $e) {
            die("Erreur lors de la création de la session de paiement : " . $e->getMessage());
        }
    }

    public function handlePaymentSuccess($session_id) {
        try {
            // Vérifier si l'ID de session est valide
            if (empty($session_id)) {
                die('ID de session de paiement manquant.');
            }

            // Récupérer les détails de la session de paiement
            $session = Session::retrieve($session_id);

            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut du paiement dans la base de données
                $payment = $this->paymentsModel->getPaymentByTransactionId($session->id);
                if ($payment) {
                    $this->paymentsModel->updatePaymentStatus($payment['id'], 'success');

                    // Mettre à jour le statut de la réservation associée
                    $this->reservationsModel->updateReservation(
                        $payment['reservation_id'],
                        null,
                        null,
                        null,
                        null,
                        null,
                        'paid'
                    );
                }

                // Afficher une confirmation de succès
                echo "Paiement réussi !";
            } else {
                echo "Une erreur s'est produite lors du traitement du paiement.";
            }
        } catch (\Exception $e) {
            die("Erreur lors du traitement du paiement : " . $e->getMessage());
        }
    }
}