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

$pageTitle = 'Báo cáo';
$currentPage = 'reports';

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
                    Module báo cáo đang được phát triển. Sẽ bao gồm: Occupancy Report, Revenue Report, Guest History.
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Occupancy Report</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Báo cáo tỷ lệ lấp đầy theo ngày/tháng</p>
                                <button class="btn btn-primary btn-sm" disabled>Xem báo cáo (Coming soon)</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Revenue Report</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Báo cáo doanh thu theo phòng/dịch vụ</p>
                                <button class="btn btn-primary btn-sm" disabled>Xem báo cáo (Coming soon)</button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Guest History</h3>
                            </div>
                            <div class="card-body">
                                <p class="text-muted">Lịch sử lưu trú của khách</p>
                                <button class="btn btn-primary btn-sm" disabled>Xem báo cáo (Coming soon)</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
