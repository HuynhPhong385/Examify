<?php
require __DIR__ . '/../app/bootstrap.php';
Auth::logout();
header('Location: ' . base_url('/login.php'));
exit;
