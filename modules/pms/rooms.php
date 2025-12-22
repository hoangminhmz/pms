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

$pageTitle = 'Quản lý phòng';
$currentPage = 'rooms';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $roomId = intval($_POST['room_id'] ?? 0);

    if (canAccessRecord('rooms', $roomId)) {
        if ($action === 'update_hk_status') {
            $hkStatus = clean($_POST['hk_status'] ?? '');
            execute("UPDATE rooms SET hk_status = ? WHERE id = ?", [$hkStatus, $roomId]);
            logActivity('update_room_status', 'pms', "Updated HK status to $hkStatus");
            setFlash('success', 'Cập nhật trạng thái thành công');
        } elseif ($action === 'update_condition') {
            $condition = clean($_POST['condition'] ?? '');
            execute("UPDATE rooms SET room_condition = ? WHERE id = ?", [$condition, $roomId]);
            logActivity('update_room_condition', 'pms', "Updated room condition to $condition");
            setFlash('success', 'Cập nhật tình trạng phòng thành công');
        }
    }

    redirect(url('modules/pms/rooms.php'));
}

// Get rooms with type info
$filter = clean($_GET['filter'] ?? 'all');
$floor = clean($_GET['floor'] ?? '');

$sql = "SELECT r.*, rt.name as room_type_name, rt.code as room_type_code
        FROM rooms r
        INNER JOIN room_types rt ON r.room_type_id = rt.id
        WHERE r.class_id = ?";

$params = [currentClassId()];

if ($filter === 'vacant') {
    $sql .= " AND r.fo_status = 'vacant' AND r.room_condition = 'available'";
} elseif ($filter === 'occupied') {
    $sql .= " AND r.fo_status = 'occupied'";
} elseif ($filter === 'dirty') {
    $sql .= " AND r.hk_status = 'dirty'";
} elseif ($filter === 'ooo') {
    $sql .= " AND r.room_condition = 'ooo'";
}

if ($floor) {
    $sql .= " AND r.floor = ?";
    $params[] = $floor;
}

$sql .= " ORDER BY r.room_number";

$rooms = fetchAll($sql, $params);

// Get floor list
$floors = fetchAll("SELECT DISTINCT floor FROM rooms WHERE class_id = ? ORDER BY floor", [currentClassId()]);

// Get stats
$stats = [
    'total' => fetchValue("SELECT COUNT(*) FROM rooms WHERE class_id = ?", [currentClassId()]),
    'vacant' => fetchValue("SELECT COUNT(*) FROM rooms WHERE class_id = ? AND fo_status = 'vacant' AND room_condition = 'available'", [currentClassId()]),
    'occupied' => fetchValue("SELECT COUNT(*) FROM rooms WHERE class_id = ? AND fo_status = 'occupied'", [currentClassId()]),
    'dirty' => fetchValue("SELECT COUNT(*) FROM rooms WHERE class_id = ? AND hk_status = 'dirty'", [currentClassId()]),
];

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
                        <h1 class="m-0"><?= $pageTitle ?></h1>
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

                <!-- Stats -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-info"><i class="fas fa-bed"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Tổng phòng</span>
                                <span class="info-box-number"><?= $stats['total'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-success"><i class="fas fa-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Vacant Clean</span>
                                <span class="info-box-number"><?= $stats['vacant'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-danger"><i class="fas fa-user"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Occupied</span>
                                <span class="info-box-number"><?= $stats['occupied'] ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-6">
                        <div class="info-box">
                            <span class="info-box-icon bg-warning"><i class="fas fa-broom"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text">Dirty</span>
                                <span class="info-box-number"><?= $stats['dirty'] ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Room Status</h3>
                    </div>
                    <div class="card-body">
                        <!-- Filter Form -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="filter" class="form-control">
                                        <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Tất cả phòng</option>
                                        <option value="vacant" <?= $filter === 'vacant' ? 'selected' : '' ?>>Vacant Clean</option>
                                        <option value="occupied" <?= $filter === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                        <option value="dirty" <?= $filter === 'dirty' ? 'selected' : '' ?>>Dirty</option>
                                        <option value="ooo" <?= $filter === 'ooo' ? 'selected' : '' ?>>Out of Order</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="floor" class="form-control">
                                        <option value="">Tất cả tầng</option>
                                        <?php foreach ($floors as $f): ?>
                                            <option value="<?= $f['floor'] ?>" <?= $floor == $f['floor'] ? 'selected' : '' ?>>
                                                Tầng <?= $f['floor'] ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Lọc</button>
                                </div>
                            </div>
                        </form>

                        <!-- Rooms Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Phòng</th>
                                        <th>Tầng</th>
                                        <th>Loại</th>
                                        <th>FO Status</th>
                                        <th>HK Status</th>
                                        <th>Condition</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($rooms)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Chưa có phòng</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($rooms as $room): ?>
                                            <tr>
                                                <td><strong><?= h($room['room_number']) ?></strong></td>
                                                <td><?= $room['floor'] ?></td>
                                                <td><?= h($room['room_type_name']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $room['fo_status'] === 'vacant' ? 'success' : 'danger' ?>">
                                                        <?= strtoupper($room['fo_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $hkColors = ['clean' => 'success', 'dirty' => 'warning', 'inspected' => 'info'];
                                                    ?>
                                                    <span class="badge badge-<?= $hkColors[$room['hk_status']] ?>">
                                                        <?= strtoupper($room['hk_status']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $condColors = ['available' => 'success', 'ooo' => 'danger', 'oos' => 'secondary'];
                                                    ?>
                                                    <span class="badge badge-<?= $condColors[$room['room_condition']] ?>">
                                                        <?= strtoupper($room['room_condition']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-toggle="dropdown">
                                                            Cập nhật
                                                        </button>
                                                        <div class="dropdown-menu">
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_hk_status">
                                                                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                <input type="hidden" name="hk_status" value="clean">
                                                                <button type="submit" class="dropdown-item">HK: Clean</button>
                                                            </form>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_hk_status">
                                                                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                <input type="hidden" name="hk_status" value="dirty">
                                                                <button type="submit" class="dropdown-item">HK: Dirty</button>
                                                            </form>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_hk_status">
                                                                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                <input type="hidden" name="hk_status" value="inspected">
                                                                <button type="submit" class="dropdown-item">HK: Inspected</button>
                                                            </form>
                                                            <div class="dropdown-divider"></div>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_condition">
                                                                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                <input type="hidden" name="condition" value="ooo">
                                                                <button type="submit" class="dropdown-item text-danger">Set OOO</button>
                                                            </form>
                                                            <form method="POST">
                                                                <input type="hidden" name="action" value="update_condition">
                                                                <input type="hidden" name="room_id" value="<?= $room['id'] ?>">
                                                                <input type="hidden" name="condition" value="available">
                                                                <button type="submit" class="dropdown-item text-success">Set Available</button>
                                                            </form>
                                                        </div>
                                                    </div>
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
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
