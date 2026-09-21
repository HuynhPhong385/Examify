<?php
require_once __DIR__ . '/../../app/bootstrap.php';

// Kiểm tra quyền giáo viên
// if (!Auth::check() || Auth::user()['role'] !== 'teacher') {
//     redirect('login.php');
// }

$pageTitle = 'Dashboard Giáo Viên';
include __DIR__ . '/../_header.php';
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2>Trang quản lý dành cho Giáo viên</h2>
            <p class="text-muted">Chào mừng bạn quay trở lại hệ thống Examify.</p>
            <hr>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Quản lý Đề thi</h5>
                    <p class="card-text">Tạo mới, chỉnh sửa hoặc xóa các đề thi và câu hỏi.</p>
                    <a href="exams.php" class="btn btn-primary">Đi đến Quản lý đề thi</a>
                </div>
            </div>
        </div>
        <!-- Thêm các thẻ tính năng khác nếu cần -->
    </div>
</div>

<?php 
include __DIR__ . '/../_footer.php';
?>