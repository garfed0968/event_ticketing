<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $title = isset($_POST['title']) ? trim($_POST['title']) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $theme = isset($_POST['theme']) ? trim($_POST['theme']) : '';
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0;
    $items = isset($_POST['items']) ? trim($_POST['items']) : '';
    $cbe_account = isset($_POST['cbe_account']) ? trim($_POST['cbe_account']) : '';
    $awash_account = isset($_POST['awash_account']) ? trim($_POST['awash_account']) : '';
    $dashen_account = isset($_POST['dashen_account']) ? trim($_POST['dashen_account']) : '';
    $abissinia_account = isset($_POST['abissinia_account']) ? trim($_POST['abissinia_account']) : '';
    $telebirr_account = isset($_POST['telebirr_account']) ? trim($_POST['telebirr_account']) : '';
    $organizer_id = intval($_SESSION['user_id']);

    if ($event_id === 0) {
        header("Location: organizer_dashboard.php?error=Invalid Event ID");
        exit;
    }

    // Serverside Validation
    $today = date('Y-m-d');
    if ($date < $today) {
        header("Location: edit_event.php?id=$event_id&error=Date cannot be in the past");
        exit;
    }
    if ($price < 0) {
        header("Location: edit_event.php?id=$event_id&error=Price cannot be negative");
        exit;
    }

    // Update query ensuring it only modifies if owned by the logged-in organizer
    $stmt = $conn->prepare("UPDATE events SET title = ?, date = ?, time = ?, location = ?, theme = ?, price = ?, items = ?, cbe_account = ?, awash_account = ?, dashen_account = ?, abissinia_account = ?, telebirr_account = ? WHERE id = ? AND organizer_id = ?");
    
    if ($stmt) {
        $stmt->bind_param("sssssdssssssii", $title, $date, $time, $location, $theme, $price, $items, $cbe_account, $awash_account, $dashen_account, $abissinia_account, $telebirr_account, $event_id, $organizer_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                header("Location: organizer_dashboard.php?success=Event updated successfully");
            } else {
                // Could be 0 rows affected if they submitted without changing anything
                header("Location: organizer_dashboard.php?success=Event updated (no changes made)");
            }
        } else {
            header("Location: edit_event.php?id=$event_id&error=" . urlencode($stmt->error));
        }
        $stmt->close();
    } else {
        header("Location: edit_event.php?id=$event_id&error=" . urlencode("Database prepare error: " . $conn->error));
    }
} else {
    header("Location: organizer_dashboard.php");
}
?>
