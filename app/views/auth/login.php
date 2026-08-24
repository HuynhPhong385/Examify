<section class="auth-card">
<h1>Đăng nhập</h1>
<p class="muted">Đăng nhập để bắt đầu làm bài hoặc quản lý hệ thống.</p>
<form method="post" action="<?= url('/login') ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Email<input type="email" name="email" required autofocus></label>
<label>Mật khẩu<input type="password" name="password" required></label>
<button class="btn btn-primary" type="submit">Đăng nhập</button>
</form>
<p>Chưa có tài khoản? <a href="<?= url('/register') ?>">Đăng ký</a></p>
</section>
