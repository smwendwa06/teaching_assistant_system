<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
$success = '';
$error   = '';
 
// DELETE FEEDBACK
if (isset($_POST['delete_feedback'])) {
    $fid = (int)$_POST['feedback_id'];
 
    if ($_SESSION['role'] === 'admin') {
        // Admin can delete anything
        $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
        $stmt->bind_param("i", $fid);
        $stmt->execute();
        $success = 'Feedback deleted successfully.';
 
    } else {
        // User can only delete their own feedback within 24 hours
        $stmt = $conn->prepare("SELECT * FROM feedback WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $fid, $_SESSION['user_id']);
        $stmt->execute();
        $fb = $stmt->get_result()->fetch_assoc();
 
        if (!$fb) {
            $error = 'Feedback not found.';
        } else {
            $submittedAt = strtotime($fb['submitted_at']);
            $hoursPassed = (time() - $submittedAt) / 3600;
 
            if ($hoursPassed > 24) {
                $error = 'You can only delete feedback within 24 hours of submitting it.';
            } else {
                $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $fid, $_SESSION['user_id']);
                $stmt->execute();
                $success = 'Your feedback has been deleted.';
            }
        }
    }
}
 
// ───── CHECK APPLICATION STATUS (users only) ─────
$appStatus = 'none';
if ($_SESSION['role'] === 'user') {
    $stmt = $conn->prepare("SELECT status FROM applications WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result      = $stmt->get_result();
    $application = $result->fetch_assoc();
    $appStatus   = $application['status'] ?? 'none';
}
 
// ───── SUBMIT FEEDBACK (approved users only) ─────
if (isset($_POST['submit_feedback']) && $_SESSION['role'] === 'user' && $appStatus === 'approved') {
    $message = trim($_POST['message'] ?? '');
    $rating  = (int)($_POST['rating'] ?? 0);
 
    if (empty($message) || $rating < 1 || $rating > 5) {
        $error = 'Please fill in all fields and select a rating.';
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, name, message, rating) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $_SESSION['user_id'], $_SESSION['name'], $message, $rating);
        $stmt->execute();
        $success = 'Thank you! Your feedback has been submitted successfully.';
    }
}
 
// ───── FETCH FEEDBACK ─────
if ($_SESSION['role'] === 'admin') {
    $feedbacks = $conn->query("SELECT * FROM feedback ORDER BY submitted_at DESC");
} else {
    $stmt = $conn->prepare("SELECT * FROM feedback WHERE user_id = ? ORDER BY submitted_at DESC");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $feedbacks = $stmt->get_result();
}
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>💬 Feedback</h1>
        <?php if ($_SESSION['role'] === 'admin'): ?>
            <p>View and manage all feedback submitted by volunteers about their experience.</p>
        <?php else: ?>
            <p>Share your experience as a volunteer teaching assistant to help us improve the programme.</p>
        <?php endif; ?>
    </div>
 
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
 
    <?php if ($_SESSION['role'] === 'user'): ?>
 
        <?php if ($appStatus === 'none'): ?>
            <div class="section-box" style="text-align:center; padding:40px;">
                <div style="font-size:48px; margin-bottom:15px;">📝</div>
                <h3 style="color:#0d2b55;">No Application Found</h3>
                <p style="color:#666; margin-top:10px;">You need to apply and be approved as a volunteer before submitting feedback.</p>
                <br>
                <a href="apply.php" class="card-btn">Apply Now</a>
            </div>
 
        <?php elseif ($appStatus === 'pending'): ?>
            <div class="section-box" style="text-align:center; padding:40px;">
                <div style="font-size:48px; margin-bottom:15px;">⏳</div>
                <h3 style="color:#856404;">Application Still Pending</h3>
                <p style="color:#666; margin-top:10px;">Feedback will be available once your application has been approved by the administrator.</p>
            </div>
 
        <?php elseif ($appStatus === 'rejected'): ?>
            <div class="section-box" style="text-align:center; padding:40px;">
                <div style="font-size:48px; margin-bottom:15px;">❌</div>
                <h3 style="color:#a42834;">Access Denied</h3>
                <p style="color:#666; margin-top:10px;">Your application was not approved. Only active volunteers can submit feedback.</p>
            </div>
 
        <?php else: ?>
            <!-- Approved — feedback form -->
            <div class="form-section" style="margin-bottom:30px;">
                <h2>Submit Feedback</h2>
                <form method="POST" action="feedback.php">
                    <label>Your Experience / Comments</label>
                    <textarea name="message" rows="5"
                        placeholder="Share your experience, challenges faced, or suggestions for improvement..."
                        required
                        style="width:100%; padding:12px; background:#f0f4f8; border:1px solid #ddd; border-radius:6px; font-size:15px; color:#333; margin-bottom:18px; font-family:Poppins, sans-serif; resize:vertical;"></textarea>
 
                    <label>Rate Your Experience</label>
                    <select name="rating" required>
                        <option value="">-- Select Rating --</option>
                        <option value="5">⭐⭐⭐⭐⭐ Excellent</option>
                        <option value="4">⭐⭐⭐⭐ Good</option>
                        <option value="3">⭐⭐⭐ Average</option>
                        <option value="2">⭐⭐ Poor</option>
                        <option value="1">⭐ Very Poor</option>
                    </select>
 
                    <button type="submit" name="submit_feedback">Submit Feedback</button>
                </form>
            </div>
 
            <!-- User's previous feedback -->
            <div class="section-box">
                <h3>Your Previous Feedback</h3>
                <p style="color:#888; font-size:13px; margin-bottom:15px;">
                    ⏱ You can delete your feedback within <strong>24 hours</strong> of submitting it.
                </p>
 
                <?php if ($feedbacks->num_rows === 0): ?>
                    <p style="color:#999; text-align:center; padding:20px;">You have not submitted any feedback yet.</p>
                <?php else: ?>
                    <table>
                        <tr>
                            <th>Message</th>
                            <th>Rating</th>
                            <th>Submitted On</th>
                            <th>Action</th>
                        </tr>
                        <?php while($row = $feedbacks->fetch_assoc()):
                            $hoursPassed = (time() - strtotime($row['submitted_at'])) / 3600;
                            $canDelete   = $hoursPassed <= 24;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['message']); ?></td>
                                <td><?php for($i = 0; $i < $row['rating']; $i++) echo '⭐'; ?></td>
                                <td>
                                    <?php echo date('d M Y, h:i A', strtotime($row['submitted_at'])); ?>
                                    <?php if ($canDelete): ?>
                                        <br><small style="color:#28a745;">
                                            <?php echo round(24 - $hoursPassed); ?>h left to delete
                                        </small>
                                    <?php else: ?>
                                        <br><small style="color:#999;">Delete window closed</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($canDelete): ?>
                                        <form method="POST" action="feedback.php" style="display:inline;"
                                            onsubmit="return confirm('Are you sure you want to delete this feedback? This cannot be undone.')">
                                            <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_feedback" class="btn-reject"
                                                style="padding:5px 12px;">🗑 Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color:#ccc; font-size:13px;">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php endif; ?>
            </div>
        <?php endif; ?>
 
    <?php else: ?>
        <!-- Admin view — all feedback with unrestricted delete -->
        <div class="section-box">
            <h3>All Feedback Received</h3>
 
            <?php if ($feedbacks->num_rows === 0): ?>
                <p style="color:#999; text-align:center; padding:20px;">No feedback submitted yet.</p>
            <?php else: ?>
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Volunteer</th>
                        <th>Message</th>
                        <th>Rating</th>
                        <th>Submitted On</th>
                        <th>Action</th>
                    </tr>
                    <?php while($row = $feedbacks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td><?php for($i = 0; $i < $row['rating']; $i++) echo '⭐'; ?></td>
                            <td><?php echo date('d M Y', strtotime($row['submitted_at'])); ?></td>
                            <td>
                                <form method="POST" action="feedback.php" style="display:inline;"
                                    onsubmit="return confirm('Delete this feedback entry? This cannot be undone.')">
                                    <input type="hidden" name="feedback_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="delete_feedback" class="btn-reject"
                                        style="padding:5px 12px;">🗑 Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
 
<?php require 'footer.php'; ?>