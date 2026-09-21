<?php
require __DIR__ . '/../app/bootstrap.php';

$user = Auth::user();

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

$sampleExams = $pdo->query(
    "SELECT id, title, description, duration_minutes
     FROM exams
     WHERE is_published = 1
     ORDER BY id DESC
     LIMIT 3"
)->fetchAll();

$pageTitle = 'Bảng điều khiển - Examify';
require __DIR__ . '/_header.php';
?>

<section class="hero">
    <div>
        <span class="eyebrow">EXAMIFY</span>
        <h1>Thi trắc nghiệm nhanh, chấm điểm tự động.</h1>
    </div>
    <a class="btn primary" href="<?= e(base_url('/exams.php')) ?>">Vào danh sách đề</a>
</section>

<section class="stats">
    <article class="stat"><strong><?= $examCount ?></strong><span>Đề đang mở</span></article>
    <article class="stat"><strong><?= $attemptCount ?></strong><span>Lượt đã nộp</span></article>
    <article class="stat"><strong>Hơn 2000 giảng viên tin cậy và sử dụng</strong></article>
</section>

<?php if ($sampleExams): ?>
<section class="panel featured-exams">
    <div class="panel-heading">
        <h2>Đề thi nổi bật</h2>
        <p class="muted">Xem trước một vài đề đang mở. Bấm vào để bắt đầu làm bài ngay.</p>
    </div>

    <div class="featured-grid">
        <?php foreach ($sampleExams as $ex): ?>
            <article class="featured-card">
                <div class="featured-card-top">
                    <span class="featured-badge">Đề #<?= (int)$ex['id'] ?></span>
                    <span class="featured-time">⏱ <?= (int)$ex['duration_minutes'] ?> phút</span>
                </div>
                <h3><?= e($ex['title']) ?></h3>
                <p class="featured-desc">
                    <?= e($ex['description'] !== '' ? $ex['description'] : 'Chưa có mô tả cho đề thi này.') ?>
                </p>
                <a class="featured-btn" href="<?= e(base_url('/exam.php?id=' . $ex['id'])) ?>">
                    Làm bài ngay <span>→</span>
                </a>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="featured-footer">
        <a class="btn primary" href="<?= e(base_url('/exams.php')) ?>">Xem tất cả đề thi</a>
    </div>
</section>

<style>
.featured-exams .panel-heading h2 {
    margin: 0 0 4px;
    font-size: 22px;
}
.featured-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 20px;
    margin: 20px 0;
}
.featured-card {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    border: 1px solid #eef0f4;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);
    display: flex;
    flex-direction: column;
    transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
}
.featured-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(30, 64, 175, 0.12);
    border-color: #c7d2fe;
}
.featured-card-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}
.featured-badge {
    background: #eef2ff;
    color: #4338ca;
    font-size: 12px;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 999px;
}
.featured-time {
    font-size: 13px;
    color: #6b7280;
}
.featured-card h3 {
    font-size: 18px;
    margin: 0 0 8px;
    color: #111827;
}
.featured-desc {
    color: #6b7280;
    font-size: 14px;
    line-height: 1.5;
    flex-grow: 1;
    margin-bottom: 16px;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.featured-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-weight: 600;
    color: #2563eb;
    text-decoration: none;
    transition: gap .15s ease;
}
.featured-btn:hover { gap: 10px; }
.featured-footer { text-align: center; margin-top: 8px; }
</style>
<?php endif; ?>

<?php if ($user): ?>
    <?php if ($user['role'] === 'admin'): ?>
        <section class="panel">
            <h2>Quản trị hệ thống (Admin)</h2>
            <p>Quản lý toàn bộ tài khoản người dùng, cấu hình và danh sách đề thi hệ thống.</p>
            <a class="btn primary" href="<?= e(base_url('/admin/dashboard.php')) ?>">Trang quản trị Admin</a>
        </section>
    <?php elseif ($user['role'] === 'teacher'): ?>
        <section class="panel">
            <h2>Dành cho giáo viên</h2>
            <p>Tạo đề, thêm câu hỏi và đáp án, xuất bản đề rồi theo dõi lượt thi.</p>
            <a class="btn primary" href="<?= e(base_url('/teacher/exams.php')) ?>">Quản lý đề thi</a>
        </section>
    <?php endif; ?>
<?php endif; ?>

<script src='assets/js/jquery.min.js'></script>
<script src='assets/js/app.js'></script>
<?php require __DIR__ . '/_footer.php'; ?>
