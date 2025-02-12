<?php
namespace Config;

use Core\Router;
use Controller_front\HomeController;
use Controller_front\UserController;
use Controller_back\categoryController;
use Controller_back\dashboardController;
use Controller_front\AuthController;
use Controller_front\EventController;
use Controller_front\PromoController;

$router = new Router();

$routes = [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/signup' => [AuthController::class, 'signup'],
        '/login' => [AuthController::class, 'login'],
        '/logout'=> [AuthController::class, 'logout'],
        '/userDash' => [UserController::class, 'index'],
        '/createEvent' => [EventController::class, 'event'],
        '/profileInfo' => [UserController::class, 'info'],
        '/admin' => [dashboardController::class, 'dashboard'],
        '/google-login' => [AuthController::class, 'googleLog'],
        '/github-login' => [AuthController::class, 'githubLog'],
    ],
    'POST' => [
        '/createCtaegory' => [categoryController::class, 'createCategory'],
        '/deleteCategory' => [categoryController::class, 'deleteCategory'],
        '/deleteEvent' => [dashboardController::class, 'deleteEvent'],
        '/signup' => [AuthController::class, 'signupPost'],
        '/login' => [AuthController::class, 'loginPost'],
        '/promo' => [PromoController::class, 'store'],
        '/promo/delete' => [PromoController::class, 'delete'],
        '/createEvent' => [EventController::class, 'store'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}
$router->dispatch();
