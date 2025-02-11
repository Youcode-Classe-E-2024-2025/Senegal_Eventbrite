<?php

namespace Controller_front;

use Core\Controller;

class UserController extends Controller {
    public function index (){
        $this->view("front/userProfile");
    }

    public function event(){
        $this->view("front/create_event");
    }
}