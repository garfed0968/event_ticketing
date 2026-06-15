<?php include 'header.php'; ?>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="glass-card p-5 animate-fade-in text-center" style="width: 100%; max-width: 600px;">
        <i class="bi bi-envelope-paper h1 text-success mb-3"></i>
        <h2 class="fw-bold mb-3">Check Your Email</h2>
        <p class="text-white-50 mb-4">We've sent a password reset link to <strong><?php echo htmlspecialchars($_GET['email']); ?></strong></p>
        
        <div class="alert alert-warning text-dark text-start">
            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Developer Mode:</strong><br>
            Since we don't have an SMTP server, here is your reset link:<br><br>
            <a href="reset_password.php?token=<?php echo htmlspecialchars($_GET['token']); ?>&email=<?php echo urlencode($_GET['email']); ?>" class="fw-bold text-dark text-decoration-underline break-words">
                http://localhost/events/reset_password.php?token=<?php echo htmlspecialchars($_GET['token']); ?>
            </a>
        </div>

        <a href="login.php" class="btn btn-outline-light mt-3">Back to Login</a>
    </div>
</div>

<?php include 'footer.php'; ?>
