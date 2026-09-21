<?php
require __DIR__ . '/../../app/bootstrap.php';
$user = require_role_page(['admin', 'teacher']);
$examId = positive_int($_GET['id'] ?? 0);
$exam = null;
$questions = [];
$pdo = Database::pdo();
$error = '';
$notice = $_SESSION['teacher_editor_notice'] ?? '';
unset($_SESSION['teacher_editor_notice']);

function teacher_exam_slugify(string $text): string
{
    $map = [
        'à'=>'a','á'=>'a','ạ'=>'a','ả'=>'a','ã'=>'a','â'=>'a','ầ'=>'a','ấ'=>'a','ậ'=>'a','ẩ'=>'a','ẫ'=>'a','ă'=>'a','ằ'=>'a','ắ'=>'a','ặ'=>'a','ẳ'=>'a','ẵ'=>'a',
        'è'=>'e','é'=>'e','ẹ'=>'e','ẻ'=>'e','ẽ'=>'e','ê'=>'e','ề'=>'e','ế'=>'e','ệ'=>'e','ể'=>'e','ễ'=>'e',
        'ì'=>'i','í'=>'i','ị'=>'i','ỉ'=>'i','ĩ'=>'i',
        'ò'=>'o','ó'=>'o','ọ'=>'o','ỏ'=>'o','õ'=>'o','ô'=>'o','ồ'=>'o','ố'=>'o','ộ'=>'o','ổ'=>'o','ỗ'=>'o','ơ'=>'o','ờ'=>'o','ớ'=>'o','ợ'=>'o','ở'=>'o','ỡ'=>'o',
        'ù'=>'u','ú'=>'u','ụ'=>'u','ủ'=>'u','ũ'=>'u','ư'=>'u','ừ'=>'u','ứ'=>'u','ự'=>'u','ử'=>'u','ữ'=>'u',
        'ỳ'=>'y','ý'=>'y','ỵ'=>'y','ỷ'=>'y','ỹ'=>'y','đ'=>'d',
    ];
    $text = mb_strtolower(strtr($text, $map));
    $text = preg_replace('/[^a-z0-9]+/u', '-', $text);

    return trim((string)$text, '-') ?: 'de-thi';
}

if ($examId) {
    $stmt = $pdo->prepare('SELECT * FROM exams WHERE id=? LIMIT 1');
    $stmt->execute([$examId]);
    $exam = $stmt->fetch();
    if (!$exam) {
        http_response_code(404);
        exit('Không tìm thấy đề.');
    }
    if ($user['role'] !== 'admin' && (int)$exam['created_by'] !== (int)$user['id']) {
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

$formValues = [
    'title' => $exam['title'] ?? '',
    'description' => $exam['description'] ?? '',
    'category' => $exam['category'] ?? '',
    'duration_minutes' => $exam['duration_minutes'] ?? 30,
    'passing_score' => $exam['passing_score'] ?? 50,
    'is_published' => !empty($exam['is_published']),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formValues = [
        'title' => trim((string)($_POST['title'] ?? '')),
        'description' => trim((string)($_POST['description'] ?? '')),
        'category' => trim((string)($_POST['category'] ?? '')),
        'duration_minutes' => positive_int($_POST['duration_minutes'] ?? 30, 30),
        'passing_score' => (float)($_POST['passing_score'] ?? 50),
        'is_published' => !empty($_POST['is_published']),
    ];

    if (!hash_equals($_SESSION['csrf_token'] ?? '', (string)($_POST['csrf_token'] ?? ''))) {
        $error = 'Phiên làm việc đã hết hạn hoặc không hợp lệ. Vui lòng tải lại trang.';
    } elseif ($formValues['title'] === '') {
        $error = 'Tên đề là bắt buộc.';
    } elseif ($formValues['duration_minutes'] < 1 || $formValues['duration_minutes'] > 600) {
        $error = 'Thời gian làm bài phải từ 1 đến 600 phút.';
    } elseif ($formValues['passing_score'] < 5 || $formValues['passing_score'] > 100) {
        $error = 'Điểm đạt phải từ 5 đến 100%.';
    } else {
        try {
            if ($exam) {
                $stmt = $pdo->prepare('UPDATE exams SET title=?,description=?,category=?,duration_minutes=?,passing_score=?,is_published=? WHERE id=?');
                $stmt->execute([
                    $formValues['title'], $formValues['description'], $formValues['category'] ?: null,
                    $formValues['duration_minutes'], $formValues['passing_score'], $formValues['is_published'] ? 1 : 0, $examId,
                ]);
                $_SESSION['teacher_editor_notice'] = 'Đã lưu thông tin đề.';
                redirect('/teacher/exam_editor.php?id=' . $examId);
            }

            $stmt = $pdo->prepare('SELECT 1 FROM exams WHERE title=? LIMIT 1');
            $stmt->execute([$formValues['title']]);
            if ($stmt->fetchColumn()) {
                $error = 'Tên đề đã tồn tại. Vui lòng chọn tên khác.';
            } else {
                $slugBase = teacher_exam_slugify($formValues['title']);
                $slug = $slugBase;
                for ($i = 2; ; $i++) {
                    $stmt = $pdo->prepare('SELECT 1 FROM exams WHERE slug=? LIMIT 1');
                    $stmt->execute([$slug]);
                    if (!$stmt->fetchColumn()) {
                        break;
                    }
                    $slug = $slugBase . '-' . $i;
                }
                $stmt = $pdo->prepare('INSERT INTO exams (title,slug,description,category,duration_minutes,passing_score,is_published,created_by) VALUES (?,?,?,?,?,?,?,?)');
                $stmt->execute([
                    $formValues['title'], $slug, $formValues['description'], $formValues['category'] ?: null,
                    $formValues['duration_minutes'], $formValues['passing_score'], $formValues['is_published'] ? 1 : 0, $user['id'],
                ]);
                $newExamId = (int)$pdo->lastInsertId();
                $_SESSION['teacher_editor_notice'] = 'Đã tạo đề. Bây giờ hãy thêm câu hỏi.';
                redirect('/teacher/exam_editor.php?id=' . $newExamId);
            }
        } catch (PDOException) {
            $error = 'Không thể lưu đề lúc này. Vui lòng thử lại.';
        }
    }
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

    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($notice): ?><div class="alert success"><?= e($notice) ?></div><?php endif; ?>
    <form id="examMetaForm" class="panel form-grid" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label class="span-2">Tên đề
            <input name="title" required maxlength="220" value="<?= e((string)$formValues['title']) ?>">
        </label>
        <label class="span-2">Mô tả
            <textarea name="description" rows="3"><?= e((string)$formValues['description']) ?></textarea>
        </label>
        <label>Chuyên mục
            <input name="category" maxlength="100" value="<?= e((string)$formValues['category']) ?>" placeholder="VD: CNTT, GPLX">
        </label>
        <label>Thời gian (phút)
            <input name="duration_minutes" type="number" min="1" max="600" value="<?= e((string)$formValues['duration_minutes']) ?>">
        </label>
        <label>Điểm đạt (%)
            <input name="passing_score" type="number" min="5" max="100" step="0.01" value="<?= e((string)$formValues['passing_score']) ?>">
        </label>
        <label class="checkbox-label">
            <input name="is_published" type="checkbox" value="1" <?= $formValues['is_published'] ? 'checked' : '' ?>>
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
<?php if ($exam): ?>
<script id="questionData" type="application/json"><?= json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?></script>
<?php endif; ?>
<?php
$extraScripts = ['/assets/js/teacher-editor.js'];
require __DIR__ . '/../_footer.php';
?>
