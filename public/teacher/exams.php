<?php
require_once __DIR__ . '/../../app/bootstrap.php';

// Kiểm tra quyền giáo viên
// if (!Auth::check() || Auth::user()['role'] !== 'teacher') {
//     redirect('login.php');
// }

$pdo = Database::pdo();

// Lấy danh sách đề thi kèm theo thống kê số lượt làm bài (attempts count)
$stmt = $pdo->query("
    SELECT e.*, 
           (SELECT COUNT(*) FROM attempts WHERE attempts.exam_id = e.id) as total_attempts,
           (SELECT AVG(score) FROM attempts WHERE attempts.exam_id = e.id) as avg_score
    FROM exams e
    ORDER BY e.id DESC
");
$exams = $stmt->fetchAll();

$pageTitle = 'Quản lý Đề thi';
include __DIR__ . '/../_header.php';
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Danh sách Đề thi</h2>
        <a href="exam_editor.php" class="btn btn-primary">+ Tạo đề thi mới</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Tiêu đề đề thi</th>
                            <th>Thời gian</th>
                            <th>Điểm đạt</th>
                            <th>Lượt làm bài</th>
                            <th>Điểm trung bình</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($exams)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">Chưa có đề thi nào được tạo.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($exams as $exam): ?>
                                <tr>
                                    <td><?= $exam['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($exam['title']) ?></strong></td>
                                    <td><?= $exam['duration_minutes'] ?> phút</td>
                                    <td><?= $exam['passing_score'] ?> điểm</td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?= $exam['total_attempts'] ?? 0 ?> lượt
                                        </span>
                                    </td>
                                    <td>
                                        <?= $exam['avg_score'] !== null ? round($exam['avg_score'], 2) : 'N/A' ?>
                                    </td>
                                    <td>
                                        <a href="exam_editor.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-warning">Sửa</a>
                                        <a href="exam_stats.php?id=<?= $exam['id'] ?>" class="btn btn-sm btn-info ">Thống kê & Điểm</a>
                                    </td>
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