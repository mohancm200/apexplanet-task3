
<?php
session_start();
require_once __DIR__ . '/config/db.php';

$message = '';
$messageType = 'danger';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Enter your full name and a valid email address.';
    } elseif (strlen($password) < 8) {
        $message = 'Password must contain at least 8 characters.';
    } elseif ($password !== $confirmPassword) {
        $message = 'Passwords do not match.';
    } else {
        try {
            $check = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    'INSERT INTO users (full_name, email, password, role_id)
                     VALUES (?, ?, ?, (SELECT id FROM roles WHERE role_name = ?))'
                );
                $role = 'User';
                $stmt->bind_param('ssss', $fullName, $email, $hash, $role);
                $stmt->execute();

                $message = 'Registration successful! You can now log in.';
                $messageType = 'success';
            }
        } catch (mysqli_sql_exception $e) {
            error_log('Registration error: ' . $e->getMessage());
            $message = 'Registration could not be completed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register | Task 3 User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width: 560px;">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h1 class="h3 mb-2">Create an account</h1>
            <p class="text-secondary mb-4">Task 3 — User Management System</p>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?= htmlspecialchars($messageType) ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full name</label>
                    <input class="form-control" id="full_name" name="full_name"
                           maxlength="100" required
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input class="form-control" type="email" id="email" name="email"
                           maxlength="150" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input class="form-control" type="password" id="password"
                           name="password" minlength="8" required>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm password</label>
                    <input class="form-control" type="password" id="confirm_password"
                           name="confirm_password" minlength="8" required>
                </div>

                <button class="btn btn-primary w-100" type="submit">Create account</button>
            </form>

            <p class="mt-4 mb-0 text-center">
                Already registered? <a href="login.php">Log in</a>
            </p>
        </div>
    </div>
</main>
</body>
</html>
