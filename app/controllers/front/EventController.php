<?php
namespace Controller_front;
use Core\Controller;
use Exception;
use Model\Event;
use Model\Category;
use PHPMailer\PHPMailer\PHPMailer;


class EventController extends Controller
{
    public function store()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $organizerEmail = $_SESSION['user']["email"] ?? null;
                $organizerName = $_SESSION['user']["name"] ?? 'Organizer';
                $organizerId = $_SESSION['user']["id"] ?? null;
                if (!$organizerId) {
                    throw new Exception("User must be logged in to create events");
                }

                $thumbnailPath = null;
                if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'assets/uploads/userEvents/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $fileName = uniqid() . '_' . basename($_FILES['thumbnail']['name']);
                    $thumbnailPath = $uploadDir . $fileName;

                    if (!move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbnailPath)) {
                        throw new \Exception("Failed to upload thumbnail");
                    }
                }

                $tags = isset($_POST['tags']) ? array_filter(preg_split('/[,\s]+/', $_POST['tags'])) : [];

                $eventData = [
                    'title' => $_POST['title'],
                    'artist_name' => $_POST['artist'],
                    'category' => $_POST['category'],
                    'tags' => "{" . implode(",", $tags) . "}",
                    'date_start' => $_POST['date_start'],
                    'date_end' => $_POST['date_end'],
                    'location' => $_POST['location'],
                    'price' => floatval($_POST['price']),
                    'capacity' => intval($_POST['capacity']),
                    'organizer_id' => $organizerId,
                    'status' => 'ACTIVE',
                    'isActif' => true,
                    'thumbnail' => $thumbnailPath
                ];

                $eventModel = new Event();
                $eventId = $eventModel->insert('events', $eventData, true);

                if ($eventId) {
                    $statsData = [
                        'event_id' => $eventId,
                        'tickets_sold' => 0,
                        'revenue' => 0.00,
                        'participants_count' => 0
                    ];
                    $eventModel->insert('event_statistics', $statsData);

                    $this->sendEventCreationEmail($organizerEmail, $organizerName, $eventData);

                    $_SESSION['success'] = "Event created successfully!";
                    header('Location: /userDash');
                    exit();
                } else {
                    throw new Exception("Failed to create event");
                }

            } catch (Exception $e) {
                $_SESSION['error'] = $e->getMessage();
                echo "<pre>";
                var_dump($_SESSION['error']);
                echo "</pre>";

                echo "<pre>";
                var_dump($eventData);
                echo "</pre>";

                echo "<pre>";
                var_dump($_SESSION['user']);
                var_dump($_SESSION['user']["id"]);
                echo "</pre>";
                exit();
            }
        }
    }

    public function event()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategory();
        $this->view("front/create_event", [
            'categories' => $categories
        ]);
    }

    private function sendEventCreationEmail($email, $name, $event)
    {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_Host'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_Username'];
            $mail->Password = $_ENV['SMTP_Password'];
            $mail->SMTPSecure = $_ENV['SMTP_Secure'];
            $mail->Port = $_ENV['SMTP_Port'];

            $mail->setFrom($_ENV['SMTP_Username'], 'ZHOO Events');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Your Event Has Been Created!';

            $mail->Body = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #e0e0e0;
                    background-color: #121212;
                    margin: 0;
                    padding: 20px;
                }
                .container {
                    padding: 20px;
                    border: 1px solid #333;
                    border-radius: 5px;
                    width: 90%;
                    max-width: 600px;
                    margin: auto;
                    background-color: #1e1e1e;
                }
                .header {
                    background-color: #0e1111;
                    color: #ffffff;
                    padding: 14px;
                    text-align: center;
                    font-size: 20px;
                    font-weight: bold;
                    border-radius: 5px 5px 0 0;
                }
                .content {
                    padding: 20px;
                    font-size: 16px;
                    line-height: 1.6;
                    color: #d4d4d4;
                }
                .footer {
                    margin-top: 20px;
                    font-size: 14px;
                    color: #888888;
                    text-align: center;
                    border-top: 1px solid #333;
                    padding-top: 10px;
                }
                a {
                    background-color: yellow;
                    padding: 10px;
                    border-radius: 10px;
                    color: black;
                    text-decoration: none;
                }
                p {
                    color: #888888;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <img src="https://lh3.googleusercontent.com/pw/AP1GczMv2wyQdFNW7zVUC0W4vUVSOTl7yWWPf718uoa3c3Wlw2gCwANtImpdZcKPYmOoZ2ruqMcr4FHtFKm6sUoEC_9oBMG7wqOPDjH6arfHLkHJQd7EP-QA2BB9JL7KKRBl1r1_Lv7PMdxhJVN2svmC41ob=w500-h210-s-no-gm?authuser=0" width="150" alt="ZHOO Logo">
                </div>
                <div class="content">
                    <p>Dear <strong>' . htmlspecialchars($name) . '</strong>,</p>
                    <p>Your event "<strong>' . htmlspecialchars($event['title']) . '</strong>" has been successfully created.</p>
                    <p><strong>Artist:</strong> ' . htmlspecialchars($event['artist_name']) . '</p>
                    <p><strong>Category:</strong> ' . htmlspecialchars($event['category']) . '</p>
                    <p><strong>Start Date:</strong> ' . htmlspecialchars($event['date_start']) . '</p>
                    <p><strong>End Date:</strong> ' . htmlspecialchars($event['date_end']) . '</p>
                    <p><strong>Location:</strong> ' . htmlspecialchars($event['location']) . '</p>
                    <p><strong>Price:</strong> $' . number_format($event['price'], 2) . '</p>
                    <p>Thank you for using ZHOO Events!</p>
                    <a href="http://localhost/userDash">Manage Your Event</a>
                </div>
                <div class="footer">
                    &copy; ' . date("Y") . ' ZHOO. All rights reserved.
                </div>
            </div>
        </body>
        </html>';

            $mail->send();
        } catch (Exception $e) {
            error_log("Event creation email could not be sent. Mailer Error: {$mail->ErrorInfo}");
        }
    }
}