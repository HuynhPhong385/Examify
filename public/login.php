<?php
require __DIR__ . '/../app/bootstrap.php';

if (Auth::user()) {
    $user = Auth::user();
    if (!isset($_SESSION['redirect_after_login'])) {
        if ($user['role'] === 'admin') {
            $target = BASE_URL . '/admin/dashboard.php';
        } elseif ($user['role'] === 'teacher') {
            $target = BASE_URL . '/teacher/dashboard.php'; // Hoặc /teacher/dashboard.php nếu có
        } else {
            $target = BASE_URL . '/dashboard.php';
        }
    } else {
        $target = $_SESSION['redirect_after_login'];
        unset($_SESSION['redirect_after_login']);
    }
    redirect($target);
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // Kiểm tra CSRF token an toàn
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $error = 'Phiên làm việc đã hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.';
    } elseif (empty($email) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ email và mật khẩu.';
    } elseif (Auth::attempt($email, $password)) {
        $user = Auth::user();
        if (!isset($_SESSION['redirect_after_login'])) {
            if ($user['role'] === 'admin') {
                $target = BASE_URL . '/admin/dashboard.php';
            } elseif ($user['role'] === 'teacher') {
                $target = BASE_URL . '/teacher/dashboard.php';
            } else {
                $target = BASE_URL . '/student/dashboard.php';
            }
        } else {
            $target = $_SESSION['redirect_after_login'];
            unset($_SESSION['redirect_after_login']);
        }
        redirect($target);
    } else {
        $error = 'Email hoặc mật khẩu không chính xác.';
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
            <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required autocomplete="email">
        </label>
        <label>Mật khẩu
            <input type="password" name="password" required autocomplete="current-password">
        </label>
        <button class="btn primary" type="submit">Đăng nhập</button>
    </form>
</section>
<?php require __DIR__ . '/_footer.php'; ?>