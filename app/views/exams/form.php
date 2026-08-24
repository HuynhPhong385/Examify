<div class="page-head"><div><h1><?= $item?'Sửa':'Tạo' ?> đề thi</h1><p class="muted">Có thể random câu hỏi theo mức độ.</p></div></div>
<form class="card form-card" method="post" action="<?= $item?url('/exams/'.$item['id']):url('/exams') ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Môn<select name="subject_id" required><?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>" <?= (($item['subject_id']??0)==$s['id'])?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></label>
<label>Tên đề<input name="title" value="<?= e($item['title']??'') ?>" required></label>
<label>Mô tả<textarea name="description"><?= e($item['description']??'') ?></textarea></label>
<div class="three-col"><label>Thời gian (phút)<input type="number" name="duration_minutes" min="1" value="<?= (int)($item['duration_minutes']??30) ?>"></label><label>Tổng câu<input type="number" name="total_questions" min="1" value="<?= (int)($item['total_questions']??10) ?>"></label><label>Trạng thái<select name="status"><option value="draft" <?= (($item['status']??'draft')==='draft')?'selected':'' ?>>Nháp</option><option value="published" <?= (($item['status']??'draft')==='published')?'selected':'' ?>>Công khai</option></select></label></div>
<h3>Random theo độ khó</h3><div class="three-col"><label>Dễ<input type="number" name="easy_count" min="0" value="0"></label><label>Trung bình<input type="number" name="medium_count" min="0" value="0"></label><label>Khó<input type="number" name="hard_count" min="0" value="0"></label></div>
<p class="muted">Nếu tổng số câu theo độ khó chưa đủ, hệ thống tự bổ sung câu ngẫu nhiên còn thiếu.</p>
<button class="btn btn-primary">Lưu đề thi</button>
</form>
