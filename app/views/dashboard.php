<section class="hero">
<div><span class="eyebrow">EXAMIFY</span><h1>Học tập. Thi thử. Tiến bộ.</h1><p>Quản lý ngân hàng câu hỏi, tạo đề ngẫu nhiên và chấm điểm tự động.</p></div>
<?php if($user['role']==='student'): ?><a class="btn btn-primary" href="<?= url('/exams') ?>">Bắt đầu thi</a><?php endif; ?>
</section>
<?php if($user['role']==='student'): ?>
<h2>Đề thi mới</h2>
<div class="grid">
<?php foreach(array_slice($exams,0,6) as $e): ?>
<article class="card"><span class="badge"><?= e($e['subject_name']) ?></span><h3><?= e($e['title']) ?></h3><p><?= e($e['description']) ?></p><div class="meta"><?= (int)$e['question_count'] ?> câu · <?= (int)$e['duration_minutes'] ?> phút</div><a class="btn btn-primary" href="<?= url('/exams/'.$e['id'].'/take') ?>">Thi ngay</a></article>
<?php endforeach; ?>
</div>
<?php else: ?>
<div class="grid stats-grid">
<div class="card"><div class="big">CRUD</div><p>Quản lý môn học & ngân hàng câu hỏi.</p></div>
<div class="card"><div class="big">REST</div><p>API nộp bài và tự động xử lý hết giờ.</p></div>
<div class="card"><div class="big">RBAC</div><p>Admin, teacher, student theo quyền.</p></div>
</div>
<?php endif; ?>
