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

$pageTitle = 'Check-in';
$currentPage = 'checkin';

// Get today's arrivals
$arrivals = fetchAll("
    SELECT r.*, g.full_name, g.profile_id, rt.name as room_type_name
    FROM reservations r
    INNER JOIN guests g ON r.guest_id = g.id
    INNER JOIN room_types rt ON r.room_type_id = rt.id
    WHERE r.class_id = ? AND r.arrival_date = CURDATE() AND r.status = 'confirmed'
    ORDER BY r.created_at
", [currentClassId()]);

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
                    Module check-in đang được phát triển. Sẽ bao gồm: assign room, collect deposit, create folio.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Arrivals hôm nay</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Profile ID</th>
                                    <th>Tên khách</th>
                                    <th>Loại phòng</th>
                                    <th>Đêm</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($arrivals)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Không có arrivals hôm nay</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($arrivals as $arrival): ?>
                                        <tr>
                                            <td><?= h($arrival['profile_id']) ?></td>
                                            <td><?= h($arrival['full_name']) ?></td>
                                            <td><?= h($arrival['room_type_name']) ?></td>
                                            <td><?= $arrival['nights'] ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" disabled>
                                                    <i class="fas fa-sign-in-alt"></i> Check-in (Coming soon)
                                                </button>
                                            </td>
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
