<?php
declare(strict_types=1);

const APP_NAME = 'Examify';
const BASE_URL = ''; // Ví dụ: '/examify/public' nếu chạy trong subfolder.
const DB_HOST = '127.0.0.1';
const DB_NAME = 'examify';
const DB_USER = 'root';
const DB_PASS = '';
const UPLOAD_DIR = __DIR__ . '/../../public/uploads/questions';
const MAX_UPLOAD_SIZE = 2 * 1024 * 1024;

date_default_timezone_set('Asia/Ho_Chi_Minh');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']),
        'samesite' => 'Lax'
    ]);
    session_start();
}
