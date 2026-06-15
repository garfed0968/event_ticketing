<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $event_id = intval($_GET['id']);
    $user_id = intval($_SESSION['user_id']);

    if ($event_id > 0 && $user_id > 0) {
        // Check if already saved
        $check = $conn->prepare("SELECT id FROM saved_events WHERE user_id = ? AND event_id = ?");
        if ($check) {
            $check->bind_param("ii", $user_id, $event_id);
            $check->execute();
            $result = $check->get_result();

            if ($result->num_rows == 0) {
                // Not saved yet, insert it
                $insert = $conn->prepare("INSERT INTO saved_events (user_id, event_id) VALUES (?, ?)");
                if ($insert) {
                    $insert->bind_param("ii", $user_id, $event_id);
                    $insert->execute();
                    $insert->close();
                }
            } else {
                // Already saved, let's toggle / remove it
                $delete = $conn->prepare("DELETE FROM saved_events WHERE user_id = ? AND event_id = ?");
                if ($delete) {
                    $delete->bind_param("ii", $user_id, $event_id);
                    $delete->execute();
                    $delete->close();
                }
            }
            $check->close();
        }
    }
}

// Redirect back to referring page or default to events page
if(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
    header("Location: " . $_SERVER['HTTP_REFERER']);
} else {
    header("Location: events.php");
}
exit;
?>
