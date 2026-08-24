<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Subject;

class SubjectController extends Controller
{
    public function index():void{
        Auth::requireRole(['admin','teacher']);
        $q=trim($_GET['q']??'');$page=max(1,(int)($_GET['page']??1));$per=8;$m=new Subject();
        $this->view('subjects/index',['title'=>'Môn học','items'=>$m->all($q,$per,($page-1)*$per),'q'=>$q,'page'=>$page,'pages'=>max(1,(int)ceil($m->count($q)/$per)),'csrf'=>$this->csrf()]);
    }
    public function store():void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        $name=trim($_POST['name']??'');$desc=trim($_POST['description']??'');
        if($name===''){flash('Tên môn học không được để trống.','danger');$this->redirect('/subjects');}
        (new Subject)->create($name,$desc);flash('Đã thêm môn học.');$this->redirect('/subjects');
    }
    public function update(int $id):void{
        Auth::requireRole(['admin','teacher']);$this->verifyCsrf($_POST['_csrf']??null);
        (new Subject)->update($id,trim($_POST['name']??''),trim($_POST['description']??''));flash('Đã cập nhật môn học.');$this->redirect('/subjects');
    }
    public function delete(int $id):void{
        Auth::requireRole(['admin']);$this->verifyCsrf($_POST['_csrf']??null);(new Subject)->delete($id);flash('Đã xóa môn học.');$this->redirect('/subjects');
    }
}
