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

$pageTitle = 'Tạo dữ liệu mẫu';
$currentPage = 'generator';

// Handle generate action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $classId = currentClassId();

    if ($action === 'reset_all') {
        // Delete all class data
        execute("DELETE FROM folios WHERE class_id = ?", [$classId]);
        execute("DELETE FROM reservations WHERE class_id = ?", [$classId]);
        execute("DELETE FROM guests WHERE class_id = ?", [$classId]);
        execute("DELETE FROM rooms WHERE class_id = ?", [$classId]);
        execute("DELETE FROM room_types WHERE class_id = ?", [$classId]);

        setFlash('success', 'Đã xóa toàn bộ dữ liệu lớp');
        redirect(url('modules/teacher/generator.php'));
    } elseif ($action === 'generate') {
        $roomCount = intval($_POST['room_count'] ?? 50);
        $guestCount = intval($_POST['guest_count'] ?? 100);
        $reservationCount = intval($_POST['reservation_count'] ?? 30);

        // Vietnamese names database
        $firstNames = ['Anh', 'Bình', 'Chi', 'Dũng', 'Đức', 'Giang', 'Hà', 'Hải', 'Hằng', 'Hiếu', 'Hoa', 'Hoàng', 'Hùng', 'Hương', 'Khang', 'Khánh', 'Lan', 'Linh', 'Long', 'Mai', 'Minh', 'Nam', 'Nga', 'Ngọc', 'Nhung', 'Phong', 'Phúc', 'Quân', 'Quỳnh', 'Sơn', 'Tài', 'Thảo', 'Thư', 'Tiến', 'Trâm', 'Trang', 'Trinh', 'Trúc', 'Tú', 'Tùng', 'Vy', 'Yến'];
        $lastNames = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô', 'Dương', 'Lý'];

        // International names
        $intlFirstNames = ['John', 'Michael', 'David', 'James', 'Robert', 'William', 'Mary', 'Patricia', 'Jennifer', 'Linda', 'Thomas', 'Charles', 'Sarah', 'Jessica', 'Daniel'];
        $intlLastNames = ['Smith', 'Johnson', 'Brown', 'Davis', 'Wilson', 'Taylor', 'Anderson', 'Miller', 'White', 'Harris', 'Martin', 'Thompson', 'Garcia', 'Martinez', 'Robinson'];

        // 1. Generate Room Types
        $roomTypes = [
            ['code' => 'STD', 'name' => 'Standard Room', 'rack_rate' => 800000, 'default_rate' => 700000, 'max_adults' => 2, 'max_children' => 1],
            ['code' => 'SUP', 'name' => 'Superior Room', 'rack_rate' => 1200000, 'default_rate' => 1000000, 'max_adults' => 2, 'max_children' => 1],
            ['code' => 'DLX', 'name' => 'Deluxe Room', 'rack_rate' => 1800000, 'default_rate' => 1500000, 'max_adults' => 2, 'max_children' => 2],
            ['code' => 'SUT', 'name' => 'Suite', 'rack_rate' => 3000000, 'default_rate' => 2500000, 'max_adults' => 3, 'max_children' => 2],
        ];

        $roomTypeIds = [];
        foreach ($roomTypes as $rt) {
            $sql = "INSERT INTO room_types (class_id, code, name, rack_rate, default_rate, max_adults, max_children, is_active, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?)";
            execute($sql, [$classId, $rt['code'], $rt['name'], $rt['rack_rate'], $rt['default_rate'], $rt['max_adults'], $rt['max_children'], now()]);
            $roomTypeIds[$rt['code']] = lastInsertId();
        }

        // 2. Generate Rooms
        $floors = 5;
        $roomsPerFloor = ceil($roomCount / $floors);
        $foStatuses = ['vacant', 'occupied'];
        $hkStatuses = ['clean', 'dirty', 'inspected'];

        for ($floor = 1; $floor <= $floors; $floor++) {
            for ($room = 1; $room <= $roomsPerFloor; $room++) {
                $roomNumber = $floor . str_pad($room, 2, '0', STR_PAD_LEFT);
                $typeCode = array_rand($roomTypeIds);
                $roomTypeId = $roomTypeIds[$typeCode];

                $sql = "INSERT INTO rooms (class_id, room_type_id, room_number, floor, fo_status, hk_status, room_condition, created_at)
                        VALUES (?, ?, ?, ?, 'vacant', ?, 'available', ?)";
                execute($sql, [$classId, $roomTypeId, $roomNumber, $floor, $hkStatuses[array_rand($hkStatuses)], now()]);
            }
        }

        // 3. Generate Guests
        for ($i = 1; $i <= $guestCount; $i++) {
            $isVietnamese = $i <= ($guestCount * 0.7); // 70% Vietnamese

            if ($isVietnamese) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $nationality = 'Vietnam';
                $country = 'Vietnam';
                $idType = 'cccd';
            } else {
                $firstName = $intlFirstNames[array_rand($intlFirstNames)];
                $lastName = $intlLastNames[array_rand($intlLastNames)];
                $nationality = ['USA', 'UK', 'Australia', 'Canada', 'France', 'Germany'][array_rand(['USA', 'UK', 'Australia', 'Canada', 'France', 'Germany'])];
                $country = $nationality;
                $idType = 'passport';
            }

            $fullName = $firstName . ' ' . $lastName;
            $email = strtolower(str_replace(' ', '', $firstName . $lastName . rand(1, 999) . '@example.com'));
            $phone = '09' . rand(10000000, 99999999);
            $idNumber = $idType === 'passport' ? strtoupper(substr($country, 0, 2)) . rand(100000, 999999) : rand(100000000000, 999999999999);
            $gender = ['male', 'female'][array_rand(['male', 'female'])];
            $vipLevel = rand(0, 3);

            $profileId = generateProfileId();

            $sql = "INSERT INTO guests (class_id, profile_id, first_name, last_name, full_name, email, phone, id_type, id_number, nationality, gender, country, vip_level, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            execute($sql, [$classId, $profileId, $firstName, $lastName, $fullName, $email, $phone, $idType, $idNumber, $nationality, $gender, $country, $vipLevel, now()]);
        }

        // 4. Generate Reservations
        $guests = fetchAll("SELECT id FROM guests WHERE class_id = ? ORDER BY RAND() LIMIT ?", [$classId, $reservationCount]);
        $rooms = fetchAll("SELECT id, room_type_id FROM rooms WHERE class_id = ? ORDER BY RAND()", [$classId]);
        $roomTypesList = fetchAll("SELECT id, default_rate FROM room_types WHERE class_id = ?", [$classId]);

        $statuses = ['confirmed', 'checked_in', 'checked_out', 'cancelled'];
        $sources = ['direct', 'phone', 'email', 'website'];

        foreach ($guests as $index => $guest) {
            $arrivalDate = date('Y-m-d', strtotime('+' . rand(-30, 30) . ' days'));
            $nights = rand(1, 7);
            $departureDate = date('Y-m-d', strtotime($arrivalDate . ' +' . $nights . ' days'));

            $room = $rooms[array_rand($rooms)];
            $roomType = array_filter($roomTypesList, fn($rt) => $rt['id'] == $room['room_type_id']);
            $roomType = reset($roomType);

            $rate = $roomType['default_rate'];
            $totalAmount = $rate * $nights;

            $status = $statuses[array_rand($statuses)];
            $source = $sources[array_rand($sources)];
            $confirmationNo = generateConfirmationNo();

            $sql = "INSERT INTO reservations (class_id, confirmation_no, guest_id, room_type_id, room_id, arrival_date, departure_date, nights, adults, children, rate, total_amount, status, source, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            execute($sql, [$classId, $confirmationNo, $guest['id'], $room['room_type_id'], $room['id'], $arrivalDate, $departureDate, $nights, rand(1, 2), rand(0, 1), $rate, $totalAmount, $status, $source, now()]);
        }

        setFlash('success', "Đã tạo thành công $roomCount phòng, $guestCount khách, và $reservationCount đặt phòng!");
        redirect(url('modules/teacher/generator.php'));
    }
}

// Get current data stats
$stats = null;
if (currentClassId()) {
    $stats = getClassStats(currentClassId());
}

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

                <!-- Current Stats -->
                <?php if ($stats): ?>
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-bed"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Phòng</span>
                                    <span class="info-box-number"><?= $stats['rooms'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Khách</span>
                                    <span class="info-box-number"><?= $stats['guests'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Đặt phòng</span>
                                    <span class="info-box-number"><?= $stats['reservations'] ?></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-user-graduate"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Sinh viên</span>
                                    <span class="info-box-number"><?= $stats['students'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <!-- Generate Form -->
                    <div class="col-md-6">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Tạo dữ liệu mẫu mới</h3>
                            </div>
                            <form method="POST">
                                <div class="card-body">
                                    <input type="hidden" name="action" value="generate">

                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info"></i>
                                        <strong>Lưu ý:</strong> Dữ liệu mẫu sẽ bao gồm:
                                        <ul class="mb-0 mt-2">
                                            <li>4 loại phòng (Standard, Superior, Deluxe, Suite)</li>
                                            <li>Phòng trên nhiều tầng với các trạng thái khác nhau</li>
                                            <li>Khách Việt Nam và quốc tế</li>
                                            <li>Đặt phòng quá khứ, hiện tại, tương lai</li>
                                        </ul>
                                    </div>

                                    <div class="form-group">
                                        <label>Số phòng</label>
                                        <input type="number" name="room_count" class="form-control" value="50" min="10" max="200">
                                        <small class="text-muted">Khuyến nghị: 50-100 phòng</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Số khách</label>
                                        <input type="number" name="guest_count" class="form-control" value="100" min="20" max="500">
                                        <small class="text-muted">Khuyến nghị: 100-200 khách</small>
                                    </div>

                                    <div class="form-group">
                                        <label>Số đặt phòng</label>
                                        <input type="number" name="reservation_count" class="form-control" value="30" min="10" max="100">
                                        <small class="text-muted">Khuyến nghị: 30-50 đặt phòng</small>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-magic"></i> Tạo dữ liệu
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reset/Clear -->
                    <div class="col-md-6">
                        <div class="card card-danger">
                            <div class="card-header">
                                <h3 class="card-title">Xóa dữ liệu</h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning">
                                    <i class="icon fas fa-exclamation-triangle"></i>
                                    <strong>Cảnh báo!</strong> Thao tác này sẽ xóa:
                                    <ul class="mb-0 mt-2">
                                        <li>Tất cả phòng và loại phòng</li>
                                        <li>Tất cả khách hàng</li>
                                        <li>Tất cả đặt phòng</li>
                                        <li>Tất cả folio và thanh toán</li>
                                    </ul>
                                    <strong>Không thể khôi phục!</strong>
                                </div>

                                <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa TOÀN BỘ dữ liệu lớp? Thao tác này không thể khôi phục!');">
                                    <input type="hidden" name="action" value="reset_all">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-trash"></i> Xóa toàn bộ dữ liệu
                                    </button>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Hướng dẫn</h3>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li>Chọn số lượng dữ liệu muốn tạo</li>
                                    <li>Nhấn "Tạo dữ liệu" và đợi</li>
                                    <li>Dữ liệu sẽ được tạo tự động</li>
                                    <li>Sinh viên có thể bắt đầu thực hành ngay</li>
                                </ol>

                                <p class="text-muted small mt-3">
                                    <i class="fas fa-lightbulb"></i>
                                    Bạn có thể tạo dữ liệu nhiều lần. Dữ liệu mới sẽ được thêm vào dữ liệu cũ.
                                    Nếu muốn bắt đầu lại từ đầu, hãy xóa toàn bộ dữ liệu trước.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>

<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
