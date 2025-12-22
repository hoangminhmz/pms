<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('teacher');

if (!currentClassId()) {
    setFlash('error', 'Vui lòng chọn lớp học');
    redirect(url('modules/teacher/dashboard.php'));
}

$pageTitle = 'Quản lý sinh viên';
$currentPage = 'students';

// Get students in current class
$students = fetchAll("
    SELECT u.*, cs.joined_at,
           (SELECT COUNT(*) FROM scenario_attempts sa WHERE sa.student_id = u.id) as attempts_count
    FROM users u
    INNER JOIN class_students cs ON u.id = cs.student_id
    WHERE cs.class_id = ?
    ORDER BY u.name
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
                    Module quản lý sinh viên đang được phát triển. Sẽ có thể: thêm/xóa sinh viên, xem activity logs.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách sinh viên</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Họ tên</th>
                                    <th>Email</th>
                                    <th>Mã SV</th>
                                    <th>Tham gia</th>
                                    <th>Số bài làm</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Chưa có sinh viên</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?= h($student['name']) ?></td>
                                            <td><?= h($student['email']) ?></td>
                                            <td><?= h($student['student_code'] ?? '-') ?></td>
                                            <td><?= formatDateTime($student['joined_at']) ?></td>
                                            <td><?= $student['attempts_count'] ?></td>
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
