<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
 
// Registration
if (isset($_POST['Register'])) {
    $name = trim($_POST['Name'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $password = trim($_POST['Password'] ?? '');
    $confirmPassword = trim($_POST['ConfirmPassword'] ?? '');
    $role = strtolower(trim($_POST['role'] ?? 'user'));
    $security_question = trim($_POST['security_question'] ?? '');
    $security_answer = strtolower(trim($_POST['security_answer'] ?? ''));
 
// Check passwords match
if ($password !== $confirmPassword) {
    $_SESSION['register_error'] = 'Passwords do not match. Please try again.';
    header("Location: register.php");
    exit();
}

// Check password is not empty
if (empty($password)) {
    $_SESSION['register_error'] = 'Please enter a password.';
    header("Location: register.php");
    exit();
}
    // Check security question selected
    if (empty($security_question) || empty($security_answer)) {
        $_SESSION['register_error'] = 'Please select a security question and provide an answer.';
        header("Location: register.php");
        exit();
    }
 
    // Check if email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $checkEmail = $stmt->get_result();
 
    if ($checkEmail->num_rows > 0) {
        $_SESSION['register_error'] = 'This email is already registered. Please login.';
        header("Location: register.php");
        exit();
    }
 
    // Save to database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $role, $security_question, $security_answer);
    $stmt->execute();
 
    $_SESSION['register_success'] = 'Registration successful! Please log in.';
    header("Location: login.php");
    exit();
}
 
// Login
if (isset($_POST['Login'])) {
    $email = trim($_POST['Email'] ?? '');
    $password = trim($_POST['Password'] ?? '');
 
    if (!empty($email) && !empty($password)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
 
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['name'] = $user['name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = strtolower($user['role']);
                $_SESSION['user_id'] = $user['id'];
 
                if ($_SESSION['role'] === 'admin') {
                    header("Location: admin_page.php");
                } else {
                    header("Location: user_page.php");
                }
                exit();
            }
        }
 
        $_SESSION['login_error'] = 'Incorrect email or password. Please try again.';
        header("Location: login.php");
        exit();
 
    } else {
        $_SESSION['login_error'] = 'Please provide both email and password.';
        header("Location: login.php");
        exit();
    }
}
?>