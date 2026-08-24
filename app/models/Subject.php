<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Subject
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function all(string $q='', int $limit=10, int $offset=0): array {
        $sql='SELECT s.*, COUNT(q.id) question_count FROM subjects s LEFT JOIN questions q ON q.subject_id=s.id';
        $params=[];
        if($q!==''){ $sql.=' WHERE s.name LIKE ?'; $params[]='%'.$q.'%'; }
        $sql.=' GROUP BY s.id ORDER BY s.id DESC LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
        $st=$this->db->prepare($sql); $st->execute($params); return $st->fetchAll();
    }
    public function count(string $q=''): int {
        $sql='SELECT COUNT(*) FROM subjects'; $params=[];
        if($q!==''){ $sql.=' WHERE name LIKE ?'; $params[]='%'.$q.'%'; }
        $st=$this->db->prepare($sql); $st->execute($params); return (int)$st->fetchColumn();
    }
    public function find(int $id): ?array {
        $st=$this->db->prepare('SELECT * FROM subjects WHERE id=?'); $st->execute([$id]); return $st->fetch() ?: null;
    }
    public function create(string $name,string $description):int {
        $st=$this->db->prepare('INSERT INTO subjects(name,description) VALUES(?,?)'); $st->execute([$name,$description]); return (int)$this->db->lastInsertId();
    }
    public function update(int $id,string $name,string $description):void {
        $st=$this->db->prepare('UPDATE subjects SET name=?,description=? WHERE id=?'); $st->execute([$name,$description,$id]);
    }
    public function delete(int $id):void { $st=$this->db->prepare('DELETE FROM subjects WHERE id=?'); $st->execute([$id]); }
}
