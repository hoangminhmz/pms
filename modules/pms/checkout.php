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

$pageTitle = 'Check-out';
$currentPage = 'checkout';

// Get in-house guests
$inHouse = fetchAll("
    SELECT r.*, g.full_name, g.profile_id, rm.room_number, rt.name as room_type_name
    FROM reservations r
    INNER JOIN guests g ON r.guest_id = g.id
    LEFT JOIN rooms rm ON r.room_id = rm.id
    LEFT JOIN room_types rt ON r.room_type_id = rt.id
    WHERE r.class_id = ? AND r.status = 'checked_in'
    ORDER BY r.departure_date
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
                    Module check-out đang được phát triển. Sẽ bao gồm: review folio, settle payment, release room.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Khách trong nhà</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Phòng</th>
                                    <th>Tên khách</th>
                                    <th>Check-in</th>
                                    <th>Departure</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($inHouse)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Không có khách trong nhà</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($inHouse as $guest): ?>
                                        <tr>
                                            <td><strong><?= h($guest['room_number'] ?? '-') ?></strong></td>
                                            <td><?= h($guest['full_name']) ?></td>
                                            <td><?= formatDate($guest['arrival_date']) ?></td>
                                            <td><?= formatDate($guest['departure_date']) ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-primary" disabled>
                                                    <i class="fas fa-sign-out-alt"></i> Check-out (Coming soon)
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
