<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireAuth();

if (!currentClassId()) {
    setFlash('error', 'Vui lòng chọn lớp học');
    redirect(url());
}

$pageTitle = 'Quản lý đặt phòng';
$currentPage = 'reservations';

// Get reservations
$sql = "SELECT r.*, g.full_name, rt.name as room_type_name, rm.room_number
        FROM reservations r
        INNER JOIN guests g ON r.guest_id = g.id
        INNER JOIN room_types rt ON r.room_type_id = rt.id
        LEFT JOIN rooms rm ON r.room_id = rm.id
        WHERE r.class_id = ?
        ORDER BY r.arrival_date DESC, r.created_at DESC
        LIMIT 50";

$reservations = fetchAll($sql, [currentClassId()]);

require_once __DIR__ . '/../../templates/header.php';
require_once __DIR__ . '/../../templates/sidebar.php';
?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0"><?= $pageTitle ?></h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="alert alert-info">
                    <i class="icon fas fa-info"></i>
                    Module đặt phòng đang được phát triển. Hiện tại bạn có thể xem danh sách đặt phòng.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách đặt phòng</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Confirmation</th>
                                    <th>Khách</th>
                                    <th>Loại phòng</th>
                                    <th>Phòng</th>
                                    <th>Ngày</th>
                                    <th>Đêm</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reservations)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chưa có đặt phòng</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($reservations as $res): ?>
                                        <tr>
                                            <td><strong><?= h($res['confirmation_no']) ?></strong></td>
                                            <td><?= h($res['full_name']) ?></td>
                                            <td><?= h($res['room_type_name']) ?></td>
                                            <td><?= h($res['room_number'] ?? '-') ?></td>
                                            <td><?= formatDate($res['arrival_date']) ?> - <?= formatDate($res['departure_date']) ?></td>
                                            <td><?= $res['nights'] ?></td>
                                            <td><span class="badge badge-status-<?= $res['status'] ?>"><?= strtoupper($res['status']) ?></span></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
