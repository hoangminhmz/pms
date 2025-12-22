<?php
/**
 * Helper Functions
 */

// Security
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// CSRF Protection
function generateCsrfToken() {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken($token) {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField() {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// Redirect
function redirect($url) {
    header("Location: " . $url);
    exit;
}

function redirectBack() {
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

// Flash Messages
function setFlash($type, $message) {
    $_SESSION['flash_' . $type] = $message;
}

function getFlash($type) {
    if (isset($_SESSION['flash_' . $type])) {
        $message = $_SESSION['flash_' . $type];
        unset($_SESSION['flash_' . $type]);
        return $message;
    }
    return null;
}

function hasFlash($type) {
    return isset($_SESSION['flash_' . $type]);
}

// Date/Time
function formatDate($date, $format = DATE_FORMAT) {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = DATETIME_FORMAT) {
    return date($format, strtotime($datetime));
}

function now() {
    return date('Y-m-d H:i:s');
}

function today() {
    return date('Y-m-d');
}

// Money
function formatMoney($amount) {
    return number_format($amount, 0, ',', '.') . ' đ';
}

function formatNumber($number, $decimals = 0) {
    return number_format($number, $decimals, ',', '.');
}

// String
function truncate($text, $length = 100, $suffix = '...') {
    if (mb_strlen($text) > $length) {
        return mb_substr($text, 0, $length) . $suffix;
    }
    return $text;
}

// Validation
function isEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isEmpty($value) {
    return empty(trim($value));
}

// URL
function url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

function asset($path) {
    return url('assets/' . ltrim($path, '/'));
}

// Debug
function dd($var) {
    echo '<pre>';
    var_dump($var);
    echo '</pre>';
    die();
}

// Pagination
function paginate($total, $perPage = PER_PAGE, $currentPage = 1) {
    $totalPages = ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $perPage;

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $currentPage,
        'total_pages' => $totalPages,
        'offset' => $offset,
        'has_prev' => $currentPage > 1,
        'has_next' => $currentPage < $totalPages,
    ];
}

// Auto-generate IDs
function generateProfileId() {
    $sql = "SELECT profile_id FROM guests WHERE profile_id LIKE 'G-%' ORDER BY id DESC LIMIT 1";
    $last = fetchOne($sql);
    $num = $last ? intval(substr($last['profile_id'], 2)) + 1 : 1;
    return 'G-' . str_pad($num, 6, '0', STR_PAD_LEFT);
}

function generateConfirmationNo() {
    $prefix = 'H-' . date('ymd') . '-';
    $sql = "SELECT confirmation_no FROM reservations WHERE confirmation_no LIKE ? ORDER BY id DESC LIMIT 1";
    $last = fetchOne($sql, [$prefix . '%']);
    $num = $last ? intval(substr($last['confirmation_no'], -4)) + 1 : 1;
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

function generateFolioNumber() {
    $prefix = 'F-' . date('ymd') . '-';
    $sql = "SELECT folio_number FROM folios WHERE folio_number LIKE ? ORDER BY id DESC LIMIT 1";
    $last = fetchOne($sql, [$prefix . '%']);
    $num = $last ? intval(substr($last['folio_number'], -4)) + 1 : 1;
    return $prefix . str_pad($num, 4, '0', STR_PAD_LEFT);
}

// Activity Logging
function logActivity($action, $module, $description, $oldValues = null, $newValues = null) {
    $sql = "INSERT INTO activity_logs (class_id, user_id, action, module, description, old_values, new_values, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    execute($sql, [
        currentClassId(),
        userId(),
        $action,
        $module,
        $description,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $_SERVER['REMOTE_ADDR'],
        now()
    ]);
}
