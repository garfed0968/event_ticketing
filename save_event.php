<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $date = trim($_POST['date']);
    $time = trim($_POST['time']);
    $location = trim($_POST['location']);
    $theme = trim($_POST['theme']);
    $price = floatval($_POST['price']);
    $items = isset($_POST['items']) ? trim($_POST['items']) : '';
    $cbe_account = isset($_POST['cbe_account']) ? trim($_POST['cbe_account']) : '';
    $awash_account = isset($_POST['awash_account']) ? trim($_POST['awash_account']) : '';
    $dashen_account = isset($_POST['dashen_account']) ? trim($_POST['dashen_account']) : '';
    $abissinia_account = isset($_POST['abissinia_account']) ? trim($_POST['abissinia_account']) : '';
    $telebirr_account = isset($_POST['telebirr_account']) ? trim($_POST['telebirr_account']) : '';
    $organizer_id = intval($_SESSION['user_id']);

    // Serverside Validation
    $today = date('Y-m-d');
    if ($date < $today) {
        header("Location: create_event.php?error=Date cannot be in the past");
        exit;
    }
    if ($price < 0) {
        header("Location: create_event.php?error=Price cannot be negative");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO events (organizer_id, title, date, time, location, theme, price, items, cbe_account, awash_account, dashen_account, abissinia_account, telebirr_account) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt) {
        // use proper variables for bind_param
        $stmt->bind_param("isssssdssssss", $organizer_id, $title, $date, $time, $location, $theme, $price, $items, $cbe_account, $awash_account, $dashen_account, $abissinia_account, $telebirr_account);

        if ($stmt->execute()) {
            header("Location: organizer_dashboard.php?success=Event created successfully");
        } else {
            header("Location: create_event.php?error=" . urlencode($stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: create_event.php?error=" . urlencode("Database prepare error: " . $conn->error));
    }
}
?>
