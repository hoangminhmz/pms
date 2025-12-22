<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> - <?= APP_NAME ?></title>

    <!-- AdminLTE CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .info-box-number { font-size: 1.5rem; }
        .card-primary:not(.card-outline) > .card-header { background-color: #007bff; }
        .content-wrapper { min-height: calc(100vh - 57px - 60px); }
        .badge-status-confirmed { background-color: #17a2b8; }
        .badge-status-checked_in { background-color: #28a745; }
        .badge-status-checked_out { background-color: #6c757d; }
        .badge-status-cancelled { background-color: #dc3545; }
        .badge-status-no_show { background-color: #ffc107; color: #000; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
            <li class="nav-item d-none d-sm-inline-block">
                <a href="<?= url() ?>" class="nav-link">Trang chủ</a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <?php if (userRole() === 'teacher' || userRole() === 'student'): ?>
                <?php
                $currentClass = null;
                if (currentClassId()) {
                    $currentClass = fetchOne("SELECT * FROM school_classes WHERE id = ?", [currentClassId()]);
                }
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="fas fa-school"></i>
                        <span class="d-none d-md-inline">
                            <?= $currentClass ? h($currentClass['name']) : 'Chưa có lớp' ?>
                        </span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
                    <i class="far fa-user"></i>
                    <span class="d-none d-md-inline"><?= h(userName()) ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <li class="user-header bg-primary">
                        <p>
                            <?= h(userName()) ?>
                            <small><?= h(userEmail()) ?></small>
                            <small class="text-uppercase"><?= h(userRole()) ?></small>
                        </p>
                    </li>
                    <li class="user-footer">
                        <a href="<?= url('modules/auth/logout.php') ?>" class="btn btn-default btn-flat btn-block">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
    <!-- /.navbar -->
