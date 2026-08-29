<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $pdo = null;
    private static array $config = [];

    public static function init(array $config): void
    {
        self::$config = $config;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        

        $c = self::$config;
        if (empty($c)) {
            throw new RuntimeException("Chưa nạp cấu hình Database. Vui lòng gọi Database::init() trước.");
        }
        $host    = $c['host'] ?? '127.0.0.1';
        $port    = $c['port'] ?? '3306';
        $name    = $c['name'] ?? 'examify'; 
        $charset = $c['charset'] ?? 'utf8mb4';
        $user    = $c['user'] ?? 'root';
        $pass    = $c['pass'] ?? '';
        if (empty($name)) {
            throw new RuntimeException("Tên cơ sở dữ liệu (dbname) không được để trống trong cấu hình.");
        }
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $host,
            $port,
            $name,
            $charset
        );

        self::$pdo = new PDO($dsn, $c['user'] ?? 'root',
                $c['pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$pdo;
    }
}
