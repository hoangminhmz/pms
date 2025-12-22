<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('teacher');

$pageTitle = 'Dashboard Giảng viên';
$currentPage = 'dashboard';

// Get teacher's classes
$myClasses = getTeacherClasses(userId());

// If has classes, get stats for current class
$stats = null;
if (currentClassId()) {
    $stats = getClassStats(currentClassId());

    // Recent student activity
    $recentActivity = fetchAll("
        SELECT al.*, u.name as user_name
        FROM activity_logs al
        INNER JOIN users u ON al.user_id = u.id
        WHERE al.class_id = ?
        ORDER BY al.created_at DESC
        LIMIT 10
    ", [currentClassId()]);
} else {
    $recentActivity = [];
}

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
                        <h1 class="m-0">Dashboard Giảng viên</h1>
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

                <?php if (empty($myClasses)): ?>
                    <!-- No Classes Alert -->
                    <div class="alert alert-info">
                        <h5><i class="icon fas fa-info"></i> Chưa có lớp học</h5>
                        Bạn chưa được phân công lớp học nào. Vui lòng liên hệ Admin để tạo lớp học cho bạn.
                    </div>
                <?php else: ?>

                    <!-- My Classes -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Lớp học của tôi</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <?php foreach ($myClasses as $class): ?>
                                    <?php
                                    $classStats = getClassStats($class['id']);
                                    $isCurrent = $class['id'] == currentClassId();
                                    ?>
                                    <div class="col-md-4">
                                        <div class="card <?= $isCurrent ? 'card-primary card-outline' : '' ?>">
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <?= h($class['name']) ?>
                                                    <?php if ($isCurrent): ?>
                                                        <span class="badge badge-primary">Đang chọn</span>
                                                    <?php endif; ?>
                                                </h5>
                                                <p class="card-text text-muted">
                                                    <strong>Mã:</strong> <?= h($class['code']) ?><br>
                                                    <small><?= h($class['description'] ?? '') ?></small>
                                                </p>
                                                <div class="row mt-3">
                                                    <div class="col-6">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?= $classStats['students'] ?></h5>
                                                            <span class="description-text">Sinh viên</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="description-block">
                                                            <h5 class="description-header"><?= $classStats['scenarios'] ?></h5>
                                                            <span class="description-text">Kịch bản</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($stats): ?>
                        <!-- Stats Row -->
                        <div class="row">
                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-info">
                                    <div class="inner">
                                        <h3><?= $stats['students'] ?></h3>
                                        <p>Sinh viên</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                    <a href="<?= url('modules/teacher/students.php') ?>" class="small-box-footer">
                                        Xem chi tiết <i class="fas fa-arrow-circle-right"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-success">
                                    <div class="inner">
                                        <h3><?= $stats['rooms'] ?></h3>
                                        <p>Phòng</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-bed"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-warning">
                                    <div class="inner">
                                        <h3><?= $stats['guests'] ?></h3>
                                        <p>Khách hàng</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-3 col-6">
                                <div class="small-box bg-danger">
                                    <div class="inner">
                                        <h3><?= $stats['reservations'] ?></h3>
                                        <p>Đặt phòng</p>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Student Activity -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Hoạt động sinh viên gần đây</h3>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm">
                                    <tbody>
                                        <?php if (empty($recentActivity)): ?>
                                            <tr>
                                                <td class="text-center text-muted p-3">Chưa có hoạt động</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($recentActivity as $activity): ?>
                                                <tr>
                                                    <td>
                                                        <small class="text-muted"><?= formatDateTime($activity['created_at']) ?></small><br>
                                                        <strong><?= h($activity['user_name']) ?></strong>:
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
                    <?php endif; ?>

                <?php endif; ?>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
