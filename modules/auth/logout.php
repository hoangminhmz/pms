<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';

logout();
setFlash('success', 'Đã đăng xuất thành công');
redirect(url('modules/auth/login.php'));
