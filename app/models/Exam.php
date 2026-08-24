<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Exam
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function all(string $q='', int $limit=10,int $offset=0,?int $userId=null):array {
        $sql='SELECT e.*,s.name subject_name,u.name creator,
              (SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id=e.id) question_count
              FROM exams e JOIN subjects s ON s.id=e.subject_id JOIN users u ON u.id=e.created_by';
        $p=[];
        if($q!==''){ $sql.=' WHERE e.title LIKE ?';$p[]='%'.$q.'%'; }
        $sql.=' ORDER BY e.id DESC LIMIT '.(int)$limit.' OFFSET '.(int)$offset;
        $st=$this->db->prepare($sql);$st->execute($p);return $st->fetchAll();
    }
    public function count(string $q=''):int{
        $sql='SELECT COUNT(*) FROM exams';$p=[];
        if($q!==''){$sql.=' WHERE title LIKE ?';$p[]='%'.$q.'%';}
        $st=$this->db->prepare($sql);$st->execute($p);return (int)$st->fetchColumn();
    }
    public function published():array{
        return $this->db->query("SELECT e.*,s.name subject_name,(SELECT COUNT(*) FROM exam_questions eq WHERE eq.exam_id=e.id) question_count FROM exams e JOIN subjects s ON s.id=e.subject_id WHERE e.status='published' ORDER BY e.id DESC")->fetchAll();
    }
    public function find(int $id):?array{
        $st=$this->db->prepare('SELECT e.*,s.name subject_name FROM exams e JOIN subjects s ON s.id=e.subject_id WHERE e.id=?');$st->execute([$id]);return $st->fetch()?:null;
    }
    public function questions(int $id):array{
        $st=$this->db->prepare('SELECT q.* FROM exam_questions eq JOIN questions q ON q.id=eq.question_id WHERE eq.exam_id=? ORDER BY eq.sort_order,eq.question_id');$st->execute([$id]);return $st->fetchAll();
    }
    public function create(array $d):int{
        $st=$this->db->prepare('INSERT INTO exams(subject_id,title,description,duration_minutes,total_questions,status,created_by) VALUES(?,?,?,?,?,?,?)');
        $st->execute([$d['subject_id'],$d['title'],$d['description'],$d['duration_minutes'],$d['total_questions'],$d['status'],$d['created_by']]);
        return (int)$this->db->lastInsertId();
    }
    public function update(int $id,array $d):void{
        $st=$this->db->prepare('UPDATE exams SET subject_id=?,title=?,description=?,duration_minutes=?,total_questions=?,status=? WHERE id=?');
        $st->execute([$d['subject_id'],$d['title'],$d['description'],$d['duration_minutes'],$d['total_questions'],$d['status'],$id]);
    }
    public function delete(int $id):void{$st=$this->db->prepare('DELETE FROM exams WHERE id=?');$st->execute([$id]);}
    public function setQuestions(int $id,array $questionIds):void{
        $this->db->beginTransaction();
        try{
            $this->db->prepare('DELETE FROM exam_questions WHERE exam_id=?')->execute([$id]);
            $st=$this->db->prepare('INSERT INTO exam_questions(exam_id,question_id,sort_order) VALUES(?,?,?)');
            foreach(array_values($questionIds) as $i=>$qid)$st->execute([$id,(int)$qid,$i+1]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
    }
    public function randomQuestions(int $subjectId,int $total,array $difficultyCounts=[]):array{
        $picked=[];
        foreach($difficultyCounts as $difficulty=>$count){
            if($count<=0)continue;
            $st=$this->db->prepare('SELECT id FROM questions WHERE subject_id=? AND difficulty=? ORDER BY RAND() LIMIT '.(int)$count);
            $st->execute([$subjectId,$difficulty]);
            $picked=array_merge($picked,array_column($st->fetchAll(),'id'));
        }
        if(count($picked)<$total){
            $need=$total-count($picked);
            $place=implode(',',array_fill(0,count($picked),'?'));
            $sql='SELECT id FROM questions WHERE subject_id=?';
            $params=[$subjectId];
            if($picked){$sql.=' AND id NOT IN('.$place.')';$params=array_merge($params,$picked);}
            $sql.=' ORDER BY RAND() LIMIT '.(int)$need;
            $st=$this->db->prepare($sql);$st->execute($params);$picked=array_merge($picked,array_column($st->fetchAll(),'id'));
        }
        return array_map('intval',array_slice($picked,0,$total));
    }
}
