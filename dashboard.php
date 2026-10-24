
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

echo '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Task 3 Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
<div class="card shadow p-4">
<h1>Task 3 Dashboard</h1>
<p>PHP is working. Now checking your login session.</p>';

session_start();

if (!isset($_SESSION['user_id'])) {
    echo '<div class="alert alert-warning">You are not logged in. Please log in first.</div>
    <a class="btn btn-primary" href="login.php">Go to Login</a>';
} else {
    $name = htmlspecialchars($_SESSION['full_name'] ?? 'User', ENT_QUOTES, 'UTF-8');
    $email = htmlspecialchars($_SESSION['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars($_SESSION['role'] ?? 'User', ENT_QUOTES, 'UTF-8');

    echo "<h2>Welcome, $name!</h2>
    <p>Email: $email</p>
    <p>Role: $role</p>
    <a class='btn btn-danger' href='logout.php'>Log out</a>";
}

echo '</div></div></body></html>';
?>
