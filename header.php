<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Teaching System.</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
 
<nav class="navbar">
    <div class="nav-brand">🎓 Volunteer Teaching System.</div>
    <div class="nav-links">
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin_page.php">Home</a>
            <a href="applications.php">Applications</a>
            <a href="schools.php">Schools</a>
            <a href="feedback.php">Feedback</a>
        <?php else: ?>
            <a href="user_page.php">Home</a>
            <a href="apply.php">Apply</a>
            <a href="training.php">Training</a>
            <a href="feedback.php">Feedback</a>
        <?php endif; ?>
        <span class="nav-user">👤 <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></span>
        <form action="logout.php" method="post" style="display:inline;">
            <button type="submit" class="nav-logout">Logout</button>
        </form>
    </div>
</nav>