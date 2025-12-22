<?php
/**
 * Data Isolation Functions
 * Ensures each class only sees their own data
 */

/**
 * Get current class ID from session
 */
function getCurrentClassId() {
    return $_SESSION['class_id'] ?? null;
}

/**
 * Ensure query includes class_id filter
 */
function withClassFilter($sql, $params = []) {
    $classId = getCurrentClassId();

    if (!$classId && userRole() !== 'admin') {
        throw new Exception('No class assigned to this user');
    }

    // Add class_id to WHERE clause if student/teacher
    if (userRole() !== 'admin') {
        if (stripos($sql, 'WHERE') !== false) {
            $sql = str_replace('WHERE', 'WHERE class_id = ? AND', $sql);
        } else {
            $sql .= ' WHERE class_id = ?';
        }
        array_unshift($params, $classId);
    }

    return ['sql' => $sql, 'params' => $params];
}

/**
 * Check if user has access to a specific record
 */
function canAccessRecord($table, $recordId) {
    $classId = getCurrentClassId();

    // Admin can access everything
    if (userRole() === 'admin') {
        return true;
    }

    // Check if record belongs to user's class
    $sql = "SELECT class_id FROM $table WHERE id = ?";
    $record = fetchOne($sql, [$recordId]);

    if (!$record) {
        return false;
    }

    return $record['class_id'] == $classId;
}

/**
 * Ensure data belongs to current class before insert
 */
function ensureClassId(&$data) {
    $classId = getCurrentClassId();

    if (!$classId && userRole() !== 'admin') {
        throw new Exception('No class assigned');
    }

    if (userRole() !== 'admin') {
        $data['class_id'] = $classId;
    }

    return $data;
}

/**
 * Get all classes for teacher
 */
function getTeacherClasses($teacherId) {
    $sql = "SELECT * FROM school_classes WHERE teacher_id = ? ORDER BY created_at DESC";
    return fetchAll($sql, [$teacherId]);
}

/**
 * Get class for student
 */
function getStudentClass($studentId) {
    $sql = "SELECT sc.* FROM school_classes sc
            INNER JOIN class_students cs ON sc.id = cs.class_id
            WHERE cs.student_id = ?
            LIMIT 1";
    return fetchOne($sql, [$studentId]);
}

/**
 * Switch active class (for teachers with multiple classes)
 */
function switchClass($classId) {
    // Verify teacher owns this class
    if (userRole() === 'teacher') {
        $sql = "SELECT id FROM school_classes WHERE id = ? AND teacher_id = ?";
        $class = fetchOne($sql, [$classId, userId()]);

        if (!$class) {
            return false;
        }
    }

    $_SESSION['class_id'] = $classId;
    return true;
}

/**
 * Get class statistics
 */
function getClassStats($classId) {
    return [
        'students' => fetchValue("SELECT COUNT(*) FROM class_students WHERE class_id = ?", [$classId]),
        'rooms' => fetchValue("SELECT COUNT(*) FROM rooms WHERE class_id = ?", [$classId]),
        'guests' => fetchValue("SELECT COUNT(*) FROM guests WHERE class_id = ?", [$classId]),
        'reservations' => fetchValue("SELECT COUNT(*) FROM reservations WHERE class_id = ? AND status != 'cancelled'", [$classId]),
        'scenarios' => fetchValue("SELECT COUNT(*) FROM scenarios WHERE class_id = ?", [$classId]),
    ];
}
