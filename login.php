<!-- <!DOCTYPE html>
<html lang= "en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css"> <!-- Link to your CSS file-->
<!-- </head>
<body>
    <div class="container">
        <div class="login-container">
       <h1>Login</h1>
// if (session_status() === PHP_SESSION_NONE) {
//     session_start();
// }
// if(isset($_SESSION['login_error'])){
//     echo "<div class='error-message'>" . $_SESSION['login_error'] . "</div>";
//     unset($_SESSION['login_error']);
// }
// ?> -->
<!-- // <form method="POST" action="login_register.php">
//            <label for="Email">Email:</label>
//            <input type="Email" id="Email" name="Email" required>
//            <label for="Password">Password:</label>
//            <input type="Password" id="Password" name="Password" required>
//            <button type="submit" name="Login">Login</button>
//            <p>Don't have an account? <a href="login_register.php">Register</a></p>
//         </form>
//     </div>
// </body>
// </html> -->

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login | Volunteer Teaching System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">
    <div class="login-container">
        <h2>🎓 Volunteer Teaching System</h2>
 
        <?php
        if (isset($_SESSION['login_error'])) {
            echo "<div class='error-message'>" . htmlspecialchars($_SESSION['login_error']) . "</div>";
            unset($_SESSION['login_error']);
        }
        if (isset($_SESSION['register_success'])) {
            echo "<div class='success-message'>" . htmlspecialchars($_SESSION['register_success']) . "</div>";
            unset($_SESSION['register_success']);
        }
        ?>
 
        <form method="POST" action="login_register.php">
            <input type="email" name="Email" placeholder="Email Address" required>
            <input type="password" name="Password" placeholder="Password" required>
            <button type="submit" name="Login">Login</button>
            <p>Don't have an account? <a href="register.php">Register</a></p>
        </form>
    </div>
</body>
</html>
