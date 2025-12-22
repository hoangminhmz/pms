<?php
require_once __DIR__ . '/../../config/app.php';
require_once __DIR__ . '/../../core/db.php';
require_once __DIR__ . '/../../core/functions.php';
require_once __DIR__ . '/../../core/auth.php';
require_once __DIR__ . '/../../core/isolation.php';

requireRole('student');

if (!currentClassId()) {
    setFlash('error', 'Vui lòng chọn lớp học');
    redirect(url());
}

$pageTitle = 'Kịch bản thực hành';
$currentPage = 'scenarios';

// Get scenarios for this class
$scenarios = fetchAll("
    SELECT s.*, sa.status as attempt_status, sa.score, sa.completed_at
    FROM scenarios s
    LEFT JOIN scenario_attempts sa ON s.id = sa.scenario_id AND sa.student_id = ?
    WHERE s.class_id = ? AND s.is_active = 1
    ORDER BY s.created_at DESC
", [userId(), currentClassId()]);

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
                    Module kịch bản thực hành đang được phát triển. Sinh viên sẽ làm bài tập theo kịch bản do giảng viên tạo.
                </div>

                <div class="row">
                    <?php if (empty($scenarios)): ?>
                        <div class="col-12">
                            <div class="alert alert-warning">
                                Chưa có kịch bản thực hành. Giảng viên sẽ tạo kịch bản cho bạn.
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($scenarios as $scenario): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title"><?= h($scenario['title']) ?></h3>
                                    </div>
                                    <div class="card-body">
                                        <p><?= nl2br(h($scenario['description'])) ?></p>

                                        <div class="mb-2">
                                            <?php
                                            $diffBadges = ['easy' => 'success', 'medium' => 'warning', 'hard' => 'danger'];
                                            ?>
                                            <span class="badge badge-<?= $diffBadges[$scenario['difficulty']] ?>">
                                                <?= ucfirst($scenario['difficulty']) ?>
                                            </span>
                                            <?php if ($scenario['time_limit']): ?>
                                                <span class="badge badge-info">
                                                    <i class="far fa-clock"></i> <?= $scenario['time_limit'] ?> phút
                                                </span>
                                            <?php endif; ?>
                                            <span class="badge badge-secondary">
                                                Max: <?= $scenario['max_score'] ?> điểm
                                            </span>
                                        </div>

                                        <?php if ($scenario['attempt_status']): ?>
                                            <?php if ($scenario['attempt_status'] === 'graded'): ?>
                                                <div class="alert alert-success mb-0">
                                                    <strong>Đã chấm điểm:</strong> <?= $scenario['score'] ?>/<?= $scenario['max_score'] ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="alert alert-info mb-0">
                                                    Đã nộp bài, chờ chấm điểm
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <button class="btn btn-primary btn-block" disabled>
                                                <i class="fas fa-play"></i> Bắt đầu (Coming soon)
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
