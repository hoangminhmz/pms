<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('student');

$pageTitle = 'Dashboard Sinh viên';
$currentPage = 'dashboard';

// Check if student has a class
if (!currentClassId()) {
    $noClassMessage = true;
} else {
    $noClassMessage = false;

    // Get class info
    $classInfo = fetchOne("SELECT * FROM school_classes WHERE id = ?", [currentClassId()]);

    // Get stats for current class
    $stats = getClassStats(currentClassId());

    // Get assigned scenarios
    $myScenarios = fetchAll("
        SELECT s.*, sa.status as attempt_status, sa.score, sa.completed_at
        FROM scenarios s
        LEFT JOIN scenario_attempts sa ON s.id = sa.scenario_id AND sa.student_id = ?
        WHERE s.class_id = ? AND s.is_active = 1
        ORDER BY s.created_at DESC
    ", [userId(), currentClassId()]);

    // Today's arrivals (for practice)
    $todayArrivals = fetchAll("
        SELECT r.*, g.full_name, rt.name as room_type_name
        FROM reservations r
        INNER JOIN guests g ON r.guest_id = g.id
        INNER JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.class_id = ? AND r.arrival_date = CURDATE() AND r.status = 'confirmed'
        ORDER BY r.created_at
    ", [currentClassId()]);

    // Current in-house guests
    $inHouse = fetchValue("SELECT COUNT(*) FROM reservations WHERE class_id = ? AND status = 'checked_in'", [currentClassId()]);
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
                        <h1 class="m-0">Dashboard Sinh viên</h1>
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

                <?php if ($noClassMessage): ?>
                    <!-- No Class Alert -->
                    <div class="alert alert-warning">
                        <h5><i class="icon fas fa-exclamation-triangle"></i> Chưa được thêm vào lớp</h5>
                        Bạn chưa được thêm vào lớp học nào. Vui lòng liên hệ giảng viên để được thêm vào lớp.
                    </div>
                <?php else: ?>

                    <!-- Class Info -->
                    <div class="card card-primary card-outline">
                        <div class="card-body">
                            <h5>
                                <i class="fas fa-school"></i> <?= h($classInfo['name']) ?>
                                <small class="text-muted">(<?= h($classInfo['code']) ?>)</small>
                            </h5>
                            <p class="text-muted mb-0"><?= h($classInfo['description'] ?? '') ?></p>
                        </div>
                    </div>

                    <!-- Stats Row -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3><?= $stats['rooms'] ?></h3>
                                    <p>Phòng</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-bed"></i>
                                </div>
                                <a href="<?= url('modules/pms/rooms.php') ?>" class="small-box-footer">
                                    Xem phòng <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3><?= $inHouse ?></h3>
                                    <p>Khách trong nhà</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3><?= count($todayArrivals) ?></h3>
                                    <p>Arrivals hôm nay</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3><?= count($myScenarios) ?></h3>
                                    <p>Kịch bản thực hành</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <a href="<?= url('modules/student/scenarios.php') ?>" class="small-box-footer">
                                    Làm bài tập <i class="fas fa-arrow-circle-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Today's Arrivals -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Arrivals hôm nay</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Khách</th>
                                                <th>Loại phòng</th>
                                                <th>Đêm</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($todayArrivals)): ?>
                                                <tr>
                                                    <td colspan="3" class="text-center text-muted">Không có arrivals</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($todayArrivals as $arrival): ?>
                                                    <tr>
                                                        <td><?= h($arrival['full_name']) ?></td>
                                                        <td><?= h($arrival['room_type_name']) ?></td>
                                                        <td><?= $arrival['nights'] ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (!empty($todayArrivals)): ?>
                                    <div class="card-footer">
                                        <a href="<?= url('modules/pms/checkin.php') ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-sign-in-alt"></i> Đi đến Check-in
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- My Scenarios -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Kịch bản thực hành</h3>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm">
                                        <tbody>
                                            <?php if (empty($myScenarios)): ?>
                                                <tr>
                                                    <td class="text-center text-muted">Chưa có kịch bản</td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach (array_slice($myScenarios, 0, 5) as $scenario): ?>
                                                    <tr>
                                                        <td>
                                                            <strong><?= h($scenario['title']) ?></strong><br>
                                                            <small class="text-muted">
                                                                <?php
                                                                $diffBadges = ['easy' => 'success', 'medium' => 'warning', 'hard' => 'danger'];
                                                                ?>
                                                                <span class="badge badge-<?= $diffBadges[$scenario['difficulty']] ?>">
                                                                    <?= ucfirst($scenario['difficulty']) ?>
                                                                </span>
                                                                <?php if ($scenario['attempt_status']): ?>
                                                                    <?php if ($scenario['attempt_status'] === 'completed'): ?>
                                                                        <span class="badge badge-info">Đã nộp</span>
                                                                    <?php elseif ($scenario['attempt_status'] === 'graded'): ?>
                                                                        <span class="badge badge-success">Điểm: <?= $scenario['score'] ?></span>
                                                                    <?php endif; ?>
                                                                <?php else: ?>
                                                                    <span class="badge badge-secondary">Chưa làm</span>
                                                                <?php endif; ?>
                                                            </small>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php if (count($myScenarios) > 5): ?>
                                    <div class="card-footer">
                                        <a href="<?= url('modules/student/scenarios.php') ?>" class="btn btn-primary btn-sm">
                                            Xem tất cả
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Thao tác nhanh</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <a href="<?= url('modules/pms/guests.php') ?>" class="btn btn-app btn-block">
                                        <i class="fas fa-address-book"></i> Quản lý khách
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('modules/pms/reservations.php') ?>" class="btn btn-app btn-block">
                                        <i class="fas fa-calendar-check"></i> Đặt phòng
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('modules/pms/checkin.php') ?>" class="btn btn-app btn-block">
                                        <i class="fas fa-sign-in-alt"></i> Check-in
                                    </a>
                                </div>
                                <div class="col-md-3">
                                    <a href="<?= url('modules/pms/checkout.php') ?>" class="btn btn-app btn-block">
                                        <i class="fas fa-sign-out-alt"></i> Check-out
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
