<?php
require_once __DIR__ . '/../../app/bootstrap.php';

$user = Auth::user();
if (!$user || $user['role'] !== 'student') {
    redirect('../login.php');
}

$pdo = Database::pdo();

// 1. Lấy danh sách tất cả các đề thi
$stmtExams = $pdo->query("SELECT * FROM exams ORDER BY id DESC");
$allExams = $stmtExams->fetchAll();

// 2. Lấy lịch sử làm bài của riêng sinh viên này
$stmtAttempts = $pdo->prepare("
    SELECT a.*, e.title as exam_title, e.passing_score 
    FROM attempts a
    JOIN exams e ON a.exam_id = e.id
    WHERE a.user_id = ?
    ORDER BY a.id DESC
");
$stmtAttempts->execute([$user['id']]);
$attempts = $stmtAttempts->fetchAll();

$pageTitle = 'Trang chủ Học sinh';
include __DIR__ . '/../_header.php';
?>

<div class="container mt-4">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h3>Xin chào, <?= htmlspecialchars($user['name']) ?>!</h3>
            <p class="text-muted mb-0">Chào mừng bạn đến với hệ thống thi trực tuyến Examify.</p>
        </div>
    </div>

    <!-- PHẦN 1: DANH SÁCH ĐỀ THI ĐỂ LÀM -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Danh sách Đề thi hiện có</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php if (empty($allExams)): ?>
                    <p class="text-muted text-center">Hiện chưa có đề thi nào.</p>
                <?php else: ?>
                    <?php foreach ($allExams as $exam): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($exam['title']) ?></h5>
                                    <p class="card-text text-muted small"><?= htmlspecialchars($exam['description'] ?? 'Không có mô tả') ?></p>
                                    <div class="mt-auto">
                                        <p class="mb-1"><strong>Thời gian:</strong> <?= $exam['duration_minutes'] ?> phút</p>
                                        <a href="../exam.php?id=<?= $exam['id'] ?>" class="btn btn-success w-100 mt-2">Vào thi ngay</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- PHẦN 2: LỊCH SỬ KẾT QUẢ ĐÃ LÀM -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Lịch sử bài thi & Điểm số của bạn</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Tên đề thi</th>
                            <th>Điểm số</th>
                            <th>Kết quả</th>
                            <th>Thời gian nộp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($attempts)): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">Bạn chưa hoàn thành bài thi nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($attempts as $index => $att): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td><strong><?= htmlspecialchars($att['exam_title']) ?></strong></td>
                                    <td>
                                        <span class="badge <?= $att['score'] >= $att['passing_score'] ? 'bg-success' : 'bg-danger' ?>" style="font-size: 1rem;">
                                            <?= $att['score'] ?> điểm
                                        </span>
                                    </td>
                                    <td>
                                        <?= $att['score'] >= $att['passing_score'] ? 'Đạt' : 'Chưa đạt' ?>
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