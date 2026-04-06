<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="form-box active">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h2>
            <p>You are logged in as <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong></p>
            <form action="logout.php" method="post">
                <button type="submit">Logout</button>
            </form>
        </div>
    </div>
</body>
</html>
