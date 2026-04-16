<?php
declare(strict_types=1);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $configFile = __DIR__ . '/config.php';
    if (!is_file($configFile)) {
        die("config.php is missing. Please create it first.");
    }
    $config = require $configFile;

    try {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['database']['host'],
            $config['database']['port'],
            $config['database']['name']
        );
        $pdo = new PDO($dsn, $config['database']['user'], $config['database']['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
        ]);

        // Run install.sql
        $sql = file_get_contents(__DIR__ . '/database/install.sql');
        if (!$sql) {
            throw new Exception("Could not read database/install.sql");
        }

        // Execute multiple statements
        $pdo->exec($sql);

        // Create Admin User
        $name = trim($_POST['admin_name'] ?? 'Super Admin');
        $email = trim($_POST['admin_email'] ?? 'admin@emag.pk');
        $password = $_POST['admin_password'] ?? '';

        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt = $pdo->prepare(
            "INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, 'admin', 'approved')"
        );
        $stmt->execute([$name, $email, $hash]);

        $success = "Database migration and seeding completed successfully! Admin user created. <b>Please delete install.php for security reasons.</b> <a href='/login'>Go to Login</a>";
    } catch (Exception $e) {
        $error = "Installation failed: " . htmlspecialchars($e->getMessage());
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>EMAG.PK Installer</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">EMAG.PK One-Click Installer</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php else: ?>
                        <p class="text-muted">Ensure your database connection details are correct in <strong>config.php</strong> before running this installer. This will drop all existing tables!</p>
                        <form method="post" action="">
                            <h5 class="mt-4 mb-3">Admin Account Details</h5>
                            <div class="mb-3">
                                <label class="form-label">Admin Name</label>
                                <input type="text" name="admin_name" class="form-control" value="EMAG Admin" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Admin Email</label>
                                <input type="email" name="admin_email" class="form-control" value="admin@emag.pk" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Admin Password</label>
                                <input type="password" name="admin_password" class="form-control" required minlength="8">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 fw-bold" onclick="return confirm('Are you sure? This will wipe the existing database.')">Run Migration & Setup</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>