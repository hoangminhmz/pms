<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('teacher');

$pageTitle = 'Lớp học của tôi';
$currentPage = 'classes';

// Redirect to dashboard - class management is shown there
redirect(url('modules/teacher/dashboard.php'));
