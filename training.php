<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
// Check if user is approved
$stmt = $conn->prepare("SELECT status FROM applications WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$status = $application['status'] ?? 'none';
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>📚 Training & Orientation</h1>
        <p>Prepare yourself for your role as a volunteer teaching assistant.</p>
    </div>
 
    <?php if ($status === 'none'): ?>
        <!-- No application yet -->
        <div class="section-box" style="text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:15px;">📝</div>
            <h3 style="color:#0d2b55;">You have not applied yet</h3>
            <p style="color:#666; margin-top:10px;">Please submit a volunteer application first before accessing training materials.</p>
            <br>
            <a href="apply.php" class="card-btn">Apply Now</a>
        </div>
 
    <?php elseif ($status === 'pending'): ?>
        <!-- Application pending -->
        <div class="section-box" style="text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:15px;">⏳</div>
            <h3 style="color:#856404;">Your Application is Pending</h3>
            <p style="color:#666; margin-top:10px;">Training materials will be available once your application has been approved by the administrator.</p>
        </div>
 
    <?php elseif ($status === 'rejected'): ?>
        <!-- Application rejected -->
        <div class="section-box" style="text-align:center; padding:40px;">
            <div style="font-size:48px; margin-bottom:15px;">❌</div>
            <h3 style="color:#a42834;">Your Application was Not Approved</h3>
            <p style="color:#666; margin-top:10px;">Unfortunately your application was not approved at this time. Please contact the administrator for more information.</p>
        </div>
 
    <?php else: ?>
        <!-- Approved — show training materials -->
        <div class="success-message">🎉 Your application has been approved! You now have access to all training materials.</div>
 
        <div class="card-grid">
            <div class="card">
                <div class="card-icon">👋</div>
                <h3>Module 1: Introduction</h3>
                <p>Learn about the Volunteer Teaching System, your role, and what is expected of you as a teaching assistant.</p>
                <div class="training-content">
                    <ul>
                        <li>Understanding your role</li>
                        <li>Code of conduct</li>
                        <li>Working with school staff</li>
                        <li>Professional etiquette</li>
                    </ul>
                </div>
            </div>
 
            <div class="card">
                <div class="card-icon">🏫</div>
                <h3>Module 2: Classroom Support</h3>
                <p>Practical skills for supporting teachers and students in the classroom environment.</p>
                <div class="training-content">
                    <ul>
                        <li>How to assist the main teacher</li>
                        <li>Managing small groups</li>
                        <li>Supporting struggling students</li>
                        <li>Classroom behaviour management</li>
                    </ul>
                </div>
            </div>
 
            <div class="card">
                <div class="card-icon">📖</div>
                <h3>Module 3: Teaching at the Right Level</h3>
                <p>Learn how to assess student understanding and deliver content at the appropriate level.</p>
                <div class="training-content">
                    <ul>
                        <li>Assessing student levels</li>
                        <li>Literacy and numeracy support</li>
                        <li>Simple teaching techniques</li>
                        <li>Giving effective feedback to students</li>
                    </ul>
                </div>
            </div>
 
            <div class="card">
                <div class="card-icon">🤝</div>
                <h3>Module 4: Community Engagement</h3>
                <p>How to engage with the school community, parents, and local leaders effectively.</p>
                <div class="training-content">
                    <ul>
                        <li>Building trust with the community</li>
                        <li>Working with parents</li>
                        <li>Extracurricular activities</li>
                        <li>Community development projects</li>
                    </ul>
                </div>
            </div>
 
            <div class="card">
                <div class="card-icon">⚠️</div>
                <h3>Module 5: Safeguarding</h3>
                <p>Understanding how to keep students safe and what to do if you have concerns.</p>
                <div class="training-content">
                    <ul>
                        <li>Child protection basics</li>
                        <li>Reporting concerns</li>
                        <li>Safe boundaries with students</li>
                        <li>Emergency procedures</li>
                    </ul>
                </div>
            </div>
 
            <div class="card">
                <div class="card-icon">📊</div>
                <h3>Module 6: Monitoring & Reporting</h3>
                <p>How to track student progress and report your impact to the programme coordinators.</p>
                <div class="training-content">
                    <ul>
                        <li>Keeping attendance records</li>
                        <li>Tracking student progress</li>
                        <li>Submitting weekly reports</li>
                        <li>Using the feedback system</li>
                    </ul>
                </div>
            </div>
        </div>
 
        <div class="section-box" style="margin-top:10px;">
            <h3>📋 Orientation Checklist</h3>
            <p style="margin-bottom:15px; color:#666;">Before you begin your placement, make sure you have completed the following:</p>
            <table>
                <tr><th>Task</th><th>Details</th></tr>
                <tr><td>✅ Read all 6 training modules</td><td>Available above</td></tr>
                <tr><td>✅ Confirm your school placement</td><td>Check with your administrator</td></tr>
                <tr><td>✅ Submit your feedback regularly</td><td>Use the Feedback page</td></tr>
                <tr><td>✅ Carry your volunteer ID at all times</td><td>Issued by the administrator</td></tr>
            </table>
        </div>
    <?php endif; ?>
</div>
 
<?php require 'footer.php'; ?>