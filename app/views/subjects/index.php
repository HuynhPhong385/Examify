<div class="page-head"><div><h1>Môn học</h1><p class="muted">Quản lý danh mục môn học.</p></div></div>
<div class="two-col">
<section class="card">
<h2>Thêm môn học</h2>
<form method="post" action="<?= url('/subjects') ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Tên môn<input name="name" required></label>
<label>Mô tả<textarea name="description"></textarea></label>
<button class="btn btn-primary">Thêm</button>
</form>
</section>
<section>
<form class="searchbar"><input name="q" value="<?= e($q) ?>" placeholder="Tìm môn học..."><button class="btn">Tìm</button></form>
<div class="table-wrap"><table><thead><tr><th>ID</th><th>Tên</th><th>Số câu</th><th>Thao tác</th></tr></thead><tbody>
<?php foreach($items as $item): ?><tr><td><?= $item['id'] ?></td><td><?= e($item['name']) ?><small><?= e($item['description']) ?></small></td><td><?= $item['question_count'] ?></td><td>
<details><summary>Sửa</summary><form method="post" action="<?= url('/subjects/'.$item['id']) ?>"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><input name="name" value="<?= e($item['name']) ?>"><textarea name="description"><?= e($item['description']) ?></textarea><button class="btn btn-sm">Lưu</button></form></details>
<?php if($_SESSION['user_role']==='admin'): ?><form method="post" action="<?= url('/subjects/'.$item['id'].'/delete') ?>" onsubmit="return confirm('Xóa môn học?')"><input type="hidden" name="_csrf" value="<?= e($csrf) ?>"><button class="link-danger">Xóa</button></form><?php endif; ?>
</td></tr><?php endforeach; ?>
</tbody></table></div>
<?php include __DIR__.'/../partials/pagination.php'; ?>
</section></div>
