<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);
    $organizer_id = intval($_SESSION['user_id']);

    if ($event_id > 0) {
        // Since database has ON DELETE CASCADE for foreign keys in tickets and saved_events
        // this query will automatically clean up related records when deleting the event.
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ? AND organizer_id = ?");
        
        if ($stmt) {
            $stmt->bind_param("ii", $event_id, $organizer_id);
            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    header("Location: organizer_dashboard.php?success=Event deleted successfully");
                } else {
                    header("Location: organizer_dashboard.php?error=Event not found or unauthorized");
                }
            } else {
                header("Location: organizer_dashboard.php?error=" . urlencode("Database error: " . $stmt->error));
            }
            $stmt->close();
        } else {
            header("Location: organizer_dashboard.php?error=" . urlencode("Database prepare error: " . $conn->error));
        }
    } else {
        header("Location: organizer_dashboard.php?error=Invalid Event ID");
    }
} else {
    header("Location: organizer_dashboard.php");
}
?>
