<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';

requireRole('admin');

$pageTitle = 'Quản lý người dùng';
$currentPage = 'users';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = clean($_POST['name'] ?? '');
        $email = clean($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = clean($_POST['role'] ?? 'student');
        $studentCode = clean($_POST['student_code'] ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            setFlash('error', 'Vui lòng điền đầy đủ thông tin');
        } else {
            $result = register($name, $email, $password, $role, $studentCode);
            if ($result['success']) {
                setFlash('success', 'Tạo người dùng thành công');
                redirect(url('modules/admin/users.php'));
            } else {
                setFlash('error', $result['message']);
            }
        }
    } elseif ($action === 'toggle_status') {
        $userId = intval($_POST['user_id'] ?? 0);
        $newStatus = intval($_POST['new_status'] ?? 0);

        execute("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $userId]);
        setFlash('success', 'Cập nhật trạng thái thành công');
        redirect(url('modules/admin/users.php'));
    } elseif ($action === 'delete') {
        $userId = intval($_POST['user_id'] ?? 0);

        // Cannot delete self
        if ($userId == userId()) {
            setFlash('error', 'Không thể xóa tài khoản của chính mình');
        } else {
            execute("DELETE FROM users WHERE id = ?", [$userId]);
            setFlash('success', 'Xóa người dùng thành công');
        }
        redirect(url('modules/admin/users.php'));
    }
}

// Get users
$search = clean($_GET['search'] ?? '');
$role = clean($_GET['role'] ?? '');

$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($search) {
    $sql .= " AND (name LIKE ? OR email LIKE ? OR student_code LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($role) {
    $sql .= " AND role = ?";
    $params[] = $role;
}

$sql .= " ORDER BY created_at DESC";

$users = fetchAll($sql, $params);

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
                            <i class="fas fa-plus"></i> Thêm người dùng
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
                        <h3 class="card-title">Danh sách người dùng</h3>
                    </div>
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" class="mb-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." value="<?= h($search) ?>">
                                </div>
                                <div class="col-md-3">
                                    <select name="role" class="form-control">
                                        <option value="">Tất cả vai trò</option>
                                        <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                                        <option value="teacher" <?= $role === 'teacher' ? 'selected' : '' ?>>Giảng viên</option>
                                        <option value="student" <?= $role === 'student' ? 'selected' : '' ?>>Sinh viên</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                </div>
                            </div>
                        </form>

                        <!-- Users Table -->
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Mã SV</th>
                                    <th>Trạng thái</th>
                                    <th>Đăng nhập cuối</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Không có dữ liệu</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= h($user['name']) ?></td>
                                            <td><?= h($user['email']) ?></td>
                                            <td>
                                                <?php
                                                $badges = ['admin' => 'danger', 'teacher' => 'primary', 'student' => 'success'];
                                                $roleNames = ['admin' => 'Admin', 'teacher' => 'Giảng viên', 'student' => 'Sinh viên'];
                                                ?>
                                                <span class="badge badge-<?= $badges[$user['role']] ?>">
                                                    <?= $roleNames[$user['role']] ?>
                                                </span>
                                            </td>
                                            <td><?= h($user['student_code'] ?? '-') ?></td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?= $user['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-sm btn-<?= $user['is_active'] ? 'success' : 'secondary' ?>">
                                                        <?= $user['is_active'] ? 'Hoạt động' : 'Ngừng' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td><?= $user['last_login_at'] ? formatDateTime($user['last_login_at']) : '-' ?></td>
                                            <td>
                                                <?php if ($user['id'] != userId()): ?>
                                                    <form method="POST" style="display:inline;">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-danger delete-confirm">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
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
        </section>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Thêm người dùng</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">

                        <div class="form-group">
                            <label>Họ và tên <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Mật khẩu <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Vai trò <span class="text-danger">*</span></label>
                            <select name="role" class="form-control" required id="roleSelect">
                                <option value="student">Sinh viên</option>
                                <option value="teacher">Giảng viên</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>

                        <div class="form-group" id="studentCodeGroup">
                            <label>Mã sinh viên</label>
                            <input type="text" name="student_code" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    $('#roleSelect').on('change', function() {
        if ($(this).val() === 'student') {
            $('#studentCodeGroup').show();
        } else {
            $('#studentCodeGroup').hide();
        }
    });
    </script>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
