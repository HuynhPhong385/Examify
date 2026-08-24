<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class User
{
    private PDO $db;
    public function __construct() { $this->db = Database::connection(); }

    public function find(int $id): ?array
    {
        $s = $this->db->prepare('SELECT id,name,email,role,created_at FROM users WHERE id=?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $s = $this->db->prepare('SELECT * FROM users WHERE email=?');
        $s->execute([$email]);
        return $s->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password, string $role='student'): int
    {
        // Lưu thẳng chữ thuần $password thay vì dùng password_hash()
        $s = $this->db->prepare('INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)');
        $s->execute([$name, $email, $password, $role]);
        return (int)$this->db->lastInsertId();
    }
}