<?php
declare(strict_types=1);

final class Auth
{
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function attempt(string $email, string $password): bool
    {
        $pdo = Database::pdo();

        $email = mb_strtolower(trim($email));

        $stmt = $pdo->prepare(
            'SELECT id, name, email, password, role 
             FROM users 
             WHERE LOWER(email) = ? 
             LIMIT 1'
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            return false;
        }

        $storedPassword = (string)$user['password'];

        /*
         * Kiểm tra mật khẩu.
         *
         * - Nếu DB đã lưu password_hash() -> password_verify()
         * - Nếu DB cũ đang lưu plaintext -> so sánh tạm thời
         */
        $validPassword = false;

        if (password_get_info($storedPassword)['algo'] !== 0) {
            // Password đã được hash
            $validPassword = password_verify($password, $storedPassword);
        } else {
            // Password cũ đang lưu plaintext
            $validPassword = hash_equals($storedPassword, $password);

            // Đăng nhập đúng thì tự động chuyển sang password hash
            if ($validPassword) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);

                $update = $pdo->prepare(
                    'UPDATE users SET password = ? WHERE id = ?'
                );

                $update->execute([
                    $newHash,
                    $user['id']
                ]);

                $user['password'] = $newHash;
            }
        }

        if (!$validPassword) {
            return false;
        }

        session_regenerate_id(true);

        unset($user['password']);

        $_SESSION['user'] = $user;

        // Tạo lại CSRF token nếu cần
        csrf_token();

        return true;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                time() - 42000,
                $p['path'],
                $p['domain'],
                $p['secure'],
                $p['httponly']
            );
        }

        session_destroy();
    }
}