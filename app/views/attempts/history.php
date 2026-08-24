<div class="page-head"><div><h1>Lịch sử bài làm</h1><p class="muted">Theo dõi kết quả các lần thi.</p></div></div>
<div class="table-wrap"><table><thead><tr><th>Đề</th><th>Môn</th><th>Điểm</th><th>Đúng</th><th>Trạng thái</th><th>Ngày</th><th></th></tr></thead><tbody>
<?php foreach($items as $a): ?><tr><td><?= e($a['exam_title']) ?></td><td><?= e($a['subject_name']) ?></td><td><strong><?= number_format((float)$a['score'],2) ?></strong></td><td><?= $a['correct_count'].'/'.$a['total_questions'] ?></td><td><?= e($a['status']) ?></td><td><?= e($a['submitted_at']??$a['started_at']) ?></td><td><?php if($a['status']==='submitted'): ?><a class="btn btn-sm" href="<?= url('/results/'.$a['id']) ?>">Chi tiết</a><?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div>
