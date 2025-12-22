    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="<?= url() ?>" class="brand-link">
            <i class="fas fa-hotel brand-image ml-2"></i>
            <span class="brand-text font-weight-light">Hotel PMS Training</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    <i class="fas fa-user-circle fa-2x text-white"></i>
                </div>
                <div class="info">
                    <a href="#" class="d-block"><?= h(userName()) ?></a>
                    <small class="text-muted text-uppercase"><?= h(userRole()) ?></small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                    <!-- Dashboard -->
                    <li class="nav-item">
                        <a href="<?= url() ?>" class="nav-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
                            <i class="nav-icon fas fa-tachometer-alt"></i>
                            <p>Dashboard</p>
                        </a>
                    </li>

                    <?php if (userRole() === 'admin'): ?>
                        <!-- Admin Menu -->
                        <li class="nav-header">QUẢN TRỊ HỆ THỐNG</li>

                        <li class="nav-item">
                            <a href="<?= url('modules/admin/users.php') ?>" class="nav-link <?= $currentPage === 'users' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Quản lý người dùng</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/admin/classes.php') ?>" class="nav-link <?= $currentPage === 'classes' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-school"></i>
                                <p>Quản lý lớp học</p>
                            </a>
                        </li>

                    <?php elseif (userRole() === 'teacher'): ?>
                        <!-- Teacher Menu -->
                        <li class="nav-header">GIẢNG VIÊN</li>

                        <li class="nav-item">
                            <a href="<?= url('modules/teacher/classes.php') ?>" class="nav-link <?= $currentPage === 'classes' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-school"></i>
                                <p>Lớp học của tôi</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/teacher/students.php') ?>" class="nav-link <?= $currentPage === 'students' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-user-graduate"></i>
                                <p>Sinh viên</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/teacher/scenarios.php') ?>" class="nav-link <?= $currentPage === 'scenarios' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tasks"></i>
                                <p>Kịch bản thực hành</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/teacher/grading.php') ?>" class="nav-link <?= $currentPage === 'grading' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-graduation-cap"></i>
                                <p>Chấm điểm</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/teacher/generator.php') ?>" class="nav-link <?= $currentPage === 'generator' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-database"></i>
                                <p>Tạo dữ liệu mẫu</p>
                            </a>
                        </li>

                    <?php elseif (userRole() === 'student'): ?>
                        <!-- Student Menu -->
                        <li class="nav-header">SINH VIÊN</li>

                        <li class="nav-item">
                            <a href="<?= url('modules/student/scenarios.php') ?>" class="nav-link <?= $currentPage === 'scenarios' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-tasks"></i>
                                <p>Bài tập của tôi</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/student/progress.php') ?>" class="nav-link <?= $currentPage === 'progress' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-line"></i>
                                <p>Tiến trình học tập</p>
                            </a>
                        </li>

                    <?php endif; ?>

                    <?php if (userRole() !== 'admin'): ?>
                        <!-- PMS Operations (Teacher & Student) -->
                        <li class="nav-header">THAO TÁC PMS</li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/guests.php') ?>" class="nav-link <?= $currentPage === 'guests' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-address-book"></i>
                                <p>Quản lý khách</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/rooms.php') ?>" class="nav-link <?= $currentPage === 'rooms' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-bed"></i>
                                <p>Quản lý phòng</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/reservations.php') ?>" class="nav-link <?= $currentPage === 'reservations' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-calendar-check"></i>
                                <p>Đặt phòng</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/checkin.php') ?>" class="nav-link <?= $currentPage === 'checkin' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-sign-in-alt"></i>
                                <p>Check-in</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/checkout.php') ?>" class="nav-link <?= $currentPage === 'checkout' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Check-out</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="<?= url('modules/pms/reports.php') ?>" class="nav-link <?= $currentPage === 'reports' ? 'active' : '' ?>">
                                <i class="nav-icon fas fa-chart-bar"></i>
                                <p>Báo cáo</p>
                            </a>
                        </li>
                    <?php endif; ?>

                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>
