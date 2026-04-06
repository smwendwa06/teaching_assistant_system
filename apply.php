<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
$success = '';
$error = '';
 
// Check if user already has an application
$stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$existing = $stmt->get_result();
$application = $existing->num_rows > 0 ? $existing->fetch_assoc() : null;
 
// Check if volunteer has been assigned a school
$assignedSchool = null;
if ($application) {
    $app_id = $application['id'];
    $stmt = $conn->prepare("SELECT * FROM schools WHERE assigned_volunteer_id = ?");
    $stmt->bind_param("i", $app_id);
    $stmt->execute();
    $schoolResult = $stmt->get_result();
    $assignedSchool = $schoolResult->num_rows > 0 ? $schoolResult->fetch_assoc() : null;
}
 
// Check grace period for rejected applications
$canReapply = false;
$reapplyDate = null;
$daysLeft = 0;
 
if ($application && $application['status'] === 'rejected' && $application['rejected_at']) {
    $rejectedAt = strtotime($application['rejected_at']);
    $reapplyTimestamp = strtotime('+7 days', $rejectedAt);
    $reapplyDate = date('d M Y', $reapplyTimestamp);
    $daysLeft = ceil(($reapplyTimestamp - time()) / 86400);
    $canReapply = time() >= $reapplyTimestamp;
 
    if ($canReapply) {
        $stmt = $conn->prepare("DELETE FROM applications WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $application = null;
    }
}
 
// Handle new application submission
if (isset($_POST['submit']) && !$application) {
    $subject = trim($_POST['subject'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $availability = trim($_POST['availability'] ?? '');
 
    if (empty($subject) || empty($location) || empty($availability)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO applications (user_id, name, email, subject, location, availability) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $_SESSION['user_id'], $_SESSION['name'], $_SESSION['email'], $subject, $location, $availability);
        $stmt->execute();
        $success = 'Application submitted successfully! We will review it shortly.';
 
        $stmt = $conn->prepare("SELECT * FROM applications WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $existing = $stmt->get_result();
        $application = $existing->fetch_assoc();
    }
}
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>📝 Apply to Volunteer</h1>
        <p>Fill in the form below to apply as a volunteer teaching assistant.</p>
    </div>
 
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
 
    <?php if ($application && $application['status'] === 'rejected'): ?>
        <div class="section-box" style="text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:15px;">⏳</div>
            <h3 style="color:#a42834;">Your Application was Not Approved</h3>
            <p style="color:#666; margin-top:10px;">Your previous application was rejected. You may reapply after the 7-day grace period.</p>
            <br>
            <div style="background:#fff3cd; border-radius:8px; padding:15px; display:inline-block;">
                <strong style="color:#856404;">
                    <?php if ($daysLeft > 0): ?>
                        You can reapply in <span style="font-size:20px;"><?php echo $daysLeft; ?></span> day(s) — on <?php echo $reapplyDate; ?>
                    <?php else: ?>
                        You can reapply today! Please refresh this page.
                    <?php endif; ?>
                </strong>
            </div>
        </div>
 
    <?php elseif ($application): ?>
        <!-- Existing application status -->
        <div class="section-box">
            <h3>Your Application</h3>
            <p>You have already submitted an application. Here is your current status:</p>
            <br>
            <table>
                <tr><th>Field</th><th>Details</th></tr>
                <tr>
                    <td><strong>Name</strong></td>
                    <td><?php echo htmlspecialchars($application['name']); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($application['email']); ?></td>
                </tr>
                <tr>
                    <td><strong>Subject</strong></td>
                    <td><?php echo htmlspecialchars($application['subject']); ?></td>
                </tr>
                <tr>
                    <td><strong>Location</strong></td>
                    <td><?php echo htmlspecialchars($application['location']); ?></td>
                </tr>
                <tr>
                    <td><strong>Availability</strong></td>
                    <td><?php echo htmlspecialchars($application['availability']); ?></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td>
                        <?php
                        $status = $application['status'];
                        $badgeClass = 'badge-pending';
                        if ($status === 'approved') $badgeClass = 'badge-approved';
                        if ($status === 'rejected') $badgeClass = 'badge-rejected';
                        ?>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Applied On</strong></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($application['applied_at'])); ?></td>
                </tr>
            </table>
        </div>
 
        <!-- School Assignment Section -->
        <?php if ($application['status'] === 'approved'): ?>
            <div class="section-box" style="margin-top:20px;">
                <h3>🏫 School Assignment</h3>
                <?php if ($assignedSchool): ?>
                    <div class="success-message" style="margin-bottom:20px;">
                        You have been assigned to a school! Please report as soon as possible.
                    </div>
                    <table>
                        <tr><th>Field</th><th>Details</th></tr>
                        <tr>
                            <td><strong>School Name</strong></td>
                            <td><?php echo htmlspecialchars($assignedSchool['school_name']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Location</strong></td>
                            <td><?php echo htmlspecialchars($assignedSchool['location']); ?></td>
                        </tr>
                        <tr>
                            <td><strong>Subject Needed</strong></td>
                            <td><?php echo htmlspecialchars($assignedSchool['subject_needed']); ?></td>
                        </tr>
                    </table>
                <?php else: ?>
                    <div style="text-align:center; padding:20px;">
                        <div style="font-size:36px; margin-bottom:10px;">⏳</div>
                        <p style="color:#666;">Your application has been approved! The administrator will assign you to a school shortly. Please check back soon.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
 
    <?php else: ?>
        <!-- Application form -->
        <div class="form-section">
            <h2>Volunteer Application Form</h2>
            <form method="POST" action="apply.php">
                <label>Subject You Can Teach</label>
                <select name="subject" required>
                    <option value="">-- Select Subject --</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="English">English</option>
                    <option value="Kiswahili">Kiswahili</option>
                    <option value="Science">Science</option>
                    <option value="Social Studies">Social Studies</option>
                    <option value="Physics">Physics</option>
                    <option value="Chemistry">Chemistry</option>
                    <option value="Biology">Biology</option>
                    <option value="History">History</option>
                    <option value="Geography">Geography</option>
                    <option value="Computer Studies">Computer Studies</option>
                    <option value="Business Studies">Business Studies</option>
                </select>
 
                <label>Your County / Location</label>
                <select name="location" required>
                    <option value="">-- Select County --</option>
                    <option value="Nairobi">Nairobi</option>
                    <option value="Mombasa">Mombasa</option>
                    <option value="Kisumu">Kisumu</option>
                    <option value="Nakuru">Nakuru</option>
                    <option value="Eldoret">Eldoret</option>
                    <option value="Kiambu">Kiambu</option>
                    <option value="Machakos">Machakos</option>
                    <option value="Kilifi">Kilifi</option>
                    <option value="Meru">Meru</option>
                    <option value="Nyeri">Nyeri</option>
                    <option value="Baringo">Baringo</option>
                    <option value="Other">Other</option>
                </select>
 
                <label>Availability (Days per week)</label>
                <select name="availability" required>
                    <option value="">-- Select Availability --</option>
                    <option value="Weekdays only">Weekdays only</option>
                    <option value="Weekends only">Weekends only</option>
                    <option value="Monday to Wednesday">Monday to Wednesday</option>
                    <option value="Thursday to Friday">Thursday to Friday</option>
                    <option value="Full week">Full week (Mon - Fri)</option>
                    <option value="Flexible">Flexible</option>
                </select>
 
                <button type="submit" name="submit">Submit Application</button>
            </form>
        </div>
    <?php endif; ?>
</div>
 
<?php require 'footer.php'; ?>