
<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'Admin') {
    http_response_code(403);
    exit('Access denied.');
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if (!$id || $id === (int)$_SESSION['user_id']) {
        $message = 'Invalid user or you cannot delete/edit your own admin account here.';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $message = $stmt->affected_rows ? 'User deleted successfully.' : 'User not found.';
    } elseif ($action === 'update') {
        $name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Enter a valid name and email.';
        } else {
            try {
                $stmt = $conn->prepare('UPDATE users SET full_name = ?, email = ? WHERE id = ?');
                $stmt->bind_param('ssi', $name, $email, $id);
                $stmt->execute();
                $message = 'User updated successfully.';
            } catch (mysqli_sql_exception $e) {
                $message = 'Unable to update. The email may already be registered.';
            }
        }
    }
}

$editUser = null;
if (isset($_GET['edit']) && ctype_digit($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $conn->prepare('SELECT id, full_name, email FROM users WHERE id = ?');
    $stmt->bind_param('i', $editId);
    $stmt->execute();
    $editUser = $stmt->get_result()->fetch_assoc();
}

$stmt = $conn->prepare(
    'SELECT u.id, u.full_name, u.email, r.role_name, u.created_at
     FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC'
);
$stmt->execute();
$users = $stmt->get_result();

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>User Management | Task 3</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>User Management</h1><p class="text-secondary">Admin control panel</p></div>
        <a class="btn btn-outline-secondary" href="dashboard.php">Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-info"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($editUser): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h2 class="h5">Edit User #<?= (int)$editUser['id'] ?></h2>
            <form method="post" class="row g-3">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">
                <div class="col-md-5">
                    <label class="form-label">Full name</label>
                    <input class="form-control" name="full_name" maxlength="100" required value="<?= e($editUser['full_name']) ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Email</label>
                    <input class="form-control" type="email" name="email" maxlength="150" required value="<?= e($editUser['email']) ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary w-100">Save</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
    <div class="card-body">
        <h2 class="h5 mb-3">Registered Users (<?= $users->num_rows ?>)</h2>
        <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead class="table-dark"><tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Registered</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php while ($user = $users->fetch_assoc()): ?>
            <tr>
                <td><?= (int)$user['id'] ?></td>
                <td><?= e($user['full_name']) ?></td>
                <td><?= e($user['email']) ?></td>
                <td><?= e($user['role_name']) ?></td>
                <td><?= e($user['created_at']) ?></td>
                <td class="text-nowrap">
                    <?php if ((int)$user['id'] !== (int)$_SESSION['user_id']): ?>
                        <a class="btn btn-sm btn-outline-primary" href="users.php?edit=<?= (int)$user['id'] ?>">Edit</a>
                        <form method="post" class="d-inline" onsubmit="return confirm('Delete this user permanently?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    <?php else: ?>
                        <span class="text-secondary small">Current admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
    </div>
</div>
</body>
</html>
