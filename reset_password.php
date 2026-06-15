<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

$error = '';
$success = '';

if (isset($_GET['token']) && isset($_GET['email'])) {
    $token = trim($_GET['token']);
    $email = trim($_GET['email']);

    // Verify Token
    $stmt = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
    if ($stmt) {
        $stmt->bind_param("ss", $email, $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $error = "Invalid or expired reset link.";
    }
        $stmt->close();
    } else {
        $error = "Database error verifying token.";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = isset($_POST['token']) ? trim($_POST['token']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Double check token validity on submit
        $check = $conn->prepare("SELECT id FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
        if ($check) {
            $check->bind_param("ss", $email, $token);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                // Update Password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                if ($update) {
                    $update->bind_param("ss", $hashed, $email);
                    
                    if ($update->execute()) {
                        // Delete used token
                        $del = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                        if ($del) {
                            $del->bind_param("s", $email);
                            $del->execute();
                            $del->close();
                        }
                        
                        $success = "Password reset successful! You can now login.";
                    } else {
                        $error = "Error updating password.";
                    }
                    $update->close();
                } else {
                    $error = "Database updating error.";
                }
            } else {
                $error = "Invalid or expired token.";
            }
            $check->close();
        } else {
            $error = "Database verify error.";
        }
    }
} else {
    header("Location: login.php");
    exit;
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-4 fw-bold">Reset Password</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?><br>
                <a href="login.php" class="alert-link">Login Now</a>
            </div>
        <?php elseif (!isset($_GET['token']) && empty($error)): ?>
             <!-- Should not reach here typically due to checks -->
        <?php else: ?>

        <form method="POST" action="reset_password.php">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-premium btn-lg">Reset Password</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
