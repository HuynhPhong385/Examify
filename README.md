# Examify — Website Quản lý & Thi thử Trắc nghiệm Trực tuyến

Examify là ứng dụng PHP + MySQL theo mô hình MVC, dùng HTML5/CSS3/JavaScript/jQuery và REST API.

## Chức năng

- Đăng ký / đăng nhập / đăng xuất.
- Phân quyền: `admin`, `teacher`, `student`.
- Admin/teacher:
  - CRUD môn học.
  - CRUD ngân hàng câu hỏi.
  - Tạo đề thi.
  - Chọn câu hỏi thủ công hoặc random theo số lượng.
  - Cấu hình thời gian và số câu.
  - Xem danh sách bài thi.
  - Xem thống kê điểm.
- Student:
  - Xem đề thi.
  - Làm bài với đồng hồ đếm ngược.
  - Tự động nộp khi hết giờ qua REST API.
  - Nộp bài thủ công.
  - Xem lịch sử bài làm và chi tiết kết quả.
- Tìm kiếm + phân trang.
- Upload ảnh cho câu hỏi.
- Editor câu hỏi dạng rich-text cơ bản.
- Session + Cookie.
- CSRF token.
- Validation đầu vào.
- PDO + prepared statements.
- REST API JSON.

## Yêu cầu

- PHP 8.1+
- MySQL 8+ / MariaDB 10.5+
- Apache + mod_rewrite hoặc PHP built-in server
- Trình duyệt hiện đại

## Cài đặt nhanh

1. Tạo database:
   - Mở phpMyAdmin hoặc MySQL.
   - Import `database/schema.sql`.
   - Import `database/seed.sql`.

2. Sửa thông tin DB trong:
   `app/config/config.php`

3. Đảm bảo thư mục:
   `public/uploads/questions`
   có quyền ghi.

4. Chạy:
   ```bash
   php -S localhost:8000 -t public public/index.php
   ```

5. Truy cập:
   `http://localhost:8000`

## Tài khoản mẫu

- Admin: `admin@examify.local` / `password`
- Teacher: `teacher@examify.local` / `password`
- Student: `student@examify.local` / `password`

## Cấu trúc

```text
examify/
├── app/
│   ├── config/
│   ├── controllers/
│   ├── core/
│   ├── models/
│   └── views/
├── database/
├── public/
│   ├── assets/
│   ├── uploads/
│   ├── .htaccess
│   └── index.php
└── README.md
```

## Luồng REST API

`POST /api/exams/{id}/submit`

Body:
```json
{
  "answers": {
    "12": "A",
    "13": "C"
  }
}
```

API trả JSON kết quả và frontend tự chuyển về trang kết quả.

## Ghi chú

Đây là bộ source học tập/đồ án. Khi triển khai production nên bổ sung HTTPS, rate limiting, logging, email verification, password reset, audit log, CSP và cấu hình web server chặt chẽ.
