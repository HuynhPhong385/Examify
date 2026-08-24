<div class="page-head"><div><h1>Đề thi</h1><p class="muted">Danh sách đề thi và trạng thái.</p></div><?php if(in_array($_SESSION['user_role'],['admin','teacher'],true)): ?><a class="btn btn-primary" href="<?= url('/exams/create') ?>">+ Tạo đề</a><?php endif; ?></div>
<form class="searchbar"><input name="q" value="<?= e($q) ?>" placeholder="Tìm tên đề..."><button class="btn">Tìm</button></form>
<div class="grid">
<?php foreach($items as $e): ?><article class="card"><div class="card-top"><span class="badge"><?= e($e['subject_name']) ?></span><span class="status <?= e($e['status']) ?>"><?= e($e['status']) ?></span></div><h2><?= e($e['title']) ?></h2><p><?= e($e['description']) ?></p><div class="meta"><?= (int)$e['question_count'] ?> câu · <?= (int)$e['duration_minutes'] ?> phút</div>
<?php if($_SESSION['user_role']==='student' && $e['status']==='published'): ?><a class="btn btn-primary" href="<?= url('/exams/'.$e['id'].'/take') ?>">Thi ngay</a><?php elseif(in_array($_SESSION['user_role'],['admin','teacher'],true)): ?><a class="btn" href="<?= url('/exams/'.$e['id'].'/edit') ?>">Sửa</a> <a class="btn" href="<?= url('/exams/'.$e['id'].'/stats') ?>">Thống kê</a><form class="inline" method="post" action="<?= url('/exams/'.$e['id'].'/delete') ?>" onsubmit="return confirm('Xóa đề?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="link-danger">Xóa</button></form><?php endif; ?>
</article><?php endforeach; ?>
</div>
<?php include __DIR__.'/../partials/pagination.php'; ?>
