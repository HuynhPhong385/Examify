<?php
require __DIR__ . '/../app/bootstrap.php';

if (Auth::user()) {
    redirect('/dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $error = 'Phiên đăng nhập không hợp lệ. Hãy thử lại.';
    } elseif (Auth::attempt($email, $password)) {
        redirect('/dashboard.php');
    } else {
        $error = 'Email hoặc mật khẩu không đúng.';
    }
}

$pageTitle = 'Đăng nhập - Examify';
require __DIR__ . '/_header.php';
?>
<section class="auth-card">
    <h1>Đăng nhập Examify</h1>
    <p class="muted">Hệ thống quản lý và thi thử trắc nghiệm trực tuyến.</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" class="stack">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label>Email
            <input type="email" name="email" required autocomplete="email">
        </label>
        <label>Mật khẩu
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn primary" type="submit">Đăng nhập</button>
    </form>
    <div class="demo-box">
        <strong>Tài khoản demo</strong>
        <div>Admin: admin@examify.local / Admin@123</div>
        <div>Giáo viên: teacher@examify.local / Teacher@123</div>
        <div>Học sinh: student@examify.local / Student@123</div>
    </div>
</section>
<?php require __DIR__ . '/_footer.php'; ?>
