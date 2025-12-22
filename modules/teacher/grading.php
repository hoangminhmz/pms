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

$pageTitle = 'Chấm điểm';
$currentPage = 'grading';

// Get pending submissions
$submissions = fetchAll("
    SELECT sa.*, s.title as scenario_title, s.max_score, u.name as student_name
    FROM scenario_attempts sa
    INNER JOIN scenarios s ON sa.scenario_id = s.id
    INNER JOIN users u ON sa.student_id = u.id
    WHERE s.class_id = ? AND sa.status IN ('completed', 'graded')
    ORDER BY sa.completed_at DESC
    LIMIT 50
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
                    Module chấm điểm đang được phát triển. Giảng viên sẽ có thể: xem bài làm, chấm điểm, viết feedback.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Bài nộp cần chấm</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Sinh viên</th>
                                    <th>Kịch bản</th>
                                    <th>Nộp lúc</th>
                                    <th>Thời gian</th>
                                    <th>Điểm</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($submissions)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Chưa có bài nộp</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($submissions as $sub): ?>
                                        <tr>
                                            <td><?= h($sub['student_name']) ?></td>
                                            <td><?= h($sub['scenario_title']) ?></td>
                                            <td><?= formatDateTime($sub['completed_at']) ?></td>
                                            <td><?= $sub['time_spent'] ? $sub['time_spent'] . ' phút' : '-' ?></td>
                                            <td>
                                                <?php if ($sub['score']): ?>
                                                    <?= $sub['score'] ?>/<?= $sub['max_score'] ?>
                                                <?php else: ?>
                                                    -
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($sub['status'] === 'graded'): ?>
                                                    <span class="badge badge-success">Đã chấm</span>
                                                <?php else: ?>
                                                    <span class="badge badge-warning">Chờ chấm</span>
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

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
