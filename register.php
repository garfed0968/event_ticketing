<?php include 'header.php'; ?>
<?php
include 'db_connect.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role = isset($_POST['role']) ? $_POST['role'] : '';

    // Basic Backend Validation
    if (empty($username) || empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            $error = "Email already registered.";
        } else {
            // Insert User
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);

                if ($stmt->execute()) {
                    $success = "Registration successful! You can now <a href='login.php' class='text-primary'>login</a>.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $error = "Database prepare error: " . $conn->error;
            }
        }
        if ($check_stmt) $check_stmt->close();
    }
}

?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="glass-card p-5 animate-fade-in" style="width: 100%; max-width: 500px;">
        <h2 class="text-center mb-4 fw-bold">Create Account</h2>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert"><?php echo $success; ?></div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="username" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-4">
                <label for="role" class="form-label">I am a...</label>
                <select class="form-select" id="role" name="role" required>
                    <option value="attender">Event Attender (Looking for tickets)</option>
                    <option value="organizer">Event Organizer (Creating events)</option>
                </select>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-premium btn-lg">Register</button>
            </div>
            <div class="text-center mt-3">
                <small>Already have an account? <a href="login.php" class="text-info text-decoration-none">Login here</a></small>
            </div>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>
