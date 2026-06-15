<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $username, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                // Password Correct, Start Session
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;

                if ($role == 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($role == 'organizer') {
                    header("Location: organizer_dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email.";
        }
        $stmt->close();
        } else {
            $error = "Database prepare error.";
        }
    }
}
?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 450px;">
        <h2 class="text-center mb-4 fw-bold">Welcome Back</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger text-center" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0" id="email" name="email" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-white"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-premium btn-lg">Login</button>
            </div>
            <div class="text-center mt-3">
                <small><a href="forgot_password.php" class="text-warning text-decoration-none">Forgot Password?</a></small>
            </div>
            <div class="text-center mt-2">
                <small>Don't have an account? <a href="register.php" class="text-info text-decoration-none">Register here</a></small>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
