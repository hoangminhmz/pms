<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';

requireRole('admin');

$pageTitle = 'Dashboard Admin';
$currentPage = 'dashboard';

// Get statistics
$stats = [
    'users' => fetchValue("SELECT COUNT(*) FROM users"),
    'teachers' => fetchValue("SELECT COUNT(*) FROM users WHERE role = 'teacher'"),
    'students' => fetchValue("SELECT COUNT(*) FROM users WHERE role = 'student'"),
    'classes' => fetchValue("SELECT COUNT(*) FROM school_classes"),
    'scenarios' => fetchValue("SELECT COUNT(*) FROM scenarios"),
    'active_classes' => fetchValue("SELECT COUNT(*) FROM school_classes WHERE is_active = 1"),
];

// Recent activity
$recentActivity = fetchAll("
    SELECT al.*, u.name as user_name
    FROM activity_logs al
    LEFT JOIN users u ON al.user_id = u.id
    ORDER BY al.created_at DESC
    LIMIT 10
");

// Recent classes
$recentClasses = fetchAll("
    SELECT sc.*, u.name as teacher_name,
           (SELECT COUNT(*) FROM class_students WHERE class_id = sc.id) as student_count
    FROM school_classes sc
    LEFT JOIN users u ON sc.teacher_id = u.id
    ORDER BY sc.created_at DESC
    LIMIT 5
");

require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/sidebar.php';
?>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
        <!-- Content Header -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Dashboard Admin</h1>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">

                <!-- Flash Messages -->
                <?php if (hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= getFlash('success') ?>
                    </div>
                <?php endif; ?>

                <!-- Stats Row -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3><?= $stats['users'] ?></h3>
                                <p>Tổng người dùng</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <a href="<?= url('modules/admin/users.php') ?>" class="small-box-footer">
                                Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3><?= $stats['teachers'] ?></h3>
                                <p>Giảng viên</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-chalkboard-teacher"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <h3><?= $stats['students'] ?></h3>
                                <p>Sinh viên</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-user-graduate"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <h3><?= $stats['active_classes'] ?></h3>
                                <p>Lớp đang hoạt động</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-school"></i>
                            </div>
                            <a href="<?= url('modules/admin/classes.php') ?>" class="small-box-footer">
                                Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Content Row -->
                <div class="row">
                    <!-- Recent Classes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Lớp học gần đây</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Tên lớp</th>
                                            <th>Giảng viên</th>
                                            <th>SV</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($recentClasses)): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">Chưa có lớp học</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentClasses as $class): ?>
                                                <tr>
                                                    <td><?= h($class['name']) ?></td>
                                                    <td><?= h($class['teacher_name']) ?></td>
                                                    <td><?= $class['student_count'] ?></td>
                                                    <td>
                                                        <?php if ($class['is_active']): ?>
                                                            <span class="badge badge-success">Hoạt động</span>
                                                        <?php else: ?>
                                                            <span class="badge badge-secondary">Ngừng</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Hoạt động gần đây</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm">
                                    <tbody>
                                        <?php if (empty($recentActivity)): ?>
                                            <tr>
                                                <td class="text-center text-muted">Chưa có hoạt động</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentActivity as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <small class="text-muted"><?= formatDateTime($activity['created_at']) ?></small><br>
                                                        <strong><?= h($activity['user_name'] ?? 'System') ?></strong>:
                                                        <?= h($activity['description']) ?>
                                                        <span class="badge badge-info"><?= h($activity['action']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
