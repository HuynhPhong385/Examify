<?php

$user = Auth::user();

// Vai trò mặc định
if ($user){
$role = $user['role'];
}
$roles = [
    'admin' => [
        'name' => 'Quản trị viên',
        'username' => 'admin'
    ],
    'teacher' => [
        'name' => 'Giáo viên Demo',
        'username' => 'teacher'
    ],
    'student' => [
        'name' => 'Học sinh Demo',
        'username' => 'student'
    ]
];
if (isset($role)){
$currentRole = $roles[$role] ?? $roles['teacher'];
}
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="app-base-url" content="<?= e(base_url()) ?>">
    <title><?= e($pageTitle ?? 'Examify') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(base_url('/assets/css/style.css')) ?>">
</head>

<body>
    <?php if ($user):?>
<header class="topbar">
    <a class="brand" href="<?= e(base_url('/dashboard.php')) ?>">Examify</a>
    <button type="button" class="nav-toggle" id="navToggle" aria-label="Mở menu" aria-expanded="false" aria-controls="topbarNav">
        <span></span><span></span><span></span>
    </button>
    <nav id="topbarNav">
        <?php if ($user): ?>
            <a href="<?= e(base_url('/exams.php')) ?>">Đề thi</a>
            <?php if (in_array($user['role'], ['admin','teacher'], true)): ?>
                <a href="<?= e(base_url('/teacher/exams.php')) ?>">Quản lý đề</a>
            <?php endif; ?>
<div class="role-selector" id="roleSelector">

    <!-- Button chính -->
    <button
        type="button"
        class="role-button user-chip"
        id="roleButton"
    >

        <span class="role-button-content">

            <span
                class="role-name"
                id="currentRoleName"
            >
                <?= htmlspecialchars($currentRole['name']) ?>
            </span>

            <span class="dot">·</span>

            <span
                class="role-username"
                id="currentRoleUsername"
            >
                <?= htmlspecialchars($currentRole['username']) ?>
            </span>

        </span>

        <span class="arrow">▼</span>

    </button>


    <!-- Dropdown -->
    <div class="role-dropdown">

        <!-- Admin -->
        <button
            type="button"
            class="role-item <?= $currentRole['username'] === 'admin' ? 'active' : '' ?>"
            data-role="admin"
            data-name="Quản trị viên"
            data-username="admin"
        >

            <span class="role-info">

                <span class="role-title">
                    Quản trị viên
                </span>

                <span class="separator">·</span>

                <span class="username">
                    admin
                </span>

            </span>

            <span class="check">✓</span>

        </button>


        <!-- Teacher -->
        <button
            type="button"
            class="role-item <?= $currentRole['username'] === 'teacher' ? 'active' : '' ?>"
            data-role="teacher"
            data-name="Giáo viên Demo"
            data-username="teacher"
        >

            <span class="role-info">

                <span class="role-title">
                    Giáo viên Demo
                </span>

                <span class="separator">·</span>

                <span class="username">
                    teacher
                </span>

            </span>

            <span class="check">✓</span>

        </button>


        <!-- Student -->
        <button
            type="button"
            class="role-item <?= $currentRole['username'] === 'student' ? 'active' : '' ?>"
            data-role="student"
            data-name="Học sinh Demo"
            data-username="student"
        >

            <span class="role-info">

                <span class="role-title">
                    Học sinh Demo
                </span>

                <span class="separator">·</span>

                <span class="username">
                    student
                </span>

            </span>

            <span class="check">✓</span>

        </button>

    </div>

</div>

    </button>
            <a href="">Đăng xuất</a>
        <?php endif; ?>
    </nav>
</header>
<?php endif;?>
<script src="<?= e(base_url('/assets/js/jquery.min.js')) ?>"></script>
<script src="assets/js/dropdown.js"></script>
</body>
<main class="container">

