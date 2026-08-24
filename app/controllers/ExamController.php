<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Exam;
use App\Models\Subject;
use App\Models\Question;
use App\Models\Attempt;

class ExamController extends Controller
{
    public function index():void{
        Auth::requireLogin();
        $q=trim($_GET['q']??'');$page=max(1,(int)($_GET['page']??1));$per=8;$m=new Exam();
        $this->view('exams/index',['title'=>'Đề thi','items'=>$m->all($q,$per,($page-1)*$per),'q'=>$q,'page'=>$page,'pages'=>max(1,(int)ceil($m->count($q)/$per)),'csrf'=>$this->csrf()]);
    }
    public function create():void{Auth::requireRole(['admin','teacher']);$this->view('exams/form',['title'=>'Tạo đề thi','subjects'=>(new Subject)->all('',1000,0),'item'=>null,'csrf'=>$this->csrf()]);}
    public function store():void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        $d=$this->examData();$d['created_by']=Auth::user()['id'];$m=new Exam();$id=$m->create($d);
        $questions=$this->chooseQuestions($d);
        $m->setQuestions($id,$questions);
        flash('Đã tạo đề thi với '.count($questions).' câu hỏi.');$this->redirect('/exams');
    }
    public function edit(int $id):void{
        Auth::requireRole(['admin','teacher']);$m=new Exam();
        $this->view('exams/form',['title'=>'Sửa đề thi','subjects'=>(new Subject)->all('',1000,0),'item'=>$m->find($id),'selected'=>array_column($m->questions($id),'id'),'csrf'=>$this->csrf()]);
    }
    public function update(int $id):void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        $d=$this->examData();$m=new Exam();$m->update($id,$d);
        $ids=array_values(array_filter(array_map('intval',$_POST['question_ids']??[])));
        if($ids)$m->setQuestions($id,array_slice($ids,0,(int)$d['total_questions']));
        else $m->setQuestions($id,$this->chooseQuestions($d));
        flash('Đã cập nhật đề thi.');$this->redirect('/exams');
    }
    public function delete(int $id):void{Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);(new Exam)->delete($id);flash('Đã xóa đề thi.');$this->redirect('/exams');}

    public function take($id):void{
        $id = (int) $id;
        Auth::requireRole(['student']);$m=new Exam();$exam=$m->find($id);
        if(!$exam || $exam['status']!=='published'){http_response_code(404);exit('Đề thi không tồn tại.');}
        $attemptM=new Attempt();$attempt=$attemptM->findActive($id,(int)Auth::user()['id']);
        if(!$attempt)$attempt=$attemptM->create($id,(int)Auth::user()['id'],count($m->questions($id)));
        $this->view('exams/take',['title'=>'Làm bài: '.$exam['title'],'exam'=>$exam,'questions'=>$m->questions($id),'attempt'=>$attempt,'csrf'=>$this->csrf()]);
    }
    public function result(int $attemptId):void{
        Auth::requireLogin();$attempt=(new Attempt)->find($attemptId);
        if(!$attempt || ((int)$attempt['user_id']!==(int)Auth::user()['id'] && !in_array(Auth::user()['role'],['admin','teacher'],true))){http_response_code(403);exit('Không có quyền.');}
        $this->view('exams/result',['title'=>'Kết quả bài thi','attempt'=>$attempt,'details'=>(new Attempt)->details($attemptId)]);
    }
    public function stats(int $id):void{
        Auth::requireRole(['admin','teacher']);$m=new Exam();$this->view('exams/stats',['title'=>'Thống kê điểm','exam'=>$m->find($id),'stats'=>(new Attempt)->stats($id)]);
    }
    private function examData():array{
        return ['subject_id'=>(int)($_POST['subject_id']??0),'title'=>trim($_POST['title']??''),'description'=>trim($_POST['description']??''),'duration_minutes'=>max(1,(int)($_POST['duration_minutes']??30)),'total_questions'=>max(1,(int)($_POST['total_questions']??10)),'status'=>in_array($_POST['status']??'', ['draft','published'],true)?$_POST['status']:'draft'];
    }
    private function chooseQuestions(array $d):array{
        $counts=['easy'=>max(0,(int)($_POST['easy_count']??0)),'medium'=>max(0,(int)($_POST['medium_count']??0)),'hard'=>max(0,(int)($_POST['hard_count']??0))];
        return (new Exam)->randomQuestions((int)$d['subject_id'],(int)$d['total_questions'],$counts);
    }
    public function submit($id): void {
        $id = (int) $id;
        Auth::requireRole(['student']);
        $this->verifyCsrf($_POST['_csrf'] ?? null);

        $answers = $_POST['answers'] ?? []; // Lấy mảng đáp án học sinh chọn

        $m = new Exam();
        $exam = $m->find($id);
        if (!$exam || $exam['status'] !== 'published') {
            http_response_code(404);
            exit('Đề thi không tồn tại.');
        }

        $attemptM = new Attempt();
        $attempt = $attemptM->findActive($id, (int)Auth::user()['id']);
        
        if (!$attempt) {
            exit('Không tìm thấy phiên làm bài hợp lệ.');
        }

        // Chấm điểm
        $score = 0;
        $questions = $m->questions($id);
        $totalQuestions = count($questions);

        foreach ($questions as $q) {
            $qId = $q['id'];
            $studentAnswer = $answers[$qId] ?? null;
            if ($studentAnswer && isset($q['correct_answer']) && $studentAnswer === $q['correct_answer']) {
                $score += (10 / max(1, $totalQuestions));
            }
        }

        // Lưu kết quả hoàn thành
        $attemptM->complete($attempt['id'], round($score, 2));

        // Chuyển hướng sang trang kết quả
        $this->redirect('/results/' . $attempt['id']);
    }
}
