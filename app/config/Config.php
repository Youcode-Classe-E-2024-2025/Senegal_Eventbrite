<?php
namespace Config;

use Dotenv\Dotenv;

class Config {
    public static function init() {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
        $dotenv->load();
    }

    public static function get($key, $default = null) {
        return $_ENV[strtoupper(str_replace('.', '_', $key))] ?? $default;
    }
}
