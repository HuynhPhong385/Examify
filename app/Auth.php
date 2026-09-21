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

            $stmt = $pdo->prepare(
                'SELECT id, name, email, password, role 
                FROM users 
                WHERE LOWER(email) = ? 
                LIMIT 1'
            );

            $stmt->execute([mb_strtolower(trim($email))]);
            $user = $stmt->fetch();
            // if ($user) {
            //         var_dump($user, $password); exit(); // Bỏ comment dòng này để xem dữ liệu thô
            //     }
            if (!$user) {
                return false;
            }

            $storedPassword = (string)$user['password'];

            // Hỗ trợ mật khẩu cũ dạng text trong dữ liệu mẫu, đồng thời dùng hash
            // cho mọi tài khoản mới và tự nâng cấp mật khẩu cũ sau lần đăng nhập đúng.
            if (password_get_info($storedPassword)['algo'] !== null) {
                $validPassword = password_verify($password, $storedPassword);
            } else {
                $validPassword = hash_equals($storedPassword, $password);
                if ($validPassword) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $update = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
                    $update->execute([$newHash, $user['id']]);
                }
            }

            if ($validPassword) {
                // Lưu thông tin user vào Session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];
                return true;
            }

            return false;
        }
        public static function logout(): void
        {
            // Xóa thông tin user khỏi session
            unset($_SESSION['user']);
            // Hủy toàn bộ session hiện tại
            session_destroy();
        }
}
