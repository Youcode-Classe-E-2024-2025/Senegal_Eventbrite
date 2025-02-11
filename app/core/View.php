<?php
namespace App\Core;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class View
{
    private $twig;

    public function __construct() {
        $loader = new FilesystemLoader(dirname(__DIR__) . '/views');
        $this->twig = new Environment($loader);
    }

    public function render(string $view, array $data = []) {
        echo $this->twig->render($view . '.twig', $data);
    }

    public function addGlobal(string $name, $value) {
        $this->twig->addGlobal($name, $value);
    }
}
