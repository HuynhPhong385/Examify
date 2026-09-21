<?php
require __DIR__ . '/../../app/bootstrap.php';

$user = Auth::user();
if (!$user || $user['role'] !== 'admin') {
    redirect('/login.php');
}

$pdo = Database::pdo();
$availableRoles = ['admin', 'teacher', 'student'];
$error = '';
$notice = $_SESSION['admin_user_notice'] ?? '';
unset($_SESSION['admin_user_notice']);
$editUser = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Phiên làm việc đã hết hạn. Vui lòng tải lại trang.';
    } else {
        $action = (string)($_POST['action'] ?? '');
        $userId = positive_int($_POST['user_id'] ?? 0);

        if ($action === 'delete') {
            if (!$userId) {
                $error = 'Không tìm thấy tài khoản cần xóa.';
            } elseif ($userId === (int)$user['id']) {
                $error = 'Bạn không thể xóa tài khoản đang đăng nhập.';
            } else {
                $target = $pdo->prepare('SELECT role FROM users WHERE id=?');
                $target->execute([$userId]);
                $target = $target->fetch();
                if (!$target) {
                    $error = 'Tài khoản không tồn tại.';
                } elseif ($target['role'] === 'admin' && (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn() <= 1) {
                    $error = 'Hệ thống phải luôn có ít nhất một quản trị viên.';
                } else {
                    try {
                        $stmt = $pdo->prepare('DELETE FROM users WHERE id=?');
                        $stmt->execute([$userId]);
                        $_SESSION['admin_user_notice'] = 'Đã xóa tài khoản.';
                        redirect('/admin/quanly_user.php');
                    } catch (PDOException) {
                        $error = 'Không thể xóa tài khoản này vì dữ liệu liên quan vẫn đang được sử dụng.';
                    }
                }
            }
        } elseif ($action === 'save') {
            $name = trim((string)($_POST['name'] ?? ''));
            $email = mb_strtolower(trim((string)($_POST['email'] ?? '')));
            $role = (string)($_POST['role'] ?? 'student');
            $password = (string)($_POST['password'] ?? '');

            if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !in_array($role, $availableRoles, true)) {
                $error = 'Vui lòng nhập họ tên, email hợp lệ và vai trò.';
            } elseif (!$userId && strlen($password) < 6) {
                $error = 'Mật khẩu cho tài khoản mới cần ít nhất 6 ký tự.';
            } elseif ($userId && $password !== '' && strlen($password) < 6) {
                $error = 'Mật khẩu mới cần ít nhất 6 ký tự.';
            } elseif ($userId === (int)$user['id'] && $role !== 'admin') {
                $error = 'Bạn không thể tự gỡ quyền quản trị của mình.';
            } else {
                $duplicate = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
                $duplicate->execute([$email, $userId]);
                if ($duplicate->fetchColumn()) {
                    $error = 'Email này đã được sử dụng.';
                } else {
                    try {
                        if ($userId) {
                            if ($password === '') {
                                $stmt = $pdo->prepare('UPDATE users SET name=?,email=?,role=? WHERE id=?');
                                $stmt->execute([$name, $email, $role, $userId]);
                            } else {
                                $stmt = $pdo->prepare('UPDATE users SET name=?,email=?,role=?,password=? WHERE id=?');
                                $stmt->execute([$name, $email, $role, password_hash($password, PASSWORD_DEFAULT), $userId]);
                            }
                            $_SESSION['admin_user_notice'] = 'Đã cập nhật tài khoản.';
                        } else {
                            $stmt = $pdo->prepare('INSERT INTO users (name,email,password,role) VALUES (?,?,?,?)');
                            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $role]);
                            $_SESSION['admin_user_notice'] = 'Đã thêm tài khoản.';
                        }
                        redirect('/admin/quanly_user.php');
                    } catch (PDOException) {
                        $error = 'Không thể lưu tài khoản. Vui lòng thử lại.';
                    }
                }
            }
            $editUser = ['id' => $userId, 'name' => $name, 'email' => $email, 'role' => $role];
        }
    }
}

if (!$editUser && isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT id,name,email,role FROM users WHERE id=?');
    $stmt->execute([positive_int($_GET['edit'])]);
    $editUser = $stmt->fetch() ?: null;
    if (!$editUser) {
        $error = 'Không tìm thấy tài khoản cần sửa.';
    }
}

$users = $pdo->query('SELECT id,name,email,role FROM users ORDER BY id DESC')->fetchAll();
$pageTitle = 'Quản lý người dùng - Admin';
require __DIR__ . '/../_header.php';
?>

<section class="panel" style="max-width: 1000px; margin: 40px auto; background: #fff; padding: 30px; border-radius: 16px; box-shadow: 0 4px 16px rgba(15, 23, 42, 0.06);">
    <h1>Quản lý tài khoản</h1>
    <p class="muted">Thêm, chỉnh sửa, phân quyền hoặc xóa tài khoản trong hệ thống.</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($notice): ?><div class="alert success"><?= e($notice) ?></div><?php endif; ?>

    <form method="post" class="form-grid" style="margin: 24px 0;">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="user_id" value="<?= (int)($editUser['id'] ?? 0) ?>">
        <label>Họ tên<input name="name" maxlength="120" required value="<?= e($editUser['name'] ?? '') ?>"></label>
        <label>Email<input name="email" type="email" maxlength="190" required value="<?= e($editUser['email'] ?? '') ?>"></label>
        <label>Vai trò<select name="role"><?php foreach ($availableRoles as $availableRole): ?><option value="<?= e($availableRole) ?>" <?= ($editUser['role'] ?? 'student') === $availableRole ? 'selected' : '' ?>><?= e(ucfirst($availableRole)) ?></option><?php endforeach; ?></select></label>
        <label>Mật khẩu <?= $editUser ? '(để trống nếu không đổi)' : '' ?><input name="password" type="password" <?= $editUser ? '' : 'required' ?> minlength="6" autocomplete="new-password"></label>
        <div class="span-2 actions"><button class="btn primary" type="submit"><?= $editUser ? 'Lưu thay đổi' : 'Thêm tài khoản' ?></button><?php if ($editUser): ?> <a class="btn" href="<?= e(base_url('/admin/quanly_user.php')) ?>">Hủy sửa</a><?php endif; ?></div>
    </form>

    <div style="overflow-x: auto;"><table style="width: 100%; border-collapse: collapse; margin-top: 20px;"><thead><tr style="background: #f8fafc; text-align: left; border-bottom: 2px solid #e2e8f0;"><th style="padding: 12px;">ID</th><th style="padding: 12px;">Họ tên</th><th style="padding: 12px;">Email</th><th style="padding: 12px;">Vai trò</th><th style="padding: 12px;">Thao tác</th></tr></thead><tbody>
        <?php foreach ($users as $item): ?><tr style="border-bottom: 1px solid #e2e8f0;"><td style="padding: 12px;"><?= (int)$item['id'] ?></td><td style="padding: 12px;"><?= e($item['name']) ?></td><td style="padding: 12px;"><?= e($item['email']) ?></td><td style="padding: 12px;"><span style="background: #eef2ff; color: #4338ca; padding: 4px 10px; border-radius: 999px; font-size: 12px; font-weight: 600;"><?= e($item['role']) ?></span></td><td style="padding: 12px; white-space: nowrap;"><a class="btn" href="<?= e(base_url('/admin/quanly_user.php?edit=' . $item['id'])) ?>">Sửa</a><?php if ((int)$item['id'] !== (int)$user['id']): ?><form method="post" style="display: inline;" onsubmit="return confirm('Xóa tài khoản này?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?= (int)$item['id'] ?>"><button class="btn danger" type="submit">Xóa</button></form><?php endif; ?></td></tr><?php endforeach; ?>
    </tbody></table></div>
</section>

<?php require __DIR__ . '/../_footer.php'; ?>
