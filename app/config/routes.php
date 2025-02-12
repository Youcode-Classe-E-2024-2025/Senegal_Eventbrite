<?php
namespace App\Config;

use Controller_front\HomeController;
use Controller_front\UserController;
use Controller_back\categoryController;
use Controller_back\dashboardController;
use Core\Router;
use App\Controllers\ReservationsController;
use Core\View;

use Controller_front\AuthController;
use Controller_front\PromoController;

$router = new Router();

// $routes = [
//     'GET' => [
//         // Route existante pour afficher les réservations
//         '/reservations' => [ReservationsController::class, 'reservations'],
//         '/generate-qr-code/{reservation_id}' => [ReservationsController::class, 'generateQrCode'],

//     ],
//     'POST' => [
//       '/create-reservation'=> [ReservationsController::class, 'createReservation'],
//         '/' => [HomeController::class, 'index'],
//         '/signup' => [AuthController::class, 'signup'],
//         '/login' => [AuthController::class, 'login'],
//         '/logout'=> [AuthController::class, 'logout'],
//         '/userDash' => [UserController::class, 'index'],
//         '/createEvent' => [UserController::class, 'event'],
//         '/profileInfo' => [UserController::class, 'info'],
//         '/admin' => [dashboardController::class, 'dashboard'],
//         '/google-login' => [AuthController::class, 'googleLog'],
//         '/github-login' => [AuthController::class, 'githubLog'],
//         '/createCtaegory' => [categoryController::class, 'createCategory'],
//         '/deleteCategory' => [categoryController::class, 'deleteCategory'],
//         '/deleteEvent' => [dashboardController::class, 'deleteEvent'],
//         '/signup' => [AuthController::class, 'signupPost'],
//         '/login' => [AuthController::class, 'loginPost'],
//         '/promo' => [PromoController::class, 'store'],
//         '/promo/delete' => [PromoController::class, 'delete']
//     ]
//     ];
    
    $routes = [
        'GET' => [
             // Route existante pour afficher les réservations
        '/reservations' => [ReservationsController::class, 'reservations'],
        // '/generate-qr-code/{reservation_id}' => [ReservationsController::class, 'generateQrCode'],
            '/' => [HomeController::class, 'index'],
            '/signup' => [AuthController::class, 'signup'],
            '/login' => [AuthController::class, 'login'],
            '/logout'=> [AuthController::class, 'logout'],
            '/userDash' => [UserController::class, 'index'],
            '/createEvent' => [UserController::class, 'event'],
            '/profileInfo' => [UserController::class, 'info'],
            '/admin' => [dashboardController::class, 'dashboard'],
            '/google-login' => [AuthController::class, 'googleLog'],
            '/github-login' => [AuthController::class, 'githubLog'],
        ],
        'POST' => [
      '/create-reservation'=> [ReservationsController::class, 'createReservation'],
      '/api/reservations'=> [ReservationsController::class, 'createReservation'],

            '/createCtaegory' => [categoryController::class, 'createCategory'],
            '/deleteCategory' => [categoryController::class, 'deleteCategory'],
            '/deleteEvent' => [dashboardController::class, 'deleteEvent'],
            '/signup' => [AuthController::class, 'signupPost'],
            '/login' => [AuthController::class, 'loginPost'],
            '/promo' => [PromoController::class, 'store'],
            '/promo/delete' => [PromoController::class, 'delete'],
        ]
    ];
    

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}
$router->dispatch();
