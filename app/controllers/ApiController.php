<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Attempt;

class ApiController extends Controller
{
    public function submit(int $examId):void
    {
        Auth::requireRole(['student']);
        $data=$this->input();
        $answers=$data['answers']??[];
        if(!is_array($answers))$this->json(['ok'=>false,'message'=>'answers phải là object.'],422);
        $attemptM=new Attempt();$attempt=$attemptM->findActive($examId,(int)Auth::user()['id']);
        if(!$attempt)$this->json(['ok'=>false,'message'=>'Không có lượt thi đang hoạt động.'],404);
        try{
            $result=$attemptM->saveAndSubmit((int)$attempt['id'],$answers);
            $this->json(['ok'=>true,'message'=>'Nộp bài thành công.','attempt_id'=>(int)$result['id'],'score'=>(float)$result['score'],'correct'=>(int)$result['correct_count'],'total'=>(int)$result['total_questions'],'redirect'=>url('/results/'.$result['id'])]);
        }catch(\Throwable $e){$this->json(['ok'=>false,'message'=>$e->getMessage()],500);}
    }
}
