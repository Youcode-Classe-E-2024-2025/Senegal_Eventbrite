<?php
namespace Config;

use Controller_front\HomeController;
use Controller_front\UserController;
use Controller_back\categoryController;
use Controller_back\dashboardController;
use Core\Router;
use Controller_front\AuthController;

$router = new Router();

$routes = [
    'GET' => [
        '/' => [HomeController::class, 'index'],
        '/signup' => [AuthController::class, 'signup'],
        '/login' => [AuthController::class, 'login'],
        '/logout'=> [AuthController::class, 'logout'],
        '/userDash' => [UserController::class, 'index'],
        '/createEvent' => [UserController::class, 'event'],
        '/profileInfo' => [UserController::class, 'info'],
        '/admin' => [dashboardController::class, 'dashboard'],
        '/google-login' => [AuthController::class, 'googleLog'],
    ],
    'POST' => [
        '/createCtaegory' => [categoryController::class, 'createCategory'],
        '/deleteCategory' => [categoryController::class, 'deleteCategory'],
        '/signup' => [AuthController::class, 'signupPost'],
        '/login' => [AuthController::class, 'loginPost'],
    ]
];

foreach ($routes as $method => $routesList) {
    foreach ($routesList as $uri => $controller) {
        $router->{strtolower($method)}($uri, $controller);
    }
}
$router->dispatch();
