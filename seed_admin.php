<?php
include 'db_connect.php';

$username = "Admin";
$email = "admin@event.com";
$password = "admin123";
$role = "admin";

// Check if admin exists
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows == 0) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $email, $hashed_password, $role);
    
    if ($stmt->execute()) {
        echo "Admin account created successfully.<br>";
        echo "Email: $email<br>";
        echo "Password: $password<br>";
    } else {
        echo "Error creating admin: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "Admin account already exists.";
}

$check->close();
$conn->close();
?>
