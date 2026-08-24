<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Question
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function all(string $q='', ?int $subject=null, ?string $difficulty=null, int $limit=10, int $offset=0):array {
        $where=[];$p=[];
        if($q!==''){ $where[]='q.content LIKE ?'; $p[]='%'.$q.'%'; }
        if($subject){$where[]='q.subject_id=?';$p[]=$subject;}
        if($difficulty){$where[]='q.difficulty=?';$p[]=$difficulty;}
        $sql='SELECT q.*,s.name subject_name FROM questions q JOIN subjects s ON s.id=q.subject_id';
        if($where)$sql.=' WHERE '.implode(' AND ',$where);
        $sql.=' ORDER BY q.id DESC LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
        $st=$this->db->prepare($sql);$st->execute($p);return $st->fetchAll();
    }
    public function count(string $q='', ?int $subject=null, ?string $difficulty=null):int {
        $where=[];$p=[];
        if($q!==''){$where[]='content LIKE ?';$p[]='%'.$q.'%';}
        if($subject){$where[]='subject_id=?';$p[]=$subject;}
        if($difficulty){$where[]='difficulty=?';$p[]=$difficulty;}
        $sql='SELECT COUNT(*) FROM questions'.($where?' WHERE '.implode(' AND ',$where):'');
        $st=$this->db->prepare($sql);$st->execute($p);return (int)$st->fetchColumn();
    }
    public function find(int $id):?array {
        $st=$this->db->prepare('SELECT * FROM questions WHERE id=?');$st->execute([$id]);return $st->fetch()?:null;
    }
    public function create(array $d):int {
        $st=$this->db->prepare('INSERT INTO questions(subject_id,content,image,difficulty,option_a,option_b,option_c,option_d,correct_option,created_by) VALUES(?,?,?,?,?,?,?,?,?,?)');
        $st->execute([$d['subject_id'],$d['content'],$d['image'],$d['difficulty'],$d['option_a'],$d['option_b'],$d['option_c'],$d['option_d'],$d['correct_option'],$d['created_by']]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id,array $d):void {
        $st=$this->db->prepare('UPDATE questions SET subject_id=?,content=?,image=?,difficulty=?,option_a=?,option_b=?,option_c=?,option_d=?,correct_option=? WHERE id=?');
        $st->execute([$d['subject_id'],$d['content'],$d['image'],$d['difficulty'],$d['option_a'],$d['option_b'],$d['option_c'],$d['option_d'],$d['correct_option'],$id]);
    }
    public function delete(int $id):void{$st=$this->db->prepare('DELETE FROM questions WHERE id=?');$st->execute([$id]);}
}
