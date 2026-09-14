<?php
require __DIR__ . '/../app/bootstrap.php';

$$user = Auth::user();
$pdo = Database::pdo();

if ($user && in_array($user['role'], ['admin','teacher'], true)) {
    if ($user['role'] === 'admin') {
        $examCount = (int)$pdo->query('SELECT COUNT(*) FROM exams')->fetchColumn();
        $attemptCount = (int)$pdo->query("SELECT COUNT(*) FROM attempts WHERE status='submitted'")->fetchColumn();
    } else {
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM exams WHERE created_by=?');
        $stmt->execute([$user['id']]);
        $examCount = (int)$stmt->fetchColumn();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM attempts a JOIN exams e ON e.id=a.exam_id WHERE e.created_by=? AND a.status='submitted'");
        $stmt->execute([$user['id']]);
        $attemptCount = (int)$stmt->fetchColumn();
    }
} elseif ($user) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attempts WHERE user_id=? AND status='submitted'");
    $stmt->execute([$user['id']]);
    $attemptCount = (int)$stmt->fetchColumn();
    $examCount = (int)$pdo->query('SELECT COUNT(*) FROM exams WHERE is_published=1')->fetchColumn();
} else {
   
    $examCount = (int)$pdo->query('SELECT COUNT(*) FROM exams WHERE is_published=1')->fetchColumn();
    $attemptCount = 0;
}
$pageTitle = 'Bảng điều khiển - Examify';
require __DIR__ . '/_header.php';

?>
<?php if ($user):?>
<section class="hero">
    <div>
        <span class="eyebrow">EXAMIFY</span>
        <h1>Thi trắc nghiệm nhanh, chấm điểm tự động.</h1>
        <p>REST API + AJAX autosave + Live Search + PHP/MySQL.</p>
    </div>
    <a class="btn primary" href="<?= e(base_url('/exams.php')) ?>">Vào danh sách đề</a>
</section>

<section class="stats">
    <article class="stat"><strong><?= $examCount ?></strong><span>Đề đang mở</span></article>
    <article class="stat"><strong><?= $attemptCount ?></strong><span>Lượt đã nộp</span></article>
    <article class="stat"><strong>AJAX</strong><span>Tự lưu đáp án</span></article>
</section>

<?php if ($user && in_array($user['role'], ['admin','teacher'], true)): ?>
<section class="panel">
    <h2>Dành cho giáo viên / quản trị</h2>
    <p>Tạo đề, thêm câu hỏi và đáp án, xuất bản đề rồi theo dõi lượt thi.</p>
    <a class="btn" href="<?= e(base_url('/teacher/exams.php')) ?>">Quản lý đề thi</a>
</section>
<?php endif; ?>

<?php if (!$user): ?>
<section class="panel">
    <h2>Bạn chưa đăng nhập</h2>
    <p>Bạn xem được danh sách đề ngay bây giờ. Khi bấm "Làm bài", hệ thống sẽ yêu cầu đăng nhập.</p>
    <a class="btn primary" href="<?= e(base_url('/login.php')) ?>">Đăng nhập</a>
</section>
<?php endif; ?>