<?php
require __DIR__ . '/../app/bootstrap.php';
require_auth_page();

$examId = positive_int($_GET['id'] ?? 0);
if (!$examId) {
    http_response_code(400);
    exit('Thiếu mã đề.');
}
$pdo = Database::pdo();
$stmt = $pdo->prepare('SELECT id,title,description,duration_minutes,passing_score,is_published FROM exams WHERE id=? LIMIT 1');
$stmt->execute([$examId]);
$exam = $stmt->fetch();
if (!$exam) {
    http_response_code(404);
    exit('Không tìm thấy đề thi.');
}

$pageTitle = $exam['title'] . ' - Examify';
require __DIR__ . '/_header.php';
?>
<section class="exam-shell" data-exam-id="<?= $examId ?>" data-duration="<?= (int)$exam['duration_minutes'] ?>">
    <div class="exam-top">
        <div>
            <span class="eyebrow">ĐANG LÀM BÀI</span>
            <h1><?= e($exam['title']) ?></h1>
            <p><?= e($exam['description']) ?></p>
        </div>
        <div class="timer-box">
            <small>Thời gian còn lại</small>
            <strong id="timer">--:--</strong>
        </div>
    </div>

    <div id="saveStatus" class="save-status">Đang khởi tạo lượt thi...</div>
    <form id="examForm">
        <div id="questionList" class="question-list"></div>
        <button id="submitExam" class="btn primary large" type="submit" disabled>Nộp bài & chấm điểm</button>
    </form>
</section>
<?php
$extraScripts = ['/assets/js/exam.js'];
require __DIR__ . '/_footer.php';
?>
