<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$msg = '';
$msg_type = '';

// Fetch Current Data
$stmt = $conn->prepare("SELECT username, email, password FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Password Update Fields
    $old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
    $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if (empty($username) || empty($email)) {
        $msg = "Username and Email are required.";
        $msg_type = "danger";
    } else {
        // Update Info
        $update_sql = "UPDATE users SET username = ?, email = ? WHERE id = ?";
        $types = "ssi";
        $params = [$username, $email, $user_id];

        // Update Password if Requested
        if (!empty($new_password)) {
            if (password_verify($old_password, $user['password'])) {
                if ($new_password === $confirm_password) {
                    $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_sql = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
                    $types = "sssi";
                    $params = [$username, $email, $hashed_new, $user_id];
                } else {
                    $msg = "New passwords do not match.";
                    $msg_type = "danger";
                }
            } else {
                $msg = "Incorrect old password.";
                $msg_type = "danger";
            }
        }

        if (empty($msg)) {
            $stmt = $conn->prepare($update_sql);
            if ($stmt) {
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    $msg = "Profile updated successfully.";
                    $msg_type = "success";
                    // Update Session Username if changed
                    $_SESSION['username'] = $username;
                    // Refresh data
                    $user['username'] = $username;
                    $user['email'] = $email;
                } else {
                    $msg = "Error updating profile: " . $stmt->error;
                    $msg_type = "danger";
                }
                $stmt->close();
            } else {
                $msg = "Database prepare error: " . $conn->error;
                $msg_type = "danger";
            }
        }
    }
}
?>

<div class="container py-5 d-flex justify-content-center">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 600px;">
        <h2 class="text-white mb-4 border-bottom border-secondary pb-2">Edit Profile</h2>

        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $msg; ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_profile.php">
            <!-- Account Info -->
            <h5 class="text-info mb-3">Account Information</h5>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
            </div>
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <!-- Password Change -->
            <h5 class="text-info mb-3 mt-4">Change Password <span class="text-muted small fs-6">(Optional)</span></h5>
            <div class="mb-3">
                <label for="old_password" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="old_password" name="old_password">
            </div>
            <div class="row g-2 mb-3">
                <div class="col-md-6">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password">
                </div>
                <div class="col-md-6">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-premium btn-lg">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
