<?php
require __DIR__ . '/../../app/bootstrap.php';
$user = require_role_page(['admin','teacher']);
$pageTitle = 'Quản lý đề thi - Examify';
require __DIR__ . '/../_header.php';
?>
<section class="page-head">
    <div>
        <h1>Quản lý đề thi</h1>
        <p class="muted">Tạo, chỉnh sửa, xuất bản hoặc xóa bộ đề.</p>
    </div>
    <a class="btn primary" href="<?= e(base_url('/teacher/exam_editor.php')) ?>">+ Tạo đề mới</a>
</section>
<div id="adminExamGrid" class="cards-grid"></div>
<?php
$extraScripts = ['/assets/js/teacher-exams.js'];
require __DIR__ . '/../_footer.php';
?>
