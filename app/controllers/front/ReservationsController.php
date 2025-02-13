<?php

namespace App\Controllers;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Models\Reservations;
// use Models\Payments;
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
            'success_url' => $_ENV['APP_URL'] . '/payment-success?session_id={CHECKOUT_SESSION_ID}',
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

                // Générer et sauvegarder le QR code
                $qrCodeData = $this->generateQrCode($payment['reservation_id']);
                
                // Mettre à jour la réservation
                $this->reservationsModel->updateReservation(
                    $payment['reservation_id'],
                    [
                        'status' => 'confirmed',
                        'qr_code' => $qrCodeData,
                        'payment_confirmed_at' => date('Y-m-d H:i:s')
                    ]
                );

                // Envoyer l'email de confirmation avec le QR code
                $this->sendConfirmationEmail($payment['reservation_id']);

                header('Location: /reservation-success');
                exit;
            }

        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }

    private function generateTicketPDF($reservationId, $qrCodeData) {
        try {
            $reservation = $this->reservationsModel->getReservationById($reservationId);
            
            // Configurer DomPDF
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isPhpEnabled', true);
            $options->set('isRemoteEnabled', true);
            
            $dompdf = new Dompdf($options);
            
            // Générer le HTML du ticket
            $html = $this->generateTicketHTML($reservation, $qrCodeData);
            
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            // Créer le dossier de stockage des PDF s'il n'existe pas
            $pdfDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/tickets/';
            if (!is_dir($pdfDir)) {
                mkdir($pdfDir, 0755, true);
            }

            // Générer un nom de fichier unique
            $filename = 'ticket_' . $reservation['unique_id'] . '.pdf';
            $pdfPath = $pdfDir . $filename;

            // Sauvegarder le PDF
            file_put_contents($pdfPath, $dompdf->output());

            return '/storage/tickets/' . $filename; // Retourner le chemin relatif
        } catch (\Exception $e) {
            error_log("Erreur lors de la génération du PDF: " . $e->getMessage());
            throw $e;
        }
    }

    private function generateTicketHTML($reservation, $qrCodeData) {
        // Convertir les dates en format lisible
        $eventDate = date('d/m/Y', strtotime($reservation['event_date']));
        $purchaseDate = date('d/m/Y H:i', strtotime($reservation['payment_confirmed_at']));

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Ticket Festival</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }
                .ticket {
                    max-width: 800px;
                    margin: 0 auto;
                    padding: 20px;
                    border: 2px solid #000;
                    border-radius: 10px;
                }
                .header {
                    text-align: center;
                    border-bottom: 2px solid #000;
                    padding-bottom: 20px;
                    margin-bottom: 20px;
                }
                .qr-code {
                    text-align: center;
                    margin: 20px 0;
                }
                .qr-code img {
                    max-width: 200px;
                }
                .details {
                    margin: 20px 0;
                }
                .footer {
                    text-align: center;
                    font-size: 12px;
                    margin-top: 20px;
                    padding-top: 20px;
                    border-top: 1px solid #ccc;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <div class="header">
                    <h1>Festival 2024</h1>
                    <h2>Billet ' . htmlspecialchars($reservation['ticket_type']) . '</h2>
                </div>
                
                <div class="details">
                    <p><strong>Numéro de réservation:</strong> ' . htmlspecialchars($reservation['unique_id']) . '</p>
                    <p><strong>Type de billet:</strong> ' . htmlspecialchars($reservation['ticket_type']) . '</p>
                    <p><strong>Quantité:</strong> ' . htmlspecialchars($reservation['quantity']) . '</p>
                    <p><strong>Date de l\'événement:</strong> ' . htmlspecialchars($eventDate) . '</p>
                    <p><strong>Date d\'achat:</strong> ' . htmlspecialchars($purchaseDate) . '</p>
                </div>

                <div class="qr-code">
                    <img src="data:image/png;base64,' . $qrCodeData . '" alt="QR Code">
                </div>

                <div class="footer">
                    <p>Ce billet est unique et ne peut être utilisé qu\'une seule fois.</p>
                    <p>Conservez-le précieusement et présentez-le à l\'entrée du festival.</p>
                </div>
            </div>
        </body>
        </html>';
    }

    private function sendConfirmationEmail($reservationId, $pdfPath) {
        try {
            $reservation = $this->reservationsModel->getReservationById($reservationId);
            
            $mail = new PHPMailer(true);
            $mail->isHTML(true);
            $mail->setFrom($_ENV['MAIL_FROM'], 'Festival');
            $mail->addAddress($reservation['email']);
            $mail->Subject = 'Confirmation de votre réservation';
            
            // Ajouter le PDF en pièce jointe
            $pdfFullPath = $_SERVER['DOCUMENT_ROOT'] . $pdfPath;
            if (file_exists($pdfFullPath)) {
                $mail->addAttachment($pdfFullPath, 'votre_billet.pdf');
            }
            
            // Créer le contenu HTML de l'email
            $emailContent = $this->view->render('emails/reservation-confirmation', [
                'reservation' => $reservation,
                'qrCode' => $reservation['qr_code']
            ], true);
            
            $mail->Body = $emailContent;
            $mail->send();
            
        } catch (\Exception $e) {
            error_log("Erreur d'envoi d'email: " . $e->getMessage());
            // Ne pas propager l'erreur pour ne pas bloquer le processus
        }
    }


    private function generateQrCode($reservationId) {
        $reservation = $this->reservationsModel->getReservationById($reservationId);
        
        // Données à encoder dans le QR code
        $qrData = [
            'id' => $reservation['unique_id'],
            'type' => $reservation['ticket_type'],
            'quantity' => $reservation['quantity'],
            'timestamp' => time(),
            'hash' => hash('sha256', $reservation['unique_id'] . $_ENV['QR_SALT'])
        ];

        // Créer le QR code
        $result = Builder::create()
            ->data(json_encode($qrData))
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(new ErrorCorrectionLevelHigh())
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->writer(new PngWriter())
            ->build();

        // Retourner le QR code en base64
        return base64_encode($result->getString());
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

    // private function sendConfirmationEmail($reservationId) {
    //     try {
    //         $reservation = $this->reservationsModel->getReservationById($reservationId);
            
    //         $mail = new PHPMailer(true);
    //         $mail->isHTML(true);
    //         $mail->setFrom($_ENV['MAIL_FROM'], 'Festival');
    //         $mail->addAddress($reservation['email']);
    //         $mail->Subject = 'Confirmation de votre réservation';
            
    //         // Créer le contenu HTML de l'email avec le QR code
    //         $emailContent = $this->view->render('emails/reservation-confirmation', [
    //             'reservation' => $reservation,
    //             'qrCode' => $reservation['qr_code']
    //         ], true);
            
    //         $mail->Body = $emailContent;
    //         $mail->send();
            
    //     } catch (\Exception $e) {
    //         error_log("Erreur d'envoi d'email: " . $e->getMessage());
    //         // Ne pas propager l'erreur pour ne pas bloquer le processus
    //     }
    // }
}