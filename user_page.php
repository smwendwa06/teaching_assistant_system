<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
// Check application and school assignment
$application = null;
$assignedSchool = null;
 
$stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $application = $result->fetch_assoc();
 
    // Check school assignment
    $stmt2 = $conn->prepare("SELECT * FROM schools WHERE assigned_volunteer_id = ?");
    $stmt2->bind_param("i", $application['id']);
    $stmt2->execute();
    $schoolResult = $stmt2->get_result();
    $assignedSchool = $schoolResult->num_rows > 0 ? $schoolResult->fetch_assoc() : null;
}
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>You are logged in as a <strong>Volunteer</strong>. Use the menu above to navigate the system.</p>
    </div>
 
    <!-- School assignment alert -->
    <?php if ($assignedSchool): ?>
        <div style="background:#d4edda; border-left: 5px solid #28a745; border-radius:8px; padding:20px; margin-bottom:25px;">
            <h3 style="color:#155724; margin-bottom:10px;">🏫 You Have Been Assigned a School!</h3>
            <p style="color:#155724; margin:0;"><strong>School:</strong> <?php echo htmlspecialchars($assignedSchool['school_name']); ?></p>
            <p style="color:#155724; margin:5px 0;"><strong>Location:</strong> <?php echo htmlspecialchars($assignedSchool['location']); ?></p>
            <p style="color:#155724; margin:0;"><strong>Subject:</strong> <?php echo htmlspecialchars($assignedSchool['subject_needed']); ?></p>
            <br>
            <a href="apply.php" class="card-btn">View Full Details</a>
        </div>
    <?php elseif ($application && $application['status'] === 'approved'): ?>
        <div style="background:#fff3cd; border-left: 5px solid #ffc107; border-radius:8px; padding:20px; margin-bottom:25px;">
            <h3 style="color:#856404; margin-bottom:5px;">✅ Application Approved!</h3>
            <p style="color:#856404; margin:0;">Your application has been approved. The administrator will assign you to a school shortly.</p>
        </div>
    <?php elseif ($application && $application['status'] === 'pending'): ?>
        <div style="background:#cce5ff; border-left: 5px solid #004085; border-radius:8px; padding:20px; margin-bottom:25px;">
            <h3 style="color:#004085; margin-bottom:5px;">⏳ Application Pending</h3>
            <p style="color:#004085; margin:0;">Your application is being reviewed by the administrator.</p>
        </div>
    <?php endif; ?>
 
    <div class="card-grid">
        <div class="card">
            <div class="card-icon">📝</div>
            <h3>Apply to Teach</h3>
            <p>Submit your application to volunteer as a teaching assistant in a school near you.</p>
            <a href="apply.php" class="card-btn">Apply Now</a>
        </div>
 
        <div class="card">
            <div class="card-icon">📚</div>
            <h3>Training & Orientation</h3>
            <p>Access training materials to prepare you for your role as a teaching assistant.</p>
            <a href="training.php" class="card-btn">View Training</a>
        </div>
 
        <div class="card">
            <div class="card-icon">💬</div>
            <h3>Feedback</h3>
            <p>Submit feedback about your experience as a volunteer teaching assistant.</p>
            <a href="feedback.php" class="card-btn">Give Feedback</a>
        </div>
    </div>
</div>
 
<?php require 'footer.php'; ?>