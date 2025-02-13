<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../vendor/autoload.php';

\Config\Config::init();

session_start();

require_once __DIR__ . '/../app/models/Reservations.php';
require_once __DIR__ . '/../app/core/View.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/controllers/front/ReservationsController.php';
require_once __DIR__ . '/../app/config/routes.php';

