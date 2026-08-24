<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Exam;
use App\Models\Attempt;

class DashboardController extends Controller
{
    public function index():void{
        Auth::requireLogin();
        $user=Auth::user();
        $exams=(new Exam)->published();
        $history=$user['role']==='student'?(new Attempt)->history((int)$user['id']):[];
        $this->view('dashboard',['title'=>'Dashboard','user'=>$user,'exams'=>$exams,'history'=>$history]);
    }
}
