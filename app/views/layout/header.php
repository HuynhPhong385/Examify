<?php use App\Core\Auth; $current=Auth::user(); $flash=flash(); ?>
<!doctype html>
<html lang="vi">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e(($title ?? APP_NAME).' — '.APP_NAME) ?></title>
<link rel="stylesheet" href="<?= url('css\style.css') ?>">
</head>
<body>
<header class="topbar">
  <a class="brand" href="<?= url('/') ?>">Exam<span>ify</span></a>
  <nav>
    <a href="<?= url('/dashboard') ?>">Dashboard</a>
    <a href="<?= url('/exams') ?>">Đề thi</a>
    <?php if($current && in_array($current['role'],['admin','teacher'],true)): ?>
      <a href="<?= url('/questions') ?>">Câu hỏi</a>
      <a href="<?= url('/subjects') ?>">Môn học</a>
    <?php endif; ?>
    <?php if($current && $current['role']==='student'): ?>
      <a href="<?= url('/history') ?>">Lịch sử</a>
    <?php endif; ?>
  </nav>
  <div class="user-menu">
  <?php if($current): ?>
    <span><?= e($current['name']) ?> · <?= e($current['role']) ?></span>
    <a class="btn btn-sm" href="<?= url('/logout') ?>">Đăng xuất</a>
  <?php else: ?>
    <a href="<?= url('/login') ?>">Đăng nhập</a>
    <a class="btn btn-sm" href="<?= url('/register') ?>">Đăng ký</a>
  <?php endif; ?>
  </div>
</header>
<main class="container">
<?php if($flash): ?><div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>
