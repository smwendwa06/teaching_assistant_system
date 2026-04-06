<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
require 'config.php';
 
$success = '';
$error = '';
 
// Handle adding a new school
if (isset($_POST['add_school'])) {
    $school_name = trim($_POST['school_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $subject_needed = trim($_POST['subject_needed'] ?? '');
 
    if (empty($school_name) || empty($location) || empty($subject_needed)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("INSERT INTO schools (school_name, location, subject_needed) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $school_name, $location, $subject_needed);
        $stmt->execute();
        $success = 'School added successfully!';
    }
}
 
// Handle assigning a volunteer to a school
if (isset($_POST['assign_volunteer'])) {
    $school_id = (int)$_POST['school_id'];
    $app_id = (int)$_POST['app_id'];
 
    $stmt = $conn->prepare("UPDATE schools SET assigned_volunteer_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $app_id, $school_id);
    $stmt->execute();
    $success = 'Volunteer assigned to school successfully!';
}
 
// Handle removing assignment
if (isset($_POST['remove_assignment'])) {
    $school_id = (int)$_POST['school_id'];
    $stmt = $conn->prepare("UPDATE schools SET assigned_volunteer_id = NULL WHERE id = ?");
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $success = 'Volunteer assignment removed.';
}
 
// Fetch all schools with assigned volunteer info
$schools = $conn->query("
    SELECT s.*, a.name as volunteer_name, a.email as volunteer_email, a.subject as volunteer_subject
    FROM schools s
    LEFT JOIN applications a ON s.assigned_volunteer_id = a.id
    ORDER BY s.created_at DESC
");
 
// Fetch approved volunteers for assignment dropdown
$volunteers = $conn->query("SELECT id, name, email, subject, location FROM applications WHERE status = 'approved'");
 
require 'header.php';
?>
 
<div class="main-content">
    <div class="welcome-banner">
        <h1>Schools Management</h1>
        <p>Add schools facing teacher shortages and assign approved volunteers to them.</p>
    </div>
 
    <?php if ($success): ?>
        <div class="success-message"><?php echo $success; ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>
 
    <!-- Add School Form -->
    <div class="form-section" style="margin-bottom:30px;">
        <h2>Add a School</h2>
        <form method="POST" action="schools.php">
            <label>School Name</label>
            <input type="text" name="school_name" placeholder="e.g. Mukuru Primary School" required>
 
            <label>Location / County</label>
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
 
            <label>Subject Needed</label>
            <select name="subject_needed" required>
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
 
            <button type="submit" name="add_school">Add School</button>
        </form>
    </div>
 
    <!-- Schools Table -->
    <div class="section-box">
        <h3>All Schools</h3>
 
        <?php if ($schools->num_rows === 0): ?>
            <p style="color:#999; text-align:center; padding:20px;">No schools added yet.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>School Name</th>
                    <th>Location</th>
                    <th>Subject Needed</th>
                    <th>Assigned Volunteer</th>
                    <th>Assign</th>
                </tr>
                <?php while($row = $schools->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['school_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['location']); ?></td>
                        <td><?php echo htmlspecialchars($row['subject_needed']); ?></td>
                        <td>
                            <?php if ($row['volunteer_name']): ?>
                                <strong><?php echo htmlspecialchars($row['volunteer_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['volunteer_subject']); ?></small>
                            <?php else: ?>
                                <span style="color:#999;">Not assigned</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['assigned_volunteer_id']): ?>
                                <!-- Remove assignment -->
                                <form method="POST" action="schools.php" style="display:inline;">
                                    <input type="hidden" name="school_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="remove_assignment" class="btn-reject">Remove</button>
                                </form>
                            <?php else: ?>
                                <!-- Assign volunteer -->
                                <form method="POST" action="schools.php" style="display:inline; display:flex; gap:8px; align-items:center;">
                                    <input type="hidden" name="school_id" value="<?php echo $row['id']; ?>">
                                    <select name="app_id" required style="margin:0; padding:6px; font-size:13px;">
                                        <option value="">-- Select Volunteer --</option>
                                        <?php
                                        $volunteers->data_seek(0);
                                        while($vol = $volunteers->fetch_assoc()):
                                        ?>
                                            <option value="<?php echo $vol['id']; ?>">
                                                <?php echo htmlspecialchars($vol['name']); ?> — <?php echo htmlspecialchars($vol['subject']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                    <button type="submit" name="assign_volunteer" class="btn-approve" style="width:auto; padding:6px 12px;">Assign</button>
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