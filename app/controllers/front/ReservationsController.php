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
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Label\LabelInterface;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;




class ReservationsController {
    private $view;
    private $router;
    private $reservationsModel;
    private $paymentsModel;

    public function __construct() {
        // Initialiser Stripe avec votre clé secrète
        Stripe::setApiKey('sk_test_51QrHbDRLKEtyBUgCsX4OcjBHKqUQdNi50eA3WLVpE2sqq5ISgiXzHBthI6fb1IhIOaIiO8TAUqnQu5G1IrBhVVg800d3z9CIim');
        $db = Database::getInstance()->getConnection();
        $this->reservationsModel = new Reservations($db);
        $this->view = new View($db);

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
    // public function createReservation() {
    //     try {
    //         // Récupération des données du formulaire
    //         $data = json_decode(file_get_contents('php://input'), true);
            
    //         // Validation des données
    //         if (!isset($data['ticket_type']) || !isset($data['quantity'])) {
    //             throw new \Exception('Données manquantes');
    //         }
    
    //         // Calcul du prix total
    //         $totalPrice = $this->calculateTotalPrice(
    //             $data['ticket_type'],
    //             $data['quantity'],
    //             [
    //                 'vip_backstage' => $data['vip_backstage'] ?? false,
    //                 'vip_dinner' => $data['vip_dinner'] ?? false,
    //                 'vip_suite' => $data['vip_suite'] ?? false
    //             ]
    //         );
    
    //         // Création de la session Stripe
    //         Stripe::setApiKey('votre_cle_stripe_secrete');
    //         $session = Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'eur',
    //                     'unit_amount' => $totalPrice * 100, // Stripe utilise les centimes
    //                     'product_data' => [
    //                         'name' => 'Billet Festival - ' . ucfirst($data['ticket_type']),
    //                     ],
    //                 ],
    //                 'quantity' => 1,
    //             ]],
    //             'mode' => 'payment',
    //             'success_url' => 'https://votre-site.com/payment-success?session_id={CHECKOUT_SESSION_ID}',
    //             'cancel_url' => 'https://votre-site.com/payment-cancel',
    //         ]);
    
    //         // Création de la réservation en statut "pending"
    //         $reservationId = $this->reservationsModel->addReservation(
    //             $data['user_id'],
    //             1, // event_id (à adapter selon votre logique)
    //             $data['ticket_type'],
    //             $data['quantity'],
    //             $totalPrice,
    //             $data['full_name'],
    //             $data['email'],
    //             [
    //                 'backstage' => $data['vip_backstage'] ?? false,
    //                 'dinner' => $data['vip_dinner'] ?? false,
    //                 'suite' => $data['vip_suite'] ?? false
    //             ]
    //         );
    
    //         // Retourner l'ID de session Stripe
    //         echo json_encode([
    //             'success' => true,
    //             'sessionId' => $session->id,
    //             'reservationId' => $reservationId
    //         ]);
    
    //     } catch (\Exception $e) {
    //         http_response_code(500);
    //         echo json_encode(['error' => $e->getMessage()]);
    //     }
    // }
    // public function createReservation() {
    //     try {
    //         // Log les données reçues
    //         $rawData = file_get_contents('php://input');
    //         error_log('Données reçues: ' . $rawData);
            
    //         // Décoder les données JSON
    //         $data = json_decode($rawData, true);
    //         error_log('Données décodées: ' . print_r($data, true));
            
    //         // Vérifier si les données obligatoires sont présentes
    //         if (!isset($data['ticket_type']) || !isset($data['quantity'])) {
    //             throw new \Exception('Données manquantes: ticket_type ou quantity');
    //         }
            
    //         // Log des variables importantes
    //         error_log('Type de billet: ' . $data['ticket_type']);
    //         error_log('Quantité: ' . $data['quantity']);
            
    //         // Réponse simple pour tester
    //         echo json_encode([
    //             'status' => 'success',
    //             'message' => 'Données reçues avec succès',
    //             'data' => $data
    //         ]);
            
    //     } catch (\Exception $e) {
    //         error_log('Erreur dans createReservation: ' . $e->getMessage());
    //         http_response_code(500);
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }
    // public function createReservation() {
    //     try {
    //         // Récupérer et décoder les données
    //         $data = json_decode(file_get_contents('php://input'), true);

    //         // Calculer le prix total
    //         $totalPrice = $this->calculateTotalPrice($data);

    //         // Créer la session Stripe
    //         $session = Session::create([
    //             'payment_method_types' => ['card'],
    //             'line_items' => [[
    //                 'price_data' => [
    //                     'currency' => 'eur',
    //                     'unit_amount' => $totalPrice * 100, // Stripe utilise les centimes
    //                     'product_data' => [
    //                         'name' => 'Festival Pass - ' . strtoupper($data['ticket_type']),
    //                         'description' => 'Quantité: ' . $data['quantity']
    //                     ],
    //                 ],
    //                 'quantity' => 1,
    //             ]],
    //             'mode' => 'payment',
    //             'success_url' => 'http://votre-domaine.com/payment-success?session_id={CHECKOUT_SESSION_ID}',
    //             'cancel_url' => 'http://votre-domaine.com/payment-cancel',
    //             'metadata' => [
    //                 'ticket_type' => $data['ticket_type'],
    //                 'quantity' => $data['quantity'],
    //                 'full_name' => $data['full_name'],
    //                 'email' => $data['email']
    //             ]
    //         ]);

    //         // Créer la réservation en status "pending"
    //         $reservationId = $this->reservationsModel->addReservation(
    //             null, // user_id (si besoin)
    //             1,   // event_id (à adapter selon votre événement)
    //             $data['ticket_type'],
    //             $data['quantity'],
    //             $totalPrice,
    //             $data['full_name'],
    //             $data['email'],
    //             [
    //                 'vip_dinner' => isset($data['vip_dinner']) ? true : false
    //             ]
    //         );

    //         // Retourner l'ID de session Stripe et l'ID de réservation
    //         echo json_encode([
    //             'status' => 'success',
    //             'sessionId' => $session->id,
    //             'reservationId' => $reservationId,
    //             'url' => $session->url // URL de redirection Stripe
    //         ]);

    //     } catch (\Exception $e) {
    //         error_log('Erreur dans createReservation: ' . $e->getMessage());
    //         http_response_code(500);
    //         echo json_encode([
    //             'status' => 'error',
    //             'message' => $e->getMessage()
    //         ]);
    //     }
    // }

    public function createReservation() {
        try {
            // Récupérer et décoder les données
            $data = json_decode(file_get_contents('php://input'), true);
    
            // Vérifier que les données nécessaires sont présentes
            if (!isset($data['ticket_type']) || !isset($data['quantity'])) {
            // if (!isset($data['ticket_type']) || !isset($data['quantity']) || !isset($data['full_name']) || !isset($data['email'])) {

                throw new \Exception('Données manquantes');
            }
    
            // Calculer le prix total
            $totalPrice = $this->calculateTotalPrice($data);
    
            // Créer la session Stripe
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => $totalPrice * 100, // Stripe utilise les centimes
                        'product_data' => [
                            'name' => 'Festival Pass - ' . strtoupper($data['ticket_type']),
                            'description' => 'Quantité: ' . $data['quantity']
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => 'http://votre-domaine.com/payment-success?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => 'http://votre-domaine.com/payment-cancel',
                'metadata' => [
                    'ticket_type' => $data['ticket_type'],
                    'quantity' => $data['quantity'],
                    'full_name' => $data['full_name'],
                    'email' => $data['email']
                ]
            ]);
    
            // // Créer les options VIP
            // $vipOptions = [
            //     'backstage' => isset($data['vip_backstage']) ? true : false,
            //     'dinner' => isset($data['vip_dinner']) ? true : false,
            //     'suite' => isset($data['vip_suite']) ? true : false
            // ];
    
            // Créer la réservation en statut "pending"
            $reservationId = $this->reservationsModel->addReservation(
                1, // user_id (ajustez selon votre logique)
                1,   // event_id (à adapter)
                $data['ticket_type'],
                $data['quantity'],
                $totalPrice,
                // $data['full_name'],
                // $data['email'],
                //$vipOptions // Passez ici les options VIP
            );
    
            // Retourner l'ID de session Stripe et l'ID de réservation
            echo json_encode([
                'status' => 'success',
                'sessionId' => $session->id,
                'reservationId' => $reservationId,
                'url' => $session->url // URL de redirection Stripe
            ]);
    
        } catch (\Exception $e) {
            error_log('Erreur dans createReservation: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    
    private function calculateTotalPrice($data) {
        $basePrice = 0;
        switch ($data['ticket_type']) {
            case 'vip':
                $basePrice = 599;
                break;
            case 'premium':
                $basePrice = 299;
                break;
            case 'standard':
                $basePrice = 149;
                break;
        }

        // Ajouter le prix des options VIP
        if (isset($data['vip_dinner'])) {
            $basePrice += 299; // Prix du dîner VIP
        }

        return $basePrice * intval($data['quantity']);
    }

    public function handlePaymentSuccess() {
        try {
            $session_id = $_GET['session_id'];
            $reservation_id = $_GET['reservation_id'];
    
            // Vérifier le paiement avec Stripe
            Stripe::setApiKey('sk_test_51QrHbDRLKEtyBUgCsX4OcjBHKqUQdNi50eA3WLVpE2sqq5ISgiXzHBthI6fb1IhIOaIiO8TAUqnQu5G1IrBhVVg800d3z9CIim');
            $session = Session::retrieve($session_id);
    
            if ($session->payment_status === 'paid') {
                // Générer le QR Code
                $qrCode = Builder::create()
                    ->data("reservation_id=$reservation_id")
                    ->encoding(new Encoding('UTF-8'))
                    ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
                    ->size(300)
                    ->margin(10)
                    ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
                    ->build();
    
                // Convertir le QR code en base64
                $writer = new PngWriter();
                $result = $writer->write($qrCode);
                $qrCodeBase64 = base64_encode($result->getString());
    
                // Mettre à jour la réservation
                $this->reservationsModel->updateReservationStatus($reservation_id, 'paid', $qrCodeBase64);
    
                // Envoyer l'email de confirmation
                $this->sendConfirmationEmail($reservation_id);
    
                // Redirection vers la page de succès
                header('Location: /reservation-success');
            }
        } catch (\Exception $e) {
            // Gérer l'erreur
            error_log($e->getMessage());
            header('Location: /error-page');
        }
    }
}