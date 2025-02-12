<?php
namespace Core;

use Model\User;

class Auth
{
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function login(User $user): void
    {
        $userData = [
            'id' => $user->id ?? null,
            'username' => $user->username,
            'email' => $user->email,
            'roles' => $user->roles ?? [],
        ];

        $_SESSION['user'] = $userData;
    }


    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function hasRole(string $role): bool
    {
        return self::check() && in_array($role, $_SESSION['user']['roles'] ?? []);
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /loginForm');
            exit();
        }
    }
}
