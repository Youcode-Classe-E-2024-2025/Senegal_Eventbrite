<?php
namespace Controller_front;
use Core\Controller;
use Model\Event;
use Model\Category;

class EventController extends Controller {
    public function store() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            try {
                $organizerId = $_SESSION['user']["id"] ?? null;
                if (!$organizerId) {
                    throw new \Exception("User must be logged in to create events");
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

                    $_SESSION['success'] = "Event created successfully!";
                    header('Location: /userDash');
                    exit();
                } else {
                    throw new \Exception("Failed to create event");
                }

            } catch (\Exception $e) {
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

    public function event() {
        $categoryModel = new Category();
        $categories = $categoryModel->getAllCategory();
        $this->view("front/create_event", [
            'categories' => $categories
        ]);
    }
}