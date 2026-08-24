<?php
declare(strict_types=1);

require __DIR__.'/../app/config/config.php';
require __DIR__.'/../app/core/helpers.php';

spl_autoload_register(function(string $class){
    $prefix='App\\';
    if(!str_starts_with($class,$prefix))return;
    $relative=substr($class,strlen($prefix));
    $file=__DIR__.'/../app/'.str_replace('\\','/',$relative).'.php';
    if(is_file($file))require $file;
});

use App\Core\Router;
use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\SubjectController;
use App\Controllers\QuestionController;
use App\Controllers\ExamController;
use App\Controllers\AttemptController;
use App\Controllers\ApiController;

$router=new Router();

$router->get('/',[DashboardController::class,'index']);
$router->get('/dashboard',[DashboardController::class,'index']);

$router->get('/login',[AuthController::class,'showLogin']);
$router->post('/login',[AuthController::class,'login']);
$router->get('/register',[AuthController::class,'showRegister']);
$router->post('/register',[AuthController::class,'register']);
$router->get('/logout',[AuthController::class,'logout']);

$router->get('/subjects',[SubjectController::class,'index']);
$router->post('/subjects',[SubjectController::class,'store']);
$router->post('/subjects/{id}',[SubjectController::class,'update']);
$router->post('/subjects/{id}/delete',[SubjectController::class,'delete']);

$router->get('/questions',[QuestionController::class,'index']);
$router->get('/questions/create',[QuestionController::class,'create']);
$router->post('/questions',[QuestionController::class,'store']);
$router->get('/questions/{id}/edit',[QuestionController::class,'edit']);
$router->post('/questions/{id}',[QuestionController::class,'update']);
$router->post('/questions/{id}/delete',[QuestionController::class,'delete']);

$router->get('/exams',[ExamController::class,'index']);
$router->get('/exams/create',[ExamController::class,'create']);
$router->post('/exams',[ExamController::class,'store']);
$router->get('/exams/{id}/edit',[ExamController::class,'edit']);
$router->post('/exams/{id}',[ExamController::class,'update']);
$router->post('/exams/{id}/delete',[ExamController::class,'delete']);
$router->get('/exams/{id}/take',[ExamController::class,'take']);
$router->get('/exams/{id}/stats',[ExamController::class,'stats']);

$router->get('/history',[AttemptController::class,'history']);
$router->get('/results/{id}',[ExamController::class,'result']);

//$router->post('/api/exams/{id}/submit',[ApiController::class,'submit']);
$router->post('/exams/{id}/submit', [ExamController::class, 'submit']);
try {
    $router->dispatch($_SERVER['REQUEST_METHOD'],$_SERVER['REQUEST_URI']);
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>500 - Server Error</h1><pre>'.e($e->getMessage()).'</pre>';
}
