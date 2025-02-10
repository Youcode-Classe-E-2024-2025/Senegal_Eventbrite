<?php
namespace Core;

class Controller
{
    protected $router;
    protected $view;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->view = new View();

        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = Security::generateCsrfToken();
        }
        $this->view->addGlobal('csrf_token', $_SESSION['_token']);
        $this->view->addGlobal('session', $_SESSION);
    }

    public function setRouter($router) {
        $this->router = $router;
    }

    public function view(string $view, array $data = [])
    {
        $this->view->render($view, $data);
    }

    public function redirect(string $url)
    {
        header('Location: ' . $url);
        exit;
    }
}
