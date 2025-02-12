<?php

namespace Controller_front;

use Core\Controller;
use Model\Event;
use Model\Promo;

class UserController extends Controller {
    public function index() {
        $eventModel = new Event();
        $promoModel = new Promo();

        $userId = $_SESSION['user']["id"] ?? null;
        
        $events = $eventModel->getAllEvent();
        $promos = $promoModel->getAllPromosWithEvents(); // Get all promos with event details
        
        $this->view("front/userDash", [
            'events' => $events,
            'promos' => $promos
        ]);
    }

    public function event(){
        $this->view("front/create_event");
    }

    public function info(){
        $this->view("front/profileInfo");
    }
}