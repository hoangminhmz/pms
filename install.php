<?php
/**
 * Installation Wizard for Hotel PMS Training Platform
 * This file should be deleted after installation
 */

// Check if already installed
if (file_exists(__DIR__ . '/config/database.php')) {
    $content = file_get_contents(__DIR__ . '/config/database.php');
    if (strpos($content, 'DB_NAME') !== false && strpos($content, 'localhost') === false) {
        // Looks like it's already configured
        // Comment this block to allow reinstall
        die('Installation already completed. Delete this file (install.php) or reconfigure config/database.php manually.');
    }
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Step 1: Database Configuration
if ($step == 1 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? '');
    $dbName = trim($_POST['db_name'] ?? '');
    $dbUser = trim($_POST['db_user'] ?? '');
    $dbPass = $_POST['db_pass'] ?? '';

    // Test connection
    try {
        $dsn = "mysql:host=$dbHost;charset=utf8mb4";
        $pdo = new PDO($dsn, $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '$dbName'");
        if ($stmt->rowCount() == 0) {
            // Create database
            $pdo->exec("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }

        // Save database config
        $configContent = "<?php\n";
        $configContent .= "/**\n";
        $configContent .= " * Database Configuration\n";
        $configContent .= " * This file was created by the installer\n";
        $configContent .= " */\n\n";
        $configContent .= "define('DB_HOST', " . var_export($dbHost, true) . ");\n";
        $configContent .= "define('DB_NAME', " . var_export($dbName, true) . ");\n";
        $configContent .= "define('DB_USER', " . var_export($dbUser, true) . ");\n";
        $configContent .= "define('DB_PASS', " . var_export($dbPass, true) . ");\n";
        $configContent .= "define('DB_CHARSET', 'utf8mb4');\n\n";
        $configContent .= "date_default_timezone_set('Asia/Ho_Chi_Minh');\n";

        file_put_contents(__DIR__ . '/config/database.php', $configContent);

        // Store for next step
        $_SESSION['db_configured'] = true;

        header('Location: install.php?step=2');
        exit;
    } catch (PDOException $e) {
        $error = 'Database connection failed: ' . $e->getMessage();
    }
}

// Step 2: Import Database Schema
if ($step == 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/database.php';

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Read and execute schema
        $schema = file_get_contents(__DIR__ . '/sql/schema.sql');

        // Split by semicolon and execute each statement
        $statements = array_filter(array_map('trim', explode(';', $schema)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        $_SESSION['schema_imported'] = true;

        header('Location: install.php?step=3');
        exit;
    } catch (PDOException $e) {
        $error = 'Schema import failed: ' . $e->getMessage();
    }
}

// Step 3: Create Admin Account
if ($step == 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/core/db.php';
    require_once __DIR__ . '/core/functions.php';

    $adminName = trim($_POST['admin_name'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $adminPassword = $_POST['admin_password'] ?? '';

    if (empty($adminName) || empty($adminEmail) || empty($adminPassword)) {
        $error = 'Please fill all fields';
    } else {
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

        // Delete default admin
        execute("DELETE FROM users WHERE email = 'admin@pms-training.com'");

        // Insert new admin
        $sql = "INSERT INTO users (name, email, password, role, is_active, created_at)
                VALUES (?, ?, ?, 'admin', 1, ?)";
        execute($sql, [$adminName, $adminEmail, $hashedPassword, date('Y-m-d H:i:s')]);

        $_SESSION['installation_complete'] = true;

        header('Location: install.php?step=4');
        exit;
    }
}

// Start session for wizard
session_start();
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - Hotel PMS Training Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="hold-transition">
<div class="wrapper">
    <div class="content-wrapper" style="margin-left: 0;">
        <section class="content pt-5">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8">

                        <div class="card">
                            <div class="card-header bg-primary">
                                <h3 class="card-title"><i class="fas fa-hotel"></i> Hotel PMS Training Platform - Installation</h3>
                            </div>
                            <div class="card-body">

                                <!-- Progress Steps -->
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <div class="progress">
                                            <div class="progress-bar" role="progressbar" style="width: <?= ($step / 4) * 100 ?>%" aria-valuenow="<?= $step ?>" aria-valuemin="0" aria-valuemax="4"></div>
                                        </div>
                                        <div class="d-flex justify-content-between mt-2 small text-muted">
                                            <span>Database</span>
                                            <span>Schema</span>
                                            <span>Admin</span>
                                            <span>Complete</span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger">
                                        <i class="icon fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($success): ?>
                                    <div class="alert alert-success">
                                        <i class="icon fas fa-check"></i> <?= htmlspecialchars($success) ?>
                                    </div>
                                <?php endif; ?>

                                <?php if ($step == 1): ?>
                                    <!-- Step 1: Database Configuration -->
                                    <h4>Step 1: Database Configuration</h4>
                                    <p>Enter your database connection details. The database will be created automatically if it doesn't exist.</p>

                                    <form method="POST">
                                        <div class="form-group">
                                            <label>Database Host</label>
                                            <input type="text" name="db_host" class="form-control" value="localhost" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Database Name</label>
                                            <input type="text" name="db_name" class="form-control" value="pms_training" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Database User</label>
                                            <input type="text" name="db_user" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Database Password</label>
                                            <input type="password" name="db_pass" class="form-control">
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            Next: Import Schema <i class="fas fa-arrow-right"></i>
                                        </button>
                                    </form>

                                <?php elseif ($step == 2): ?>
                                    <!-- Step 2: Import Schema -->
                                    <h4>Step 2: Import Database Schema</h4>
                                    <p>Click the button below to create all database tables and insert default data.</p>

                                    <div class="alert alert-info">
                                        <i class="icon fas fa-info-circle"></i>
                                        This will create:
                                        <ul class="mb-0 mt-2">
                                            <li>11 database tables</li>
                                            <li>Default admin user (will be replaced in next step)</li>
                                            <li>5 template scenarios</li>
                                        </ul>
                                    </div>

                                    <form method="POST">
                                        <button type="submit" class="btn btn-primary">
                                            Import Schema <i class="fas fa-database"></i>
                                        </button>
                                    </form>

                                <?php elseif ($step == 3): ?>
                                    <!-- Step 3: Create Admin Account -->
                                    <h4>Step 3: Create Admin Account</h4>
                                    <p>Create your administrator account. You'll use this to log in and manage the system.</p>

                                    <form method="POST">
                                        <div class="form-group">
                                            <label>Full Name</label>
                                            <input type="text" name="admin_name" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Email</label>
                                            <input type="email" name="admin_email" class="form-control" required>
                                        </div>

                                        <div class="form-group">
                                            <label>Password</label>
                                            <input type="password" name="admin_password" class="form-control" required minlength="6">
                                            <small class="text-muted">Minimum 6 characters</small>
                                        </div>

                                        <button type="submit" class="btn btn-success">
                                            Complete Installation <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                <?php elseif ($step == 4): ?>
                                    <!-- Step 4: Installation Complete -->
                                    <div class="text-center">
                                        <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                                        <h4>Installation Complete!</h4>
                                        <p>Your Hotel PMS Training Platform is ready to use.</p>

                                        <div class="alert alert-warning mt-4">
                                            <i class="icon fas fa-exclamation-triangle"></i>
                                            <strong>Important:</strong> Please delete the <code>install.php</code> file from your server for security.
                                        </div>

                                        <div class="mt-4">
                                            <h5>Next Steps:</h5>
                                            <ol class="text-left">
                                                <li>Delete <code>install.php</code> file</li>
                                                <li>Log in as admin</li>
                                                <li>Create teacher accounts</li>
                                                <li>Teachers create classes and add students</li>
                                                <li>Generate dummy data for practice</li>
                                            </ol>
                                        </div>

                                        <a href="index.php" class="btn btn-primary btn-lg mt-3">
                                            <i class="fas fa-sign-in-alt"></i> Go to Login
                                        </a>
                                    </div>

                                <?php endif; ?>

                            </div>
                        </div>

                        <div class="text-center text-muted mt-3">
                            <small>Hotel PMS Training Platform v1.0</small>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
