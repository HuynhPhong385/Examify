<?php
require __DIR__ . '/../app/bootstrap.php';
$user = require_auth_page();
$id = positive_int($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    exit('Thiếu mã lượt thi.');
}

$pdo = Database::pdo();
$stmt = $pdo->prepare(
    "SELECT a.*, e.title, e.passing_score, u.name AS student_name
     FROM attempts a
     JOIN exams e ON e.id=a.exam_id
     JOIN users u ON u.id=a.user_id
     WHERE a.id=? LIMIT 1"
);
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) {
    http_response_code(404);
    exit('Không tìm thấy kết quả.');
}
$allowed = (int)$a['user_id'] === (int)$user['id'];
if (!$allowed && in_array($user['role'], ['admin','teacher'], true)) {
    $stmt = $pdo->prepare('SELECT created_by FROM exams WHERE id=?');
    $stmt->execute([$a['exam_id']]);
    $owner = (int)$stmt->fetchColumn();
    $allowed = $user['role'] === 'admin' || $owner === (int)$user['id'];
}
if (!$allowed) {
    http_response_code(403);
    exit('Bạn không có quyền xem kết quả này.');
}

$pageTitle = 'Kết quả - Examify';
require __DIR__ . '/_header.php';
?>
<section class="result-card <?= $a['passed'] ? 'pass' : 'fail' ?>">
    <span class="eyebrow">KẾT QUẢ</span>
    <h1><?= e($a['title']) ?></h1>
    <div class="score"><?= number_format((float)$a['percentage'], 2) ?>%</div>
    <p><?= $a['passed'] ? 'Đạt' : 'Chưa đạt' ?> · <?= number_format((float)$a['score'], 2) ?>/<?= number_format((float)$a['total_points'], 2) ?> điểm</p>
    <p class="muted">Thí sinh: <?= e($a['student_name']) ?> · Ngưỡng đạt: <?= number_format((float)$a['passing_score'], 2) ?>%</p>
    <a class="btn primary" href="<?= e(base_url('/exams.php')) ?>">Làm đề khác</a>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
