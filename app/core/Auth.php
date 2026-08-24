<?php
declare(strict_types=1);

namespace App\Core;

use App\Models\User;

class Auth
{
    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) return null;
        return (new User)->find((int)$_SESSION['user_id']);
    }

    public static function check(): bool { return self::user() !== null; }

    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time()-42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            exit('403 - Bạn không có quyền truy cập.');
        }
    }
}
