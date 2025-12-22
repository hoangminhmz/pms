<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('admin');

$pageTitle = 'Quản lý lớp học';
$currentPage = 'classes';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = clean($_POST['name'] ?? '');
        $code = strtoupper(clean($_POST['code'] ?? ''));
        $teacherId = intval($_POST['teacher_id'] ?? 0);
        $description = clean($_POST['description'] ?? '');
        $startDate = clean($_POST['start_date'] ?? '');
        $endDate = clean($_POST['end_date'] ?? '');

        if (empty($name) || empty($code) || empty($teacherId)) {
            setFlash('error', 'Vui lòng điền đầy đủ thông tin');
        } else {
            // Check if code exists
            if (fetchOne("SELECT id FROM school_classes WHERE code = ?", [$code])) {
                setFlash('error', 'Mã lớp đã tồn tại');
            } else {
                $sql = "INSERT INTO school_classes (name, code, teacher_id, description, start_date, end_date, is_active, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, 1, ?)";
                execute($sql, [$name, $code, $teacherId, $description, $startDate ?: null, $endDate ?: null, now()]);

                setFlash('success', 'Tạo lớp học thành công');
                redirect(url('modules/admin/classes.php'));
            }
        }
    } elseif ($action === 'toggle_status') {
        $classId = intval($_POST['class_id'] ?? 0);
        $newStatus = intval($_POST['new_status'] ?? 0);

        execute("UPDATE school_classes SET is_active = ? WHERE id = ?", [$newStatus, $classId]);
        setFlash('success', 'Cập nhật trạng thái thành công');
        redirect(url('modules/admin/classes.php'));
    } elseif ($action === 'delete') {
        $classId = intval($_POST['class_id'] ?? 0);

        execute("DELETE FROM school_classes WHERE id = ?", [$classId]);
        setFlash('success', 'Xóa lớp học thành công');
        redirect(url('modules/admin/classes.php'));
    }
}

// Get classes with teacher info
$sql = "SELECT sc.*, u.name as teacher_name,
        (SELECT COUNT(*) FROM class_students WHERE class_id = sc.id) as student_count
        FROM school_classes sc
        LEFT JOIN users u ON sc.teacher_id = u.id
        ORDER BY sc.created_at DESC";

$classes = fetchAll($sql);

// Get teachers for dropdown
$teachers = fetchAll("SELECT id, name, email FROM users WHERE role = 'teacher' AND is_active = 1 ORDER BY name");

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
                            <i class="fas fa-plus"></i> Tạo lớp học
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
                        <h3 class="card-title">Danh sách lớp học</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Mã lớp</th>
                                    <th>Tên lớp</th>
                                    <th>Giảng viên</th>
                                    <th>SV</th>
                                    <th>Thời gian</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($classes)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Chưa có lớp học</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($classes as $class): ?>
                                        <tr>
                                            <td><strong><?= h($class['code']) ?></strong></td>
                                            <td><?= h($class['name']) ?></td>
                                            <td><?= h($class['teacher_name']) ?></td>
                                            <td><?= $class['student_count'] ?></td>
                                            <td>
                                                <?php if ($class['start_date'] && $class['end_date']): ?>
                                                    <?= formatDate($class['start_date']) ?> - <?= formatDate($class['end_date']) ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
                                                    <input type="hidden" name="new_status" value="<?= $class['is_active'] ? 0 : 1 ?>">
                                                    <button type="submit" class="btn btn-sm btn-<?= $class['is_active'] ? 'success' : 'secondary' ?>">
                                                        <?= $class['is_active'] ? 'Hoạt động' : 'Ngừng' ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td>
                                                <form method="POST" style="display:inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="class_id" value="<?= $class['id'] ?>">
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
        </section>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tạo lớp học mới</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Tên lớp <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required placeholder="VD: Quản trị Khách sạn K19">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Mã lớp <span class="text-danger">*</span></label>
                                    <input type="text" name="code" class="form-control" required placeholder="VD: HM-K19-A">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Giảng viên <span class="text-danger">*</span></label>
                            <select name="teacher_id" class="form-control" required>
                                <option value="">-- Chọn giảng viên --</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>"><?= h($teacher['name']) ?> (<?= h($teacher['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Mô tả</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Mô tả về lớp học..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày bắt đầu</label>
                                    <input type="date" name="start_date" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Ngày kết thúc</label>
                                    <input type="date" name="end_date" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo lớp</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
