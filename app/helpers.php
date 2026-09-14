<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_url(string $path = ''): string
{
    global $config;
    $base = rtrim((string)($config['app']['base_url'] ?? ''), '/');
    $path = '/' . ltrim($path, '/');

    return $base . ($path === '/' ? '' : $path);
}

function redirect(string $path): never
{
    header('Location: ' . base_url($path));
    exit;
}

function json_input(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === '' || $raw === false) {
        return [];
    }

    $data = json_decode($raw, true);

    if (!is_array($data)) {
        json_response(['error' => 'JSON không hợp lệ.'], 400);
    }

    return $data;
}

function json_response(array $data, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');

    echo json_encode(
        $data,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf_json(): void
{
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';

    if (
        !$token ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $token)
    ) {
        json_response(
            ['error' => 'CSRF token không hợp lệ. Hãy tải lại trang.'],
            419
        );
    }
}

function require_auth_page(): array
{
    $user = Auth::user();

    if (!$user) {
        $_SESSION['redirect_after_login'] =
            $_SERVER['REQUEST_URI'] ?? '/dashboard.php';

        redirect('/login.php');
    }

    return $user;
}

function require_role_page(array $roles): array
{
    $user = require_auth_page();

    if (!in_array($user['role'], $roles, true)) {
        http_response_code(403);
        exit('403 - Bạn không có quyền truy cập.');
    }

    return $user;
}

function require_auth_json(): array
{
    $user = Auth::user();

    if (!$user) {
        json_response(
            ['error' => 'Bạn chưa đăng nhập.'],
            401
        );
    }

    return $user;
}

function require_role_json(array $roles): array
{
    $user = require_auth_json();

    if (!in_array($user['role'], $roles, true)) {
        json_response(
            ['error' => 'Bạn không có quyền thực hiện thao tác này.'],
            403
        );
    }

    return $user;
}

function positive_int(mixed $value, int $default = 0): int
{
    $n = filter_var($value, FILTER_VALIDATE_INT);

    return ($n !== false && $n > 0) ? $n : $default;
}