<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void {
        $this->view('auth/login', ['title' => 'Đăng nhập', 'csrf' => $this->csrf()]);
    }

    public function login(): void {
        $this->verifyCsrf($_POST['_csrf'] ?? null);
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        $user = (new User)->findByEmail($email);
        
        // Đổi từ password_verify sang so sánh chuỗi trực tiếp
        if (!$user || $password !== $user['password']) {
            flash('Email hoặc mật khẩu không đúng.', 'danger');
            $this->redirect('/login');
        }
        
        Auth::login($user);
        $this->redirect('/dashboard');
    }

    public function showRegister(): void {
        $this->view('auth/register', ['title' => 'Đăng ký', 'csrf' => $this->csrf()]);
    }

    public function register(): void {
        $this->verifyCsrf($_POST['_csrf'] ?? null);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
            flash('Dữ liệu đăng ký chưa hợp lệ. Mật khẩu tối thiểu 6 ký tự.', 'danger');
            $this->redirect('/register');
        }

        $u = new User;
        if ($u->findByEmail($email)) {
            flash('Email đã tồn tại.', 'danger');
            $this->redirect('/register');
        }

        // Lưu ý: Nếu Model User của bạn có hàm tự động hash mật khẩu khi create, 
        // bạn cần kiểm tra lại Model User. Nếu không, ở đây nó sẽ lưu thẳng chữ thuần.
        $id = $u->create($name, $email, $password, 'student');
        
        Auth::login($u->find($id));
        $this->redirect('/dashboard');
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect('/');
    }
}