<?php

declare(strict_types=1);

namespace App\Core;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
    }

    public static function studioId(): int
    {
        return isset($_SESSION['studio_id']) ? (int) $_SESSION['studio_id'] : 0;
    }

    public static function studio(): ?array
    {
        return $_SESSION['auth_studio'] ?? null;
    }

    public static function rol(): string
    {
        return $_SESSION['auth_user']['rol'] ?? 'owner';
    }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $sid = (int) ($user['studio_id'] ?? 0);
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['studio_id'] = $sid;
        $_SESSION['auth_user'] = [
            'id'        => $user['id'],
            'nombre'    => $user['nombre'],
            'email'     => $user['email'],
            'rol'       => $user['rol']       ?? 'owner',
            'studio_id' => $sid,
        ];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function requireAuth(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    /** Allows only the studio owner. Redirects everyone else to /dashboard. */
    public static function requireOwner(): void
    {
        self::requireAuth();
        if (self::rol() !== 'owner') {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    /** Allow owner and admin; redirect staff to /dashboard. */
    public static function requireAdmin(): void
    {
        self::requireAuth();
        if (!in_array(self::rol(), ['owner', 'admin'], true)) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
    }

    /** Generate or return existing CSRF token */
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(): bool
    {
        $token = $_POST['_csrf'] ?? '';
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
