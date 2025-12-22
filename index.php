<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/core/db.php';
require_once __DIR__ . '/core/functions.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/isolation.php';

// Redirect to login if not authenticated
if (!isLoggedIn()) {
    redirect(url('modules/auth/login.php'));
}

// Route to appropriate dashboard based on role
$role = userRole();

switch ($role) {
    case 'admin':
        require_once __DIR__ . '/modules/admin/dashboard.php';
        break;

    case 'teacher':
        require_once __DIR__ . '/modules/teacher/dashboard.php';
        break;

    case 'student':
        require_once __DIR__ . '/modules/student/dashboard.php';
        break;

    default:
        setFlash('error', 'Invalid user role');
        redirect(url('modules/auth/logout.php'));
}
