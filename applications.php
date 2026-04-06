<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
// Handle approve / reject / pending
if (isset($_POST['action']) && isset($_POST['app_id'])) {
    $action = $_POST['action'];
    $app_id = (int)$_POST['app_id'];
 
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE applications SET status = 'approved', rejected_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE applications SET status = 'rejected', rejected_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
    } elseif ($action === 'pending') {
        $stmt = $conn->prepare("UPDATE applications SET status = 'pending', rejected_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $app_id);
        $stmt->execute();
    }
 
    header("Location: applications.php");
    exit();
}
 
// Fetch all applications
$result = $conn->query("SELECT * FROM applications ORDER BY applied_at DESC");
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>📋 Volunteer Applications</h1>
        <p>Review and manage all volunteer applications submitted by users.</p>
    </div>
 
    <div class="section-box">
        <h3>All Applications</h3>
 
        <?php if ($result->num_rows === 0): ?>
            <p style="color:#999; text-align:center; padding:20px;">No applications submitted yet.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Location</th>
                    <th>Availability</th>
                    <th>Applied On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['availability']); ?></td>
                        <td><?php echo date('d M Y', strtotime($row['applied_at'])); ?></td>
                        <td>
                            <?php
                            $status = $row['status'];
                            $badgeClass = 'badge-pending';
                            if ($status === 'approved') $badgeClass = 'badge-approved';
                            if ($status === 'rejected') $badgeClass = 'badge-rejected';
                            ?>
                            <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                            <?php if ($status === 'rejected' && !empty($row['rejected_at'])): ?>
                                <br>
                                <small style="color:#999;">
                                    Can reapply: <?php echo date('d M Y', strtotime($row['rejected_at'] . ' +7 days')); ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'pending'): ?>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Approve this application?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve">Approve</button>
                                </form>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Reject this application?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject">Reject</button>
                                </form>
 
                            <?php elseif ($row['status'] === 'approved'): ?>
                                <span style="color:#999; font-size:12px; display:block; margin-bottom:4px;">Approved</span>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Change this to Rejected?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject">↩ Reject Instead</button>
                                </form>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Reset this application to Pending?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="pending">
                                    <button type="submit" style="background:#6c757d; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:13px; width:auto; margin:2px;">↺ Reset</button>
                                </form>
 
                            <?php elseif ($row['status'] === 'rejected'): ?>
                                <span style="color:#999; font-size:12px; display:block; margin-bottom:4px;">Rejected</span>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Change this to Approved?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve">↩ Approve Instead</button>
                                </form>
                                <form method="POST" action="applications.php" style="display:inline;"
                                    onsubmit="return confirm('Reset this application to Pending?')">
                                    <input type="hidden" name="app_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="action" value="pending">
                                    <button type="submit" style="background:#6c757d; color:#fff; border:none; padding:6px 14px; border-radius:6px; cursor:pointer; font-size:13px; width:auto; margin:2px;">↺ Reset</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
 
<?php require 'footer.php'; ?>