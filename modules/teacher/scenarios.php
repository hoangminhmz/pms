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

$pageTitle = 'Kịch bản thực hành';
$currentPage = 'scenarios';

// Get scenarios for current class and templates
$scenarios = fetchAll("
    SELECT * FROM scenarios
    WHERE class_id = ? OR is_template = 1
    ORDER BY is_template DESC, created_at DESC
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
                    Module quản lý kịch bản đang được phát triển. Giảng viên có thể: tạo kịch bản mới, clone template, chỉnh sửa.
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Danh sách kịch bản</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Loại</th>
                                    <th>Độ khó</th>
                                    <th>Điểm</th>
                                    <th>Template</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($scenarios)): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Chưa có kịch bản</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($scenarios as $scenario): ?>
                                        <tr>
                                            <td><?= h($scenario['title']) ?></td>
                                            <td><?= ucfirst($scenario['type']) ?></td>
                                            <td>
                                                <?php
                                                $diffBadges = ['easy' => 'success', 'medium' => 'warning', 'hard' => 'danger'];
                                                ?>
                                                <span class="badge badge-<?= $diffBadges[$scenario['difficulty']] ?>">
                                                    <?= ucfirst($scenario['difficulty']) ?>
                                                </span>
                                            </td>
                                            <td><?= $scenario['max_score'] ?></td>
                                            <td><?= $scenario['is_template'] ? 'Yes' : 'No' ?></td>
                                            <td>
                                                <?= $scenario['is_active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Inactive</span>' ?>
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
