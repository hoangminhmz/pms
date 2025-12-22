-- ============================================
-- Hotel PMS Training Platform - Database Schema
-- ============================================

-- Users table (Admin, Teachers, Students)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'teacher', 'student') DEFAULT 'student',
  `student_code` VARCHAR(20) NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `last_login_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  KEY `idx_email` (`email`),
  KEY `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- School Classes table
CREATE TABLE IF NOT EXISTS `school_classes` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(20) NOT NULL UNIQUE,
  `teacher_id` INT UNSIGNED NOT NULL,
  `description` TEXT NULL,
  `start_date` DATE NULL,
  `end_date` DATE NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  FOREIGN KEY (`teacher_id`) REFERENCES `users`(`id`) ON DELETE RESTRICT,
  KEY `idx_code` (`code`),
  KEY `idx_teacher` (`teacher_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Class Students (many-to-many)
CREATE TABLE IF NOT EXISTS `class_students` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `joined_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_class_student` (`class_id`, `student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Room Types table (per class)
CREATE TABLE IF NOT EXISTS `room_types` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NULL,
  `rack_rate` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `default_rate` DECIMAL(12,2) NOT NULL DEFAULT 0,
  `max_adults` TINYINT UNSIGNED DEFAULT 2,
  `max_children` TINYINT UNSIGNED DEFAULT 1,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  KEY `idx_class` (`class_id`),
  KEY `idx_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rooms table (per class)
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `room_type_id` INT UNSIGNED NOT NULL,
  `room_number` VARCHAR(20) NOT NULL,
  `floor` TINYINT UNSIGNED NOT NULL,
  `fo_status` ENUM('vacant', 'occupied') DEFAULT 'vacant',
  `hk_status` ENUM('clean', 'dirty', 'inspected') DEFAULT 'clean',
  `room_condition` ENUM('available', 'ooo', 'oos') DEFAULT 'available',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE RESTRICT,
  KEY `idx_class` (`class_id`),
  KEY `idx_room_number` (`room_number`),
  KEY `idx_status` (`fo_status`, `hk_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Guests table (per class)
CREATE TABLE IF NOT EXISTS `guests` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `profile_id` VARCHAR(20) NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(50) NOT NULL,
  `full_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NULL,
  `phone` VARCHAR(20) NULL,
  `id_type` ENUM('passport', 'cccd', 'cmnd') NOT NULL,
  `id_number` VARCHAR(20) NOT NULL,
  `nationality` VARCHAR(50) DEFAULT 'Vietnam',
  `date_of_birth` DATE NULL,
  `gender` ENUM('male', 'female', 'other') NULL,
  `address` VARCHAR(200) NULL,
  `city` VARCHAR(100) NULL,
  `country` VARCHAR(100) DEFAULT 'Vietnam',
  `vip_level` TINYINT UNSIGNED DEFAULT 0,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  KEY `idx_class` (`class_id`),
  KEY `idx_profile` (`profile_id`),
  KEY `idx_name` (`full_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Reservations table (per class)
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `confirmation_no` VARCHAR(20) NOT NULL,
  `guest_id` INT UNSIGNED NOT NULL,
  `room_type_id` INT UNSIGNED NOT NULL,
  `room_id` INT UNSIGNED NULL,
  `arrival_date` DATE NOT NULL,
  `departure_date` DATE NOT NULL,
  `nights` TINYINT UNSIGNED NOT NULL,
  `adults` TINYINT UNSIGNED DEFAULT 1,
  `children` TINYINT UNSIGNED DEFAULT 0,
  `rate` DECIMAL(12,2) NOT NULL,
  `total_amount` DECIMAL(15,2) NOT NULL,
  `status` ENUM('confirmed', 'checked_in', 'checked_out', 'cancelled', 'no_show') DEFAULT 'confirmed',
  `source` ENUM('direct', 'phone', 'email', 'website', 'walk_in') DEFAULT 'direct',
  `special_requests` TEXT NULL,
  `notes` TEXT NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`guest_id`) REFERENCES `guests`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`room_type_id`) REFERENCES `room_types`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE SET NULL,
  KEY `idx_class` (`class_id`),
  KEY `idx_confirmation` (`confirmation_no`),
  KEY `idx_dates` (`arrival_date`, `departure_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Folios table (billing)
CREATE TABLE IF NOT EXISTS `folios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NOT NULL,
  `reservation_id` INT UNSIGNED NOT NULL,
  `folio_number` VARCHAR(20) NOT NULL,
  `guest_name` VARCHAR(100) NOT NULL,
  `room_number` VARCHAR(20) NOT NULL,
  `total_charges` DECIMAL(15,2) DEFAULT 0,
  `total_payments` DECIMAL(15,2) DEFAULT 0,
  `balance` DECIMAL(15,2) DEFAULT 0,
  `status` ENUM('open', 'closed') DEFAULT 'open',
  `closed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`reservation_id`) REFERENCES `reservations`(`id`) ON DELETE RESTRICT,
  KEY `idx_class` (`class_id`),
  KEY `idx_folio_number` (`folio_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Folio Transactions
CREATE TABLE IF NOT EXISTS `folio_transactions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `folio_id` INT UNSIGNED NOT NULL,
  `transaction_date` DATE NOT NULL,
  `transaction_time` TIME NOT NULL,
  `type` ENUM('charge', 'payment') NOT NULL,
  `charge_code` VARCHAR(10) NULL,
  `description` VARCHAR(200) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `payment_method` ENUM('cash', 'card', 'bank_transfer') NULL,
  `posted_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`folio_id`) REFERENCES `folios`(`id`) ON DELETE CASCADE,
  KEY `idx_folio` (`folio_id`),
  KEY `idx_date` (`transaction_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scenarios table
CREATE TABLE IF NOT EXISTS `scenarios` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NULL,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT NOT NULL,
  `type` ENUM('checkin', 'checkout', 'reservation', 'complaint', 'other') NOT NULL,
  `difficulty` ENUM('easy', 'medium', 'hard') DEFAULT 'medium',
  `objectives` TEXT NOT NULL,
  `scenario_data` TEXT NULL,
  `expected_actions` TEXT NULL,
  `time_limit` INT UNSIGNED NULL,
  `max_score` INT UNSIGNED DEFAULT 100,
  `is_template` TINYINT(1) DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_class` (`class_id`),
  KEY `idx_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Scenario Attempts (student submissions)
CREATE TABLE IF NOT EXISTS `scenario_attempts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `scenario_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `started_at` DATETIME NOT NULL,
  `completed_at` DATETIME NULL,
  `time_spent` INT UNSIGNED NULL,
  `actions_log` TEXT NULL,
  `score` INT UNSIGNED NULL,
  `feedback` TEXT NULL,
  `status` ENUM('in_progress', 'completed', 'graded') DEFAULT 'in_progress',
  `graded_by` INT UNSIGNED NULL,
  `graded_at` DATETIME NULL,
  FOREIGN KEY (`scenario_id`) REFERENCES `scenarios`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`graded_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_scenario` (`scenario_id`),
  KEY `idx_student` (`student_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `class_id` INT UNSIGNED NULL,
  `user_id` INT UNSIGNED NULL,
  `action` VARCHAR(50) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `old_values` TEXT NULL,
  `new_values` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `created_at` DATETIME NOT NULL,
  FOREIGN KEY (`class_id`) REFERENCES `school_classes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  KEY `idx_class` (`class_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user
INSERT INTO `users` (`name`, `email`, `password`, `role`, `is_active`, `created_at`) VALUES
('System Admin', 'admin@pms-training.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1, NOW());
-- Default password: password

-- Insert template scenarios
INSERT INTO `scenarios` (`class_id`, `title`, `description`, `type`, `difficulty`, `objectives`, `time_limit`, `max_score`, `is_template`, `is_active`, `created_at`) VALUES
(NULL, 'Walk-in Check-in', 'Khách đến không có booking. Cần tạo profile, kiểm tra phòng trống và check-in.', 'checkin', 'easy', 'Tạo guest profile\nKiểm tra phòng available\nAssign phòng\nCheck-in thành công\nTạo folio', 15, 100, 1, 1, NOW()),
(NULL, 'Express Checkout', 'Khách check-out nhanh, cần thanh toán và in hóa đơn.', 'checkout', 'easy', 'Kiểm tra folio\nTính tổng tiền\nNhận thanh toán\nCheck-out\nCập nhật room status', 10, 100, 1, 1, NOW()),
(NULL, 'VIP Guest Arrival', 'Khách VIP đến sớm, cần upgrade phòng nếu có thể.', 'checkin', 'medium', 'Nhận diện VIP guest\nKiểm tra upgrade availability\nCoordinate với housekeeping\nCheck-in với amenities', 20, 100, 1, 1, NOW()),
(NULL, 'Overbooking Situation', 'Xử lý tình huống overbooking - cần tìm giải pháp cho khách.', 'reservation', 'hard', 'Xác định overbooking\nTìm phương án thay thế\nLiên hệ khách sạn khác\nĐàm phán với khách\nGhi nhận incident', 30, 100, 1, 1, NOW()),
(NULL, 'Complaint Handling', 'Khách phàn nàn về phòng ồn, cần xử lý và đổi phòng.', 'complaint', 'medium', 'Lắng nghe complaint\nXin lỗi chân thành\nKiểm tra phòng khác\nĐổi phòng\nCập nhật folio với discount', 20, 100, 1, 1, NOW());
