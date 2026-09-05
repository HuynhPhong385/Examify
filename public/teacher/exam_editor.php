<?php
require __DIR__ . '/../../app/bootstrap.php';
$user = require_role_page(['teacher']);
$examId = positive_int($_GET['id'] ?? 0);
$exam = null;
$questions = [];
$pdo = Database::pdo();

if ($examId) {
    $stmt = $pdo->prepare('SELECT * FROM exams WHERE id=? LIMIT 1');
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    if (!$exam) {
        http_response_code(404);
        exit('Không tìm thấy đề.');
    }
    if ($user['role'] !== 'teacher' && (int)$exam['created_by'] !== (int)$user['id']) {
        http_response_code(403);
        exit('Bạn không có quyền sửa đề này.');
    }

    $stmt = $pdo->prepare('SELECT id,content,points,position FROM questions WHERE exam_id=? ORDER BY position,id');
    $stmt->execute([$examId]);
    $questions = $stmt->fetchAll();
    foreach ($questions as &$q) {
        $c = $pdo->prepare('SELECT id,content,is_correct,position FROM choices WHERE question_id=? ORDER BY position,id');
        $c->execute([$q['id']]);
        $q['choices'] = $c->fetchAll();
    }
    unset($q);
}

$pageTitle = ($exam ? 'Sửa đề' : 'Tạo đề') . ' - Examify';
require __DIR__ . '/../_header.php';
?>
<section class="editor-shell" data-exam-id="<?= $examId ?: 0 ?>">
    <div class="page-head">
        <div>
            <h1><?= $exam ? 'Chỉnh sửa đề thi' : 'Tạo đề thi mới' ?></h1>
            <p class="muted">Lưu thông tin đề trước, sau đó thêm các câu hỏi.</p>
        </div>
    </div>

    <form id="examMetaForm" class="panel form-grid">
        <label class="span-2">Tên đề
            <input name="title" required maxlength="220" value="<?= e($exam['title'] ?? '') ?>">
        </label>
        <label class="span-2">Mô tả
            <textarea name="description" rows="3"><?= e($exam['description'] ?? '') ?></textarea>
        </label>
        <label>Chuyên mục
            <input name="category" maxlength="100" value="<?= e($exam['category'] ?? '') ?>" placeholder="VD: CNTT, GPLX">
        </label>
        <label>Thời gian (phút)
            <input name="duration_minutes" type="number" min="1" max="600" value="<?= e((string)($exam['duration_minutes'] ?? 30)) ?>">
        </label>
        <label>Điểm đạt (%)
            <input name="passing_score" type="number" min="5" max="100" step="0.01" value="<?= e((string)($exam['passing_score'] ?? 50)) ?>">
        </label>
        <label class="checkbox-label">
            <input name="is_published" type="checkbox" value="1" <?= !empty($exam['is_published']) ? 'checked' : '' ?>>
            Xuất bản để học sinh nhìn thấy
        </label>
        <div class="span-2">
            <button class="btn primary" type="submit"><?= $exam ? 'Lưu thông tin đề' : 'Tạo đề & tiếp tục' ?></button>
        </div>
    </form>

    <?php if ($exam): ?>
    <section class="panel">
        <div class="section-title">
            <div>
                <h2>Câu hỏi</h2>
                <p class="muted">Mỗi câu hiện hỗ trợ 1 đáp án đúng.</p>
            </div>
            <button class="btn" id="addQuestionBtn" type="button">+ Thêm câu hỏi</button>
        </div>
        <div id="questionEditorList"></div>
    </section>
    <?php else: ?>
    <div class="alert info">Sau khi tạo đề, hệ thống sẽ chuyển sang màn hình thêm câu hỏi.</div>
    <?php endif; ?>
</section>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const pendingToast = sessionStorage.getItem('pendingToast');
        
        if (pendingToast) {
            Examify.toast(pendingToast);
            // Xóa đi để F5 không bị hiện lại
            sessionStorage.removeItem('pendingToast');
        }
    });
</script>
<?php if ($exam): ?>
<script id="questionData" type="application/json"><?= json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
<?php
$extraScripts = ['/assets/js/teacher-editor.js'];
require __DIR__ . '/../_footer.php';
?>
