<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attempt;

class AttemptController extends Controller
{
    public function history():void{
        Auth::requireLogin();
        $this->view('attempts/history',['title'=>'Lịch sử bài làm','items'=>(new Attempt)->history((int)Auth::user()['id'])]);
    }
}
