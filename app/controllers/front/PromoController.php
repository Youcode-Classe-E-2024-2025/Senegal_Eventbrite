<?php

namespace Controller_front;
use Core\Controller;
use DateTime;
use Model\Promo;

class PromoController extends Controller {
    public function store() {
        if($_SERVER["REQUEST_METHOD"] === "POST") {
            $code = $_POST["code"] ?? null;
            $discount = $_POST["discount"] ?? null;
            $activeDays = $_POST["activeDays"] ?? null;
            $usageLimit = $_POST["usageLimit"] ?? null;
            $eventId = $_POST["event"] ?? null;
            
            if(!$code || !$discount || !$activeDays || !$usageLimit || !$eventId) {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
                return;
            }
            
            $expirationDate = new DateTime();
            $expirationDate->modify("+{$activeDays} days");
            
            $data = [
                "code" => $code,
                "discount_percentage" => $discount,
                "event_id" => $eventId,
                "usage_limit" => $usageLimit,
                "expiration_date" => $expirationDate->format('Y-m-d H:i:s'),
            ];
            
            $promoModel = new Promo();
            $result = $promoModel->insertPromo($data);
            $userId = $_SESSION["user"]['id'] ?? null;
            
            if($result) {
                // Fetch the newly created promo with event details
                $promos = $promoModel->getAllPromosWithEvents($userId);
                $newPromo = array_filter($promos, function($promo) use ($code) {
                    return $promo['code'] === $code;
                });
                $newPromo = reset($newPromo);
                
                echo json_encode(['status' => 'success', 'promo' => $newPromo]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error creating promo code']);
            }
        }
    }
    
    public function getAll() {
        $promoModel = new Promo();
        $userId = $_SESSION["user"]['id'] ?? null;
        $promos = $promoModel->getAllPromosWithEvents($userId);
        echo json_encode($promos);
    }
    
    public function delete() {
        if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['id'])) {
            $promoModel = new Promo();
            $result = $promoModel->deletePromo($_POST['id']);
            echo json_encode(['status' => $result ? 'success' : 'error']);
        }
    }
}
