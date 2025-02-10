<?php
namespace Core;

class Router {
    private $routes = [];

    public function get($uri, $controller) {
        $this->routes['GET'][$uri] = $controller;
    }

    public function post($uri, $controller) {
        $this->routes['POST'][$uri] = $controller;
    }

    public function dispatch() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = urldecode($uri);
        $uri = '/' . trim($uri, '/');

        if (isset($this->routes[$method][$uri])) {
            $this->executeRoute($this->routes[$method][$uri]);
            return;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            $pattern = preg_replace('/{[^}]+}/', '([^/]+)', $route);
            $pattern = str_replace('/', '\/', $pattern);
            
            if (preg_match('/^' . $pattern . '$/', $uri, $matches)) {
                array_shift($matches);
                $this->executeRoute($handler, $matches);
                return;
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }

    private function executeRoute($handler, $params = []) {
        [$controller, $action] = $handler;
        $controllerInstance = new $controller();
        $controllerInstance->setRouter($this);
        call_user_func_array([$controllerInstance, $action], $params);
    }
}
