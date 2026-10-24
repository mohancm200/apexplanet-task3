
<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$message = '';
$messageType = 'success';

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function loadUser($conn, $userId) {
    $stmt = $conn->prepare(
        'SELECT full_name, email, profile_photo FROM users WHERE id = ?'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

$user = loadUser($conn, $userId);

if (!$user) {
    exit('User account not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'profile';

    if ($action === 'photo') {
        if (!isset($_FILES['profile_photo']) ||
            $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
            $message = 'Please select an image smaller than 2 MB.';
            $messageType = 'danger';
        } elseif ($_FILES['profile_photo']['size'] > 2 * 1024 * 1024) {
            $message = 'Image must be 2 MB or smaller.';
            $messageType = 'danger';
        } else {
            $tmp = $_FILES['profile_photo']['tmp_name'];
            $imageInfo = @getimagesize($tmp);
            $mime = $imageInfo['mime'] ?? '';

            $allowed = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];

            if (!$imageInfo || !isset($allowed[$mime])) {
                $message = 'Only valid JPG, PNG, or WEBP images are allowed.';
                $messageType = 'danger';
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!is_dir($uploadDir) &&
                    !mkdir($uploadDir, 0755, true)) {
                    $message = 'Unable to create the uploads folder.';
                    $messageType = 'danger';
                } else {
                    $fileName = 'profile_' . $userId . '_' .
                        bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                    $destination = $uploadDir . $fileName;

                    if (move_uploaded_file($tmp, $destination)) {
                        $relativePath = 'uploads/' . $fileName;

                        $stmt = $conn->prepare(
                            'UPDATE users SET profile_photo = ? WHERE id = ?'
                        );
                        $stmt->bind_param('si', $relativePath, $userId);
                        $stmt->execute();

                        if (!empty($user['profile_photo'])) {
                            $oldPath = __DIR__ . '/' . $user['profile_photo'];
                            if (is_file($oldPath) &&
                                realpath(dirname($oldPath)) === realpath($uploadDir)) {
                                @unlink($oldPath);
                            }
                        }

                        $message = 'Profile picture uploaded successfully.';
                    } else {
                        $message = 'Upload failed. Check the uploads folder permissions.';
                        $messageType = 'danger';
                    }
                }
            }
        }
    } elseif ($action === 'profile') {
        $name = trim($_POST['full_name'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));

        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Enter a valid name and email address.';
            $messageType = 'danger';
        } else {
            try {
                $stmt = $conn->prepare(
                    'UPDATE users SET full_name = ?, email = ? WHERE id = ?'
                );
                $stmt->bind_param('ssi', $name, $email, $userId);
                $stmt->execute();

                $_SESSION['full_name'] = $name;
                $_SESSION['email'] = $email;

                $message = 'Profile updated successfully.';
            } catch (mysqli_sql_exception $ex) {
                $message = 'That email may already be registered. Try another.';
                $messageType = 'danger';
            }
        }
    }

    $user = loadUser($conn, $userId);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>My Profile | Task 3</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background:#f4f7fb; }
.profile-card { max-width:650px; margin:45px auto; border:0; border-radius:18px; }
.avatar { width:120px;height:120px;border-radius:50%;object-fit:cover;
border:4px solid #e5edff;background:#edf2ff; }
</style>
</head>
<body>
<div class="container">
<div class="card profile-card shadow-sm">
<div class="card-body p-4 p-md-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">My Profile</h1>
            <p class="text-secondary mb-0">Manage your account</p>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">Dashboard</a>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= e($messageType) ?>"><?= e($message) ?></div>
    <?php endif; ?>

    <div class="text-center mb-4">
        <?php if (!empty($user['profile_photo'])): ?>
            <img class="avatar" src="<?= e($user['profile_photo']) ?>" alt="Profile picture">
        <?php else: ?>
            <div class="avatar d-inline-flex align-items-center justify-content-center">
                <span class="fs-1 text-primary">
                    <?= e(strtoupper(substr($user['full_name'], 0, 1))) ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <form method="post" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="action" value="photo">
        <label for="profile_photo" class="form-label">Profile picture (JPG, PNG, WEBP; max 2 MB)</label>
        <input id="profile_photo" class="form-control mb-3" type="file"
               name="profile_photo" accept=".jpg,.jpeg,.png,.webp" required>
        <button type="submit" class="btn btn-outline-primary w-100">Upload Picture</button>
    </form>

    <hr class="my-4">

    <form method="post">
        <input type="hidden" name="action" value="profile">
        <div class="mb-3">
            <label for="full_name" class="form-label">Full name</label>
            <input id="full_name" name="full_name" class="form-control form-control-lg"
                   maxlength="100" required value="<?= e($user['full_name']) ?>">
        </div>
        <div class="mb-4">
            <label for="email" class="form-label">Email address</label>
            <input id="email" type="email" name="email"
                   class="form-control form-control-lg" maxlength="150"
                   required value="<?= e($user['email']) ?>">
        </div>
        <button class="btn btn-primary btn-lg w-100" type="submit">Save Changes</button>
    </form>
</div>
</div>
</div>
</body>
</html>
