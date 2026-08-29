<?php
declare(strict_types=1);

final class Auth
{
    public static function user(): ?array
    {
        $pdo = Database::pdo();
        /*$stmt = $pdo->query("SELECT id, name, email, password, role FROM users WHERE email = 'student@examify.local' LIMIT 1");
        return $_SESSION['user'] ?? $stmt->fetchAll();*/
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $pdo = Database::pdo();
        
        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1'
        );
        $stmt->execute([mb_strtolower(trim($email))]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        unset($user['password']);
        $_SESSION['user'] = $user;
        csrf_token();
        return true;
    }

    /*public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }*/
}
