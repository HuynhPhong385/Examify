<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Question;
use App\Models\Subject;

class QuestionController extends Controller
{
    public function index():void{
        Auth::requireRole(['admin','teacher']);
        $q=trim($_GET['q']??'');$subject=(int)($_GET['subject']??0)?:null;$difficulty=$_GET['difficulty']??null;
        $page=max(1,(int)($_GET['page']??1));$per=8;$m=new Question();
        $this->view('questions/index',['title'=>'Ngân hàng câu hỏi','items'=>$m->all($q,$subject,$difficulty,$per,($page-1)*$per),'subjects'=>(new Subject)->all('',1000,0),'q'=>$q,'subject'=>$subject,'difficulty'=>$difficulty,'page'=>$page,'pages'=>max(1,(int)ceil($m->count($q,$subject,$difficulty)/$per)),'csrf'=>$this->csrf()]);
    }
    public function create():void{Auth::requireRole(['admin','teacher']);$this->view('questions/form',['title'=>'Thêm câu hỏi','subjects'=>(new Subject)->all('',1000,0),'item'=>null,'csrf'=>$this->csrf()]);}
    public function store():void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        $d=$this->questionData();$d['created_by']=Auth::user()['id'];
        $d['image']=$this->uploadImage($_FILES['image']??null);
        (new Question)->create($d);flash('Đã thêm câu hỏi.');$this->redirect('/questions');
    }
    public function edit(int $id):void{Auth::requireRole(['admin','teacher']);$this->view('questions/form',['title'=>'Sửa câu hỏi','subjects'=>(new Subject)->all('',1000,0),'item'=>(new Question)->find($id),'csrf'=>$this->csrf()]);}
    public function update(int $id):void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        $old=(new Question)->find($id);$d=$this->questionData();$d['created_by']=$old['created_by'];$d['image']=$old['image'];
        $new=$this->uploadImage($_FILES['image']??null);if($new)$d['image']=$new;
        (new Question)->update($id,$d);flash('Đã cập nhật câu hỏi.');$this->redirect('/questions');
    }
    public function delete(int $id):void{Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);(new Question)->delete($id);flash('Đã xóa câu hỏi.');$this->redirect('/questions');}
    private function questionData():array{
        return [
            'subject_id'=>(int)($_POST['subject_id']??0),
            'content'=>trim($_POST['content']??''),
            'difficulty'=>$_POST['difficulty']??'medium',
            'option_a'=>trim($_POST['option_a']??''),'option_b'=>trim($_POST['option_b']??''),
            'option_c'=>trim($_POST['option_c']??''),'option_d'=>trim($_POST['option_d']??''),
            'correct_option'=>$_POST['correct_option']??'A'
        ];
    }
    private function uploadImage(?array $file):?string{
        if(!$file || ($file['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
        if($file['error']!==UPLOAD_ERR_OK || $file['size']>MAX_UPLOAD_SIZE)throw new \RuntimeException('Ảnh không hợp lệ hoặc vượt quá 2MB.');
        $mime=(new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
        if(!isset($allowed[$mime]))throw new \RuntimeException('Chỉ nhận JPG, PNG, WEBP.');
        if(!is_dir(UPLOAD_DIR))mkdir(UPLOAD_DIR,0755,true);
        $name=bin2hex(random_bytes(12)).'.'.$allowed[$mime];
        move_uploaded_file($file['tmp_name'],UPLOAD_DIR.'/'.$name);
        return $name;
    }
}
