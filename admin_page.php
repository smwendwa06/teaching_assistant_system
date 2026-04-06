<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
// USER MANAGEMENT
 
$editUser = null;
$addError = '';
 
// Delete user
if (isset($_POST['delete_user'])) {
    $uid = (int)$_POST['user_id'];
    $conn->query("DELETE FROM applications WHERE user_id = $uid");
    $conn->query("DELETE FROM feedback WHERE user_id = $uid");
    $conn->query("DELETE FROM users WHERE id = $uid");
    header("Location: admin_page.php");
    exit();
}
 
// Load user into edit form
if (isset($_GET['edit'])) {
    $uid      = (int)$_GET['edit'];
    $editUser = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
}
 
// Save edited user (name, email, role)
if (isset($_POST['update_user'])) {
    $uid   = (int)$_POST['user_id'];
    $name  = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role  = trim($_POST['role']);
    $stmt  = $conn->prepare("UPDATE users SET name=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("sssi", $name, $email, $role, $uid);
    $stmt->execute();
    header("Location: admin_page.php");
    exit();
}
 
// Reset password for a user
if (isset($_POST['reset_password'])) {
    $uid      = (int)$_POST['user_id'];
    $newPass  = trim($_POST['new_password']);
    $confPass = trim($_POST['confirm_password']);
    if (empty($newPass)) {
        $editUser = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
        $passError = 'Password cannot be empty.';
    } elseif ($newPass !== $confPass) {
        $editUser  = $conn->query("SELECT * FROM users WHERE id = $uid")->fetch_assoc();
        $passError = 'Passwords do not match.';
    } else {
        $hashed = password_hash($newPass, PASSWORD_DEFAULT);
        $stmt   = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashed, $uid);
        $stmt->execute();
        header("Location: admin_page.php");
        exit();
    }
}
 
// Add new user
if (isset($_POST['add_user'])) {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $role     = trim($_POST['role']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $check    = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $addError = 'Email already registered.';
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        $stmt->execute();
        header("Location: admin_page.php");
        exit();
    }
}
 
// ───── REPORTS DATA ─────
$totalUsers      = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$totalAdmins     = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
$totalApps       = $conn->query("SELECT COUNT(*) as count FROM applications")->fetch_assoc()['count'];
$pendingApps     = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'pending'")->fetch_assoc()['count'];
$approvedApps    = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'approved'")->fetch_assoc()['count'];
$rejectedApps    = $conn->query("SELECT COUNT(*) as count FROM applications WHERE status = 'rejected'")->fetch_assoc()['count'];
$totalFeedback   = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
$avgRating       = $conn->query("SELECT ROUND(AVG(rating),1) as avg FROM feedback")->fetch_assoc()['avg'];
$totalSchools    = $conn->query("SELECT COUNT(*) as count FROM schools")->fetch_assoc()['count'];
$assignedSchools = $conn->query("SELECT COUNT(*) as count FROM schools WHERE assigned_volunteer_id IS NOT NULL")->fetch_assoc()['count'];
 
// ───── USERS TABLE ─────
$result = $conn->query("SELECT id, name, email, role FROM users");
 
require 'header.php';
?>
 
<div class="main-content">
 
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
        <p>You are logged in as an <strong>Admin</strong>. Use the menu above to manage the system.</p>
    </div>
 
    <!-- Quick Action Cards -->
    <div class="card-grid" style="margin-bottom:30px;">
        <div class="card">
            <div class="card-icon">📋</div>
            <h3>Applications</h3>
            <p>View and manage all volunteer applications submitted by users.</p>
            <a href="applications.php" class="card-btn">View Applications</a>
        </div>
        <div class="card">
            <div class="card-icon">🏫</div>
            <h3>Schools</h3>
            <p>Manage schools facing teacher shortages and assign volunteers to them.</p>
            <a href="schools.php" class="card-btn">Manage Schools</a>
        </div>
        <div class="card">
            <div class="card-icon">💬</div>
            <h3>Feedback</h3>
            <p>View feedback submitted by volunteers about the programme.</p>
            <a href="feedback.php" class="card-btn">View Feedback</a>
        </div>
    </div>
 
    <!-- ───── REPORTS SECTION ───── -->
    <div class="section-box">
        <h3>📊 System Reports</h3>
 
        <!-- Stat Cards -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(150px,1fr)); gap:16px; margin-bottom:25px;">
            <div style="background:#f0f4f8; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#0d2b55;"><?php echo $totalUsers; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Volunteers Registered</div>
            </div>
            <div style="background:#fff3cd; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#856404;"><?php echo $pendingApps; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Pending Applications</div>
            </div>
            <div style="background:#d4edda; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#155724;"><?php echo $approvedApps; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Approved Applications</div>
            </div>
            <div style="background:#f8d7da; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#a42834;"><?php echo $rejectedApps; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Rejected Applications</div>
            </div>
            <div style="background:#e2e3ff; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#3730a3;"><?php echo $totalFeedback; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Feedback Received</div>
            </div>
            <div style="background:#d1ecf1; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#0c5460;"><?php echo $avgRating ?? 'N/A'; ?> ⭐</div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Avg Feedback Rating</div>
            </div>
            <div style="background:#f0f4f8; border-radius:8px; padding:16px; text-align:center;">
                <div style="font-size:28px; font-weight:700; color:#0d2b55;"><?php echo $assignedSchools; ?>/<?php echo $totalSchools; ?></div>
                <div style="font-size:13px; color:#666; margin-top:4px;">Schools Assigned</div>
            </div>
        </div>
 
        <!-- Applications Breakdown -->
        <h4 style="color:#0d2b55; margin-bottom:12px;">Applications Breakdown</h4>
        <table style="margin-bottom:25px;">
            <tr><th>Metric</th><th>Count</th></tr>
            <tr><td>Total Applications</td><td><?php echo $totalApps; ?></td></tr>
            <tr><td>Pending</td><td><span class="badge badge-pending"><?php echo $pendingApps; ?></span></td></tr>
            <tr><td>Approved</td><td><span class="badge badge-approved"><?php echo $approvedApps; ?></span></td></tr>
            <tr><td>Rejected</td><td><span class="badge badge-rejected"><?php echo $rejectedApps; ?></span></td></tr>
        </table>
 
        <!-- Recent Feedback -->
        <h4 style="color:#0d2b55; margin-bottom:12px;">Recent Feedback</h4>
        <?php
        $recentFeedback = $conn->query("SELECT name, message, rating, submitted_at FROM feedback ORDER BY submitted_at DESC LIMIT 5");
        if ($recentFeedback->num_rows === 0): ?>
            <p style="color:#999; text-align:center; padding:10px;">No feedback submitted yet.</p>
        <?php else: ?>
            <table>
                <tr><th>Volunteer</th><th>Message</th><th>Rating</th><th>Date</th></tr>
                <?php while($fb = $recentFeedback->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fb['name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($fb['message'], 0, 60)) . (strlen($fb['message']) > 60 ? '...' : ''); ?></td>
                        <td><?php for($i = 0; $i < $fb['rating']; $i++) echo '⭐'; ?></td>
                        <td><?php echo date('d M Y', strtotime($fb['submitted_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
 
    <!-- ───── ALL REGISTERED USERS ───── -->
    <div class="section-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
            <h3 style="margin:0;">All Registered Users</h3>
            <button onclick="document.getElementById('add-user-form').style.display='block'; this.style.display='none';"
                class="btn-approve" style="padding:8px 16px;">+ Add User</button>
        </div>
 
        <!-- Add User Form (hidden by default) -->
        <div id="add-user-form" style="display:none; background:#f0f4f8; border-radius:8px; padding:20px; margin-bottom:20px;">
            <h4 style="color:#0d2b55; margin-bottom:15px;">Add New User</h4>
            <?php if (!empty($addError)): ?>
                <div class="error-message"><?php echo $addError; ?></div>
            <?php endif; ?>
            <form method="POST" action="admin_page.php" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div>
                    <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Full Name</label>
                    <input type="text" name="name" placeholder="Full Name" required style="margin-bottom:0;">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Email</label>
                    <input type="email" name="email" placeholder="Email" required style="margin-bottom:0;">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Password</label>
                    <input type="password" name="password" placeholder="Password" required style="margin-bottom:0;">
                </div>
                <div>
                    <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Role</label>
                    <select name="role" required style="margin-bottom:0;">
                        <option value="user">Volunteer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div style="grid-column:span 2; display:flex; gap:10px; margin-top:5px;">
                    <button type="submit" name="add_user" class="btn-approve" style="width:auto; padding:8px 20px;">Save User</button>
                    <button type="button" onclick="document.getElementById('add-user-form').style.display='none'; document.querySelector('[onclick*=add-user-form]') && location.reload();"
                        class="btn-reject" style="width:auto; padding:8px 20px;">Cancel</button>
                </div>
            </form>
        </div>
 
        <!-- Edit User Form (shown when Edit is clicked) -->
        <?php if ($editUser): ?>
            <div style="background:#fff3cd; border-radius:8px; padding:20px; margin-bottom:20px;">
                <h4 style="color:#856404; margin-bottom:15px;">✏️ Editing: <?php echo htmlspecialchars($editUser['name']); ?></h4>
 
                <?php if (!empty($passError)): ?>
                    <div class="error-message"><?php echo $passError; ?></div>
                <?php endif; ?>
 
                <!-- Edit name / email / role -->
                <form method="POST" action="admin_page.php" style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                    <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                    <div>
                        <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Full Name</label>
                        <input type="text" name="name" value="<?php echo htmlspecialchars($editUser['name']); ?>" required style="margin-bottom:0;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Email</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($editUser['email']); ?>" required style="margin-bottom:0;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Role</label>
                        <select name="role" style="margin-bottom:0;">
                            <option value="user"  <?php echo $editUser['role'] === 'user'  ? 'selected' : ''; ?>>Volunteer</option>
                            <option value="admin" <?php echo $editUser['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div style="grid-column:span 2; display:flex; gap:10px; margin-top:5px;">
                        <button type="submit" name="update_user" class="btn-approve" style="width:auto; padding:8px 20px;">Update Details</button>
                        <a href="admin_page.php" class="btn-reject" style="padding:8px 20px; text-decoration:none; border-radius:6px; font-size:13px; display:inline-flex; align-items:center;">Cancel</a>
                    </div>
                </form>
 
                <!-- Divider -->
                <hr style="border:none; border-top:1px solid #e0c84a; margin:5px 0 18px;">
 
                <!-- Reset Password -->
                <h5 style="color:#856404; margin-bottom:12px;">🔒 Reset Password</h5>
                <form method="POST" action="admin_page.php" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                    <input type="hidden" name="user_id" value="<?php echo $editUser['id']; ?>">
                    <div>
                        <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">New Password</label>
                        <input type="password" name="new_password" placeholder="New Password" style="margin-bottom:0;">
                    </div>
                    <div>
                        <label style="font-size:13px; font-weight:500; display:block; margin-bottom:4px;">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" style="margin-bottom:0;">
                    </div>
                    <div style="grid-column:span 2; margin-top:5px;">
                        <button type="submit" name="reset_password" class="btn-approve" style="width:auto; padding:8px 20px;">Reset Password</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
 
        <!-- Users Table -->
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <span class="badge <?php echo $row['role'] === 'admin' ? 'badge-approved' : 'badge-pending'; ?>">
                            <?php echo ucfirst($row['role']); ?>
                        </span>
                    </td>
                    <td>
                        <!-- Edit -->
                        <a href="admin_page.php?edit=<?php echo $row['id']; ?>" class="btn-approve"
                            style="padding:5px 12px; text-decoration:none; border-radius:6px; font-size:13px; display:inline-block; margin:2px;">
                            ✏️ Edit
                        </a>
 
                        <!-- Delete (blocked for self) -->
                        <?php if ($row['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" action="admin_page.php" style="display:inline;"
                                onsubmit="return confirm('Delete <?php echo htmlspecialchars($row['name']); ?>? This will also remove their applications and feedback.');">
                                <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_user" class="btn-reject" style="padding:5px 12px; margin:2px;">🗑 Delete</button>
                            </form>
                        <?php else: ?>
                            <span style="color:#999; font-size:12px; margin-left:8px;">(you)</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
 
</div>
 
<?php require 'footer.php'; ?>