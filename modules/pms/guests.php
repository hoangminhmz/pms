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

$pageTitle = 'Quản lý khách';
$currentPage = 'guests';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $firstName = clean($_POST['first_name'] ?? '');
        $lastName = clean($_POST['last_name'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $phone = clean($_POST['phone'] ?? '');
        $idType = clean($_POST['id_type'] ?? 'cccd');
        $idNumber = clean($_POST['id_number'] ?? '');
        $nationality = clean($_POST['nationality'] ?? 'Vietnam');
        $gender = clean($_POST['gender'] ?? '');

        if (empty($firstName) || empty($lastName) || empty($idNumber)) {
            setFlash('error', 'Vui lòng điền đầy đủ thông tin bắt buộc');
        } else {
            $fullName = $firstName . ' ' . $lastName;
            $profileId = generateProfileId();

            $sql = "INSERT INTO guests (class_id, profile_id, first_name, last_name, full_name, email, phone, id_type, id_number, nationality, gender, country, vip_level, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)";

            execute($sql, [
                currentClassId(),
                $profileId,
                $firstName,
                $lastName,
                $fullName,
                $email,
                $phone,
                $idType,
                $idNumber,
                $nationality,
                $gender,
                $nationality,
                now()
            ]);

            logActivity('create_guest', 'pms', "Created guest profile: $fullName ($profileId)");

            setFlash('success', "Tạo hồ sơ khách thành công! Profile ID: $profileId");
            redirect(url('modules/pms/guests.php'));
        }
    } elseif ($action === 'delete') {
        $guestId = intval($_POST['guest_id'] ?? 0);

        if (canAccessRecord('guests', $guestId)) {
            execute("DELETE FROM guests WHERE id = ?", [$guestId]);
            logActivity('delete_guest', 'pms', "Deleted guest profile");
            setFlash('success', 'Xóa hồ sơ khách thành công');
        } else {
            setFlash('error', 'Không có quyền truy cập');
        }
        redirect(url('modules/pms/guests.php'));
    }
}

// Get guests
$search = clean($_GET['search'] ?? '');
$nationality = clean($_GET['nationality'] ?? '');

$sql = "SELECT * FROM guests WHERE class_id = ?";
$params = [currentClassId()];

if ($search) {
    $sql .= " AND (full_name LIKE ? OR profile_id LIKE ? OR email LIKE ? OR phone LIKE ? OR id_number LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($nationality) {
    $sql .= " AND nationality = ?";
    $params[] = $nationality;
}

$sql .= " ORDER BY created_at DESC LIMIT 100";

$guests = fetchAll($sql, $params);

// Get nationalities for filter
$nationalities = fetchAll("SELECT DISTINCT nationality FROM guests WHERE class_id = ? ORDER BY nationality", [currentClassId()]);

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
                    <div class="col-sm-6">
                        <button type="button" class="btn btn-primary float-right" data-toggle="modal" data-target="#createModal">
                            <i class="fas fa-plus"></i> Tạo hồ sơ khách
                        </button>
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
                <?php if (hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        <?= getFlash('error') ?>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách khách hàng</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="text" name="search" class="form-control" placeholder="Tìm theo tên, Profile ID, email, phone..." value="<?= h($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="nationality" class="form-control">
                                        <option value="">Tất cả quốc tịch</option>
                                        <?php foreach ($nationalities as $nat): ?>
                                            <option value="<?= h($nat['nationality']) ?>" <?= $nationality === $nat['nationality'] ? 'selected' : '' ?>>
                                                <?= h($nat['nationality']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                </div>
                            </div>
                        </form>

                        <!-- Guests Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Profile ID</th>
                                        <th>Họ tên</th>
                                        <th>Email / Phone</th>
                                        <th>ID Type / Number</th>
                                        <th>Quốc tịch</th>
                                        <th>VIP</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($guests)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Chưa có khách hàng</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($guests as $guest): ?>
                                            <tr>
                                                <td><strong><?= h($guest['profile_id']) ?></strong></td>
                                                <td>
                                                    <?= h($guest['full_name']) ?>
                                                    <?php if ($guest['gender']): ?>
                                                        <small class="text-muted">(<?= ucfirst($guest['gender']) ?>)</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= h($guest['email'] ?? '-') ?><br>
                                                    <small><?= h($guest['phone'] ?? '-') ?></small>
                                                </td>
                                                <td>
                                                    <?= strtoupper($guest['id_type']) ?>: <?= h($guest['id_number']) ?>
                                                </td>
                                                <td><?= h($guest['nationality']) ?></td>
                                                <td>
                                                    <?php if ($guest['vip_level'] > 0): ?>
                                                        <span class="badge badge-warning">VIP <?= $guest['vip_level'] ?></span>
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="guest_id" value="<?= $guest['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger delete-confirm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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

    <!-- Create Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tạo hồ sơ khách mới</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="first_name" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="last_name" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ID Type <span class="text-danger">*</span></label>
                                    <select name="id_type" class="form-control" required>
                                        <option value="cccd">CCCD</option>
                                        <option value="cmnd">CMND</option>
                                        <option value="passport">Passport</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ID Number <span class="text-danger">*</span></label>
                                    <input type="text" name="id_number" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-control">
                                        <option value="">-</option>
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nationality</label>
                            <input type="text" name="nationality" class="form-control" value="Vietnam">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo hồ sơ</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
