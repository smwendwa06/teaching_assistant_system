<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register | Volunteer Teaching System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="login-container">
        <h2>Create Account</h2>
 
        <?php
        if (isset($_SESSION['register_error'])) {
            echo "<div class='error-message'>" . htmlspecialchars($_SESSION['register_error']) . "</div>";
            unset($_SESSION['register_error']);
        }
        ?>
 
        <form method="POST" action="login_register.php">
            <input type="text" name="Name" placeholder="Full Name" required>
            <input type="email" name="Email" placeholder="Email Address" required>
            <input type="password" name="Password" placeholder="Password" required>
            <input type="password" name="ConfirmPassword" placeholder="Confirm Password" required>
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="user">Volunteer</option>
                <option value="admin">Admin</option>
            </select>
            <select name="security_question" required>
                <option value="">-- Select Security Question --</option>
                <option value="What was the name of your first pet?">What was the name of your first pet?</option>
                <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                <option value="What town were you born in?">What town were you born in?</option>
            </select>
            <input type="text" name="security_answer" placeholder="Your Answer to Security Question" required>
            <button type="submit" name="Register">Register</button>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </form>
    </div>
</body>
</html>