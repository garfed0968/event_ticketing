<?php
include 'db_connect.php';

// Users Table
// Updated to include 'admin' role
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('organizer', 'attender', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_users) === TRUE) {
    echo "Table 'users' created/checked successfully.<br>";
} else {
    echo "Error creating table 'users': " . $conn->error . "<br>";
}

// Events Table
$sql_events = "CREATE TABLE IF NOT EXISTS events (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    organizer_id INT(6) UNSIGNED,
    title VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    location VARCHAR(100) NOT NULL,
    theme VARCHAR(50),
    price DECIMAL(10, 2) DEFAULT 0.00,
    items TEXT,
    cbe_account VARCHAR(50) NULL,
    awash_account VARCHAR(50) NULL,
    dashen_account VARCHAR(50) NULL,
    abissinia_account VARCHAR(50) NULL,
    telebirr_account VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (organizer_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_events) === TRUE) {
    echo "Table 'events' created/checked successfully.<br>";
} else {
    echo "Error creating table 'events': " . $conn->error . "<br>";
}

// Tickets Table
// Updated to include 'refund_requested' and 'refunded' statuses
$sql_tickets = "CREATE TABLE IF NOT EXISTS tickets (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id INT(6) UNSIGNED,
    user_id INT(6) UNSIGNED,
    status ENUM('pending', 'approved', 'declined', 'refund_requested', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    payment_proof_path VARCHAR(255),
    refund_proof_path VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

if ($conn->query($sql_tickets) === TRUE) {
    echo "Table 'tickets' created/checked successfully.<br>";
} else {
    echo "Error creating table 'tickets': " . $conn->error . "<br>";
}

// Saved Events Table
$sql_saved = "CREATE TABLE IF NOT EXISTS saved_events (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED,
    event_id INT(6) UNSIGNED,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_save (user_id, event_id)
)";

if ($conn->query($sql_saved) === TRUE) {
    echo "Table 'saved_events' created/checked successfully.<br>";
} else {
    echo "Error creating table 'saved_events': " . $conn->error . "<br>";
}

// Password Resets Table
$sql_resets = "CREATE TABLE IF NOT EXISTS password_resets (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql_resets) === TRUE) {
    echo "Table 'password_resets' created/checked successfully.<br>";
} else {
    echo "Error creating table 'password_resets': " . $conn->error . "<br>";
}

$conn->close();
?>
