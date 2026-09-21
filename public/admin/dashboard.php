<?php
require __DIR__ . '/../../app/bootstrap.php';

$user = Auth::user();

// Kiểm tra bảo mật: Nếu chưa đăng nhập hoặc không phải admin thì đá về trang login
if (!$user || $user['role'] !== 'admin') {
    redirect('/login.php');
}

$pdo = Database::pdo();

// Lấy thống kê tổng quan cho Admin
$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalExams = (int)$pdo->query("SELECT COUNT(*) FROM exams")->fetchColumn();
$totalAttempts = (int)$pdo->query("SELECT COUNT(*) FROM attempts")->fetchColumn();

$pageTitle = 'Trang quản trị Admin - Examify';
require __DIR__ . '/../_header.php';
?>

<section class="auth-card" style="max-width: 900px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);">
    <h1>Khu vực Quản trị hệ thống</h1>
    <p class="muted">Xin chào <strong><?= e($user['name']) ?></strong>, Bạn có toàn quyền quản lý hệ thống.</p>
    
    <div class="stats" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin: 25px 0;">
        <article class="stat" style="background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0;">
            <strong style="font-size: 24px; color: #2563eb; display: block;"><?= $totalUsers ?></strong>
            <span style="color: #64748b; font-size: 14px;">Tổng số tài khoản</span>
        </article>
        <article class="stat" style="background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0;">
            <strong style="font-size: 24px; color: #2563eb; display: block;"><?= $totalExams ?></strong>
            <span style="color: #64748b; font-size: 14px;">Tổng số đề thi</span>
        </article>
        <article class="stat" style="background: #f8fafc; padding: 20px; border-radius: 12px; text-align: center; border: 1px solid #e2e8f0;">
            <strong style="font-size: 24px; color: #2563eb; display: block;"><?= $totalAttempts ?></strong>
            <span style="color: #64748b; font-size: 14px;">Tổng lượt làm bài</span>
        </article>
    </div>

</section>

<?php require __DIR__ . '/../_footer.php'; ?>
