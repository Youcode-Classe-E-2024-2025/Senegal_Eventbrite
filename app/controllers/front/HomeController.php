<?php

namespace Controller_front;

use Core\Controller;
use Core\View;

class HomeController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $data = [
            'title' => 'Home Page',
            'description' => 'Welcome to the Home Page'
        ];

        $this->view('front/Home', $data);
    }
}

