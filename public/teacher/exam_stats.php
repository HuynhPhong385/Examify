<?php
require_once __DIR__ . '/../../app/bootstrap.php';

$examId = $_GET['id'] ?? null;
if (!$examId) {
    redirect('exams.php');
}

$pdo = Database::pdo();

// Lấy thông tin đề thi
$stmtExam = $pdo->prepare("SELECT * FROM exams WHERE id = ?");
$stmtExam->execute([$examId]);
$exam = $stmtExam->fetch();

if (!$exam) {
    die("Không tìm thấy đề thi!");
}

// Lấy danh sách sinh viên đã làm bài kèm điểm số
$stmtAttempts = $pdo->prepare("
    SELECT a.*, u.name as student_name, u.email as student_email
    FROM attempts a
    JOIN users u ON a.user_id = u.id
    WHERE a.exam_id = ?
    ORDER BY a.score DESC
");
$stmtAttempts->execute([$examId]);
$attempts = $stmtAttempts->fetchAll();

$pageTitle = 'Thống kê kết quả: ' . $exam['title'];
include __DIR__ . '/../_header.php';
?>

<div class="container mt-4">
    <div class="mb-3">
        <a href="exams.php" class="btn btn-secondary btn-sm">&larr; Quay lại danh sách đề</a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3>Thống kê đề thi: <?= htmlspecialchars($exam['title']) ?></h3>
            <p class="text-muted mb-0"><?= htmlspecialchars($exam['description'] ?? 'Không có mô tả') ?></p>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Danh sách sinh viên đã nộp bài (<?= count($attempts) ?> lượt)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Họ tên sinh viên</th>
                            <th>Email</th>
                            <th>Điểm số</th>
                            <th>Trạng thái</th>
                            <th>Thời gian nộp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attempts)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Chưa có sinh viên nào làm bài thi này.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attempts as $index => $att): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($att['student_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($att['student_email']) ?></td>
                                    <td>
                                        <span class="badge <?= $att['score'] >= $exam['passing_score'] ? 'bg-success' : 'bg-danger' ?>" style="font-size: 1rem;">
                                            <?= $att['score'] ?> điểm
                                        </span>
                                    </td>
                                    <td>
                                        <?= $att['score'] >= $exam['passing_score'] ? 'Đạt' : 'Chưa đạt' ?>
                                    </td>
                                    <td><?= $att['created_at'] ?? 'N/A' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
include __DIR__ . '/../_footer.php';
?>