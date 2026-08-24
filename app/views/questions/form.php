<div class="page-head"><div><h1><?= $item?'Sửa':'Thêm' ?> câu hỏi</h1></div><a class="btn" href="<?= url('/questions') ?>">← Quay lại</a></div>
<form class="card form-card" method="post" enctype="multipart/form-data" action="<?= $item?url('/questions/'.$item['id']):url('/questions') ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Môn học<select name="subject_id" required><?php foreach($subjects as $s): ?><option value="<?= $s['id'] ?>" <?= (($item['subject_id']??0)==$s['id'])?'selected':'' ?>><?= e($s['name']) ?></option><?php endforeach; ?></select></label>
<label>Nội dung câu hỏi
<div class="editor-toolbar"><button type="button" data-cmd="bold"><b>B</b></button><button type="button" data-cmd="italic"><i>I</i></button><button type="button" data-cmd="underline"><u>U</u></button></div>
<div id="rich-editor" class="rich-editor" contenteditable="true"><?= $item['content']??'' ?></div>
<textarea id="content" name="content" hidden required><?= e($item['content']??'') ?></textarea></label>
<div class="two-col"><label>Độ khó<select name="difficulty"><option value="easy" <?= (($item['difficulty']??'medium')==='easy')?'selected':'' ?>>Dễ</option><option value="medium" <?= (($item['difficulty']??'medium')==='medium')?'selected':'' ?>>Trung bình</option><option value="hard" <?= (($item['difficulty']??'medium')==='hard')?'selected':'' ?>>Khó</option></select></label><label>Ảnh minh họa<input type="file" name="image" accept="image/jpeg,image/png,image/webp"><small>JPG/PNG/WEBP, tối đa 2MB.</small></label></div>
<div class="options">
<label>A<input name="option_a" value="<?= e($item['option_a']??'') ?>" required></label>
<label>B<input name="option_b" value="<?= e($item['option_b']??'') ?>" required></label>
<label>C<input name="option_c" value="<?= e($item['option_c']??'') ?>" required></label>
<label>D<input name="option_d" value="<?= e($item['option_d']??'') ?>" required></label>
</div>
<label>Đáp án đúng<select name="correct_option"><?php foreach(['A','B','C','D'] as $op): ?><option <?= (($item['correct_option']??'A')===$op)?'selected':'' ?>><?= $op ?></option><?php endforeach; ?></select></label>
<button class="btn btn-primary" type="submit">Lưu câu hỏi</button>
</form>
