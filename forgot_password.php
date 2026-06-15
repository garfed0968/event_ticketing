<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    
    // Check if email exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Insert into password_resets
            $insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            if ($insert) {
                $insert->bind_param("sss", $email, $token, $expires);
                
                if ($insert->execute()) {
                    // SIMULATE SENDING EMAIL
                    header("Location: send_reset.php?email=" . urlencode($email) . "&token=" . $token);
                    exit;
                } else {
                    $error = "Something went wrong. Try again.";
                }
                $insert->close();
            } else {
                $error = "Database prepare error handling reset.";
            }
        } else {
            $error = "Email not found in our records.";
        }
        $stmt->close();
    } else {
        $error = "Database connection error.";
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-3 fw-bold">Forgot Password</h2>
        <p class="text-white-50 text-center mb-4">Enter your email to receive a reset link.</p>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="forgot_password.php">
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-premium btn-lg">Send Reset Link</button>
                <a href="login.php" class="btn btn-outline-light">Back to Login</a>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
