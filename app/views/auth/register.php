<section class="auth-card">
<h1>Tạo tài khoản</h1>
<form method="post" action="<?= url('/register') ?>">
<input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
<label>Họ tên<input name="name" required minlength="2"></label>
<label>Email<input type="email" name="email" required></label>
<label>Mật khẩu<input type="password" name="password" required minlength="6"></label>
<button class="btn btn-primary" type="submit">Đăng ký</button>
</form>
<p>Đã có tài khoản? <a href="<?= url('/login') ?>">Đăng nhập</a></p>
</section>
