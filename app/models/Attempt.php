<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

class Attempt
{
    private PDO $db;
    public function __construct(){ $this->db=Database::connection(); }

    public function findActive(int $examId,int $userId):?array{
        $st=$this->db->prepare("SELECT * FROM exam_attempts WHERE exam_id=? AND user_id=? AND status='in_progress' ORDER BY id DESC LIMIT 1");
        $st->execute([$examId,$userId]);return $st->fetch()?:null;
    }
    public function find(int $id):?array{
        $st=$this->db->prepare('SELECT a.*,e.title exam_title,e.duration_minutes,u.name user_name FROM exam_attempts a JOIN exams e ON e.id=a.exam_id JOIN users u ON u.id=a.user_id WHERE a.id=?');
        $st->execute([$id]);return $st->fetch()?:null;
    }
    public function create(int $examId,int $userId,int $total):int{
        $st=$this->db->prepare("INSERT INTO exam_attempts(exam_id,user_id,started_at,total_questions,status) VALUES(?,?,NOW(),?,'in_progress')");
        $st->execute([$examId,$userId,$total]);return (int)$this->db->lastInsertId();
    }
    public function saveAndSubmit(int $attemptId,array $answers):array{
        $attempt=$this->find($attemptId);
        if(!$attempt)throw new \RuntimeException('Không tìm thấy lượt thi.');
        if($attempt['status']==='submitted') return $attempt;

        $questions=(new Exam)->questions((int)$attempt['exam_id']);
        $correct=0;
        $this->db->beginTransaction();
        try{
            $del=$this->db->prepare('DELETE FROM exam_attempt_answers WHERE attempt_id=?');$del->execute([$attemptId]);
            $ins=$this->db->prepare('INSERT INTO exam_attempt_answers(attempt_id,question_id,selected_option,is_correct) VALUES(?,?,?,?)');
            foreach($questions as $q){
                $selected=$answers[(string)$q['id']] ?? $answers[(int)$q['id']] ?? null;
                if(!in_array($selected,['A','B','C','D'],true))$selected=null;
                $is=$selected!==null && $selected===$q['correct_option'];
                if($is)$correct++;
                $ins->execute([$attemptId,$q['id'],$selected,$is?1:0]);
            }
            $total=count($questions);
            $score=$total?round($correct/$total*10,2):0;
            $up=$this->db->prepare("UPDATE exam_attempts SET submitted_at=NOW(),score=?,correct_count=?,total_questions=?,status='submitted' WHERE id=?");
            $up->execute([$score,$correct,$total,$attemptId]);
            $this->db->commit();
        }catch(\Throwable $e){$this->db->rollBack();throw $e;}
        return $this->find($attemptId);
    }
    public function history(int $userId):array{
        $st=$this->db->prepare('SELECT a.*,e.title exam_title,s.name subject_name FROM exam_attempts a JOIN exams e ON e.id=a.exam_id JOIN subjects s ON s.id=e.subject_id WHERE a.user_id=? ORDER BY a.id DESC');
        $st->execute([$userId]);return $st->fetchAll();
    }
    public function details(int $attemptId):array{
        $st=$this->db->prepare('SELECT q.*,eaa.selected_option,eaa.is_correct FROM exam_attempt_answers eaa JOIN questions q ON q.id=eaa.question_id WHERE eaa.attempt_id=? ORDER BY q.id');
        $st->execute([$attemptId]);return $st->fetchAll();
    }
    public function stats(int $examId):array{
        $st=$this->db->prepare("SELECT COUNT(*) attempts,COALESCE(AVG(score),0) avg_score,COALESCE(MAX(score),0) max_score,COALESCE(MIN(score),0) min_score FROM exam_attempts WHERE exam_id=? AND status='submitted'");
        $st->execute([$examId]);return $st->fetch();
    }
}
