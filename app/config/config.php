<?php
declare(strict_types=1);

// File này giữ ĐỒNG THỜI 2 định dạng để không phải sửa code nơi khác:
//  1) hằng số (define) -> cho Controller.php, Router.php, header.php, QuestionController.php
//  2) return [...] -> cho bootstrap.php, Database.php, helpers.php

if (!defined('APP_NAME')) {
    define('APP_NAME', 'Examify');
}
if (!defined('BASE_URL')) {
    define('BASE_URL', ''); // Ví dụ: '/examify/public' nếu chạy trong subfolder.
}
if (!defined('DB_HOST')) {
    define('DB_HOST', '127.0.0.1');
}
if (!defined('DB_NAME')) {
    define('DB_NAME', 'examify');
}
if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}
if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../../public/uploads/questions');
}
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024);
}

// Lưu ý: không gọi date_default_timezone_set() hay session_start() ở đây,
// vì app/bootstrap.php đã tự làm 2 việc này ngay sau khi require file này
// (dùng đúng giá trị lấy từ mảng bên dưới) — gọi lại ở đây dễ gây xung đột session.

return [
    'app' => [
        'name'     => APP_NAME,
        'base_url' => BASE_URL,
        'timezone' => 'Asia/Ho_Chi_Minh',
    ],
    'db' => [
        'host'    => DB_HOST,
        'port'    => '3306',
        'name'    => DB_NAME,
        'charset' => 'utf8mb4',
        'user'    => DB_USER,
        'pass'    => DB_PASS,
    ],
    'upload' => [
        'dir'      => UPLOAD_DIR,
        'max_size' => MAX_UPLOAD_SIZE,
    ],
];