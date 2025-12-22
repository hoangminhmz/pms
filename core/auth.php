<?php
/**
 * Authentication Functions
 */

function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

function requireAuth() {
    if (!isLoggedIn()) {
        setFlash('error', 'Vui lòng đăng nhập để tiếp tục');
        redirect(url('modules/auth/login.php'));
    }
}

function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

function requireRole(...$roles) {
    requireAuth();
    if (!in_array($_SESSION['user_role'], $roles)) {
        setFlash('error', 'Bạn không có quyền truy cập');
        redirect(url());
    }
}

function userId() {
    return $_SESSION['user_id'] ?? null;
}

function userEmail() {
    return $_SESSION['user_email'] ?? null;
}

function userName() {
    return $_SESSION['user_name'] ?? '';
}

function userRole() {
    return $_SESSION['user_role'] ?? null;
}

function currentClassId() {
    return $_SESSION['class_id'] ?? null;
}

function currentUser() {
    if (!isLoggedIn()) return null;

    static $user = null;
    if ($user === null) {
        $sql = "SELECT * FROM users WHERE id = ?";
        $user = fetchOne($sql, [userId()]);
    }
    return $user;
}

function login($email, $password) {
    $sql = "SELECT * FROM users WHERE email = ? AND is_active = 1";
    $user = fetchOne($sql, [$email]);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in_at'] = time();

        // Set class_id for student
        if ($user['role'] === 'student') {
            $sql = "SELECT class_id FROM class_students WHERE student_id = ? LIMIT 1";
            $class = fetchOne($sql, [$user['id']]);
            $_SESSION['class_id'] = $class['class_id'] ?? null;
        }

        // Update last login
        execute("UPDATE users SET last_login_at = ? WHERE id = ?", [now(), $user['id']]);

        logActivity('login', 'auth', 'User logged in');

        return true;
    }

    return false;
}

function logout() {
    if (isLoggedIn()) {
        logActivity('logout', 'auth', 'User logged out');
    }

    session_unset();
    session_destroy();
    session_start();

    return true;
}

function register($name, $email, $password, $role = 'student', $studentCode = null) {
    if (fetchOne("SELECT id FROM users WHERE email = ?", [$email])) {
        return ['success' => false, 'message' => 'Email đã tồn tại'];
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, password, role, student_code, is_active, created_at)
            VALUES (?, ?, ?, ?, ?, 1, ?)";

    try {
        execute($sql, [$name, $email, $hashedPassword, $role, $studentCode, now()]);
        return ['success' => true, 'user_id' => lastInsertId()];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Lỗi tạo tài khoản'];
    }
}

function changePassword($userId, $oldPassword, $newPassword) {
    $user = fetchOne("SELECT password FROM users WHERE id = ?", [$userId]);

    if (!$user) {
        return ['success' => false, 'message' => 'User không tồn tại'];
    }

    if (!password_verify($oldPassword, $user['password'])) {
        return ['success' => false, 'message' => 'Mật khẩu cũ không đúng'];
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    execute("UPDATE users SET password = ? WHERE id = ?", [$hashedPassword, $userId]);

    logActivity('change_password', 'auth', 'Changed password');

    return ['success' => true, 'message' => 'Đổi mật khẩu thành công'];
}
