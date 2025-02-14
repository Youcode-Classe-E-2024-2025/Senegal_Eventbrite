<?php

namespace App\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Models\Reservations;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use App\Core\View;
use Core\Router;
use Core\Database;
use PHPMailer\PHPMailer\PHPMailer;
use Dompdf\Dompdf;
use Dompdf\Options;
use Core\Payments ;
use Ramsey\Uuid\Guid\Guid;
use PHPMailer\PHPMailer\Exception;

class ReservationsController {
    private $view;
    private $router;
    private $reservationsModel;
    private $paymentsModel;
    private $stripeKey;

    public function __construct() {
        // Charger la clé API depuis les variables d'environnement
        // $this->stripeKey = getenv('STRIPE_SECRET_KEY');
        $this->stripeKey = $_ENV['STRIPE_SECRET_KEY'] ?? $_SERVER['STRIPE_SECRET_KEY'] ?? null;
        if (!$this->stripeKey) {
            throw new \Exception('La clé API Stripe n\'est pas configurée');
        }
        
        Stripe::setApiKey($this->stripeKey);
        
        $db = Database::getInstance()->getConnection();
        $this->reservationsModel = new Reservations($db);
        $this->paymentsModel = new Payments($db);
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

    public function createReservation() {
        try {
            $data = $this->validateAndSanitizeInput();
            $totalPrice = $this->calculateTotalPrice($data);
            
            // Créer la session Stripe
            $session = $this->createStripeSession($data, $totalPrice);
            
            // Générer un identifiant unique pour la réservation
            $uniqueId = $this->generateUniqueId();
            
            // Créer la réservation avec statut "pending"
            $reservationId = $this->reservationsModel->addReservation(
                $data['user_id'] ?? 1,
                1, // event_id
                $data['ticket_type'],
                $data['quantity'],
                $totalPrice,
                $data['full_name'] ?? '',
                $data['email'] ?? '',
                $uniqueId ,
                'pending'
            );

            // Enregistrer les détails du paiement
            $this->paymentsModel->createPaymentRecord([
                'reservation_id' => $reservationId,
                'stripe_session_id' => $session->id,
                'amount' => $totalPrice,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            echo json_encode([
                'status' => 'success',
                'sessionId' => $session->id,
                'reservationId' => $reservationId,
                'url' => $session->url
            ]);

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function validateAndSanitizeInput() {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            throw new \Exception('Données JSON invalides');
        }

        $requiredFields = ['ticket_type', 'quantity', 'full_name', 'email'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                throw new \Exception("Le champ {$field} est requis");
            }
        }

        // Valider le type de billet
        if (!in_array($data['ticket_type'], ['VIP', 'PREMIUM', 'STANDART'])) {
            throw new \Exception('Type de billet invalide');
        }

        // Valider la quantité
        if (!filter_var($data['quantity'], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 10]])) {
            throw new \Exception('Quantité invalide (1-10 billets maximum)');
        }

        // Valider l'email
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \Exception('Email invalide');
        }

        return $data;
    }

    private function createStripeSession($data, $totalPrice) {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $totalPrice * 100,
                    'product_data' => [
                        'name' => 'Festival Pass - ' . strtoupper($data['ticket_type']),
                        'description' => "Quantité: {$data['quantity']}"
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $_ENV['APP_URL'] . '/reservations',
            'cancel_url' => $_ENV['APP_URL'] . '/payment-cancel',
            'metadata' => [
                'reservation_id' => $data['reservation_id'] ?? null,
                'ticket_type' => $data['ticket_type'],
                'quantity' => $data['quantity'],
                'email' => $data['email']
            ]
        ]);
    }

    
    public function handlePaymentSuccess() {
        try {
            if (!isset($_GET['session_id'])) {
                throw new \Exception('Session ID manquant');
            }
    
            $session = Session::retrieve($_GET['session_id']);
            $payment = $this->paymentsModel->getPaymentByStripeSessionId($session->id);
            
            if (!$payment) {
                throw new \Exception('Paiement non trouvé');
            }
    
            if ($session->payment_status === 'paid') {
                // Mettre à jour le statut du paiement
                $this->paymentsModel->updatePaymentStatus(
                    $payment['id'],
                    'completed',
                    json_encode([
                        'stripe_payment_intent' => $session->payment_intent,
                        'payment_method' => $session->payment_method_types[0],
                        'completed_at' => date('Y-m-d H:i:s')
                    ])
                );
    
                // Générer le QR code
                $qrCodeData = $this->generateQrCode($payment['reservation_id']);
                
                // Mettre à jour la réservation avec le QR code
                $this->reservationsModel->updateReservation(
                    $payment['reservation_id'],
                    [
                        'status' => 'confirmed',
                        'qr_code' => $qrCodeData,
                        'payment_confirmed_at' => date('Y-m-d H:i:s')
                    ]
                );
    
                // Envoyer l'email de confirmation avec le QR code
                $this->sendConfirmationEmail($payment['reservation_id'], $qrCodeData);
    
                header('Location: /reservations');
                exit;
            }
    
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function sendConfirmationEmail($reservationId, $qrCodePath) {
        try {
            $reservation = $this->reservationsModel->getUserReservations($reservationId);
            
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $_ENV['MAIL_HOST'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['MAIL_USERNAME'];
            $mail->Password   = $_ENV['MAIL_PASSWORD'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $_ENV['MAIL_PORT'];
            $mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
            $mail->addAddress($reservation['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Confirmation de votre réservation';
    
            if (file_exists($qrCodePath)) {
                $mail->addAttachment($qrCodePath, 'qr_code.png');
            } else {
                error_log("Le fichier QR Code est introuvable : " . $qrCodePath);
            }
    
            $emailContent = '<p>Merci pour votre réservation ! Voici votre QR Code :</p>';
            if (file_exists($qrCodePath)) {
                $mail->addEmbeddedImage($qrCodePath, 'qr_code.png');
                $emailContent .= '<p><img src="cid:qr_code.png" alt="QR Code"></p>';
            } else {
                $emailContent .= '<p>Erreur : Impossible d\'afficher le QR Code.</p>';
            }
    
            $mail->Body = $emailContent;
            $mail->send();
            error_log("Email envoyé avec succès à " . $reservation['email']);
        } catch (Exception $e) {
            error_log("Erreur d'envoi d'email: " . $mail->ErrorInfo);
        }
    }
    
    

    private function generateQrCode($reservationId) {
        $reservation = $this->reservationsModel->getUserReservations($reservationId);
        $qrData = json_encode([
            'id' => $reservation['unique_id'],
            'type' => $reservation['ticket_type'],
            'quantity' => $reservation['quantity'],
            'timestamp' => time(),
            'hash' => hash('sha256', $reservation['unique_id'] . $_ENV['QR_SALT'])
        ]);

        $result = Builder::create()
            ->data($qrData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->writer(new PngWriter())
            ->build();

        $qrCodePath = __DIR__ . '/../../storage/qr_codes/' . $reservation['unique_id'] . '.png';
        $result->saveToFile($qrCodePath);
        return $qrCodePath;
    }

    private function generateUniqueId() {
        return uniqid('RSV-', true) . '-' . bin2hex(random_bytes(4));
    }

    private function handleError(\Exception $e) {
        error_log($e->getMessage());
        http_response_code(500);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }

    private function calculateTotalPrice($data) {
        $prices = [
            'VIP' => 599,
            'PREMIUM' => 299,
            'STANDART' => 149
        ];

        if (!isset($prices[$data['ticket_type']])) {
            throw new \Exception('Type de billet invalide');
        }

        return $prices[$data['ticket_type']] * intval($data['quantity']);
    }
}