<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['action'])) {
    $ticket_id = intval($_GET['id']);
    $action = trim($_GET['action']);
    $user_id = intval($_SESSION['user_id']);
    $role = $_SESSION['role'];

    if ($action == 'request_refund' && $role == 'attender') {
        // User requesting refund
        // Ensure user owns ticket and it is approved
        $check = $conn->prepare("SELECT id FROM tickets WHERE id = ? AND user_id = ? AND status = 'approved'");
        if ($check) {
            $check->bind_param("ii", $ticket_id, $user_id);
            $check->execute();
            
            if ($check->get_result()->num_rows > 0) {
                $update = $conn->prepare("UPDATE tickets SET status = 'refund_requested' WHERE id = ?");
                if ($update) {
                    $update->bind_param("i", $ticket_id);
                    if ($update->execute()) {
                        header("Location: my_tickets.php?success=Refund requested.");
                    } else {
                        header("Location: my_tickets.php?error=Error requesting refund.");
                    }
                    $update->close();
                } else {
                    header("Location: my_tickets.php?error=Database error.");
                }
            } else {
                header("Location: my_tickets.php?error=Invalid ticket for refund.");
            }
            $check->close();
        } else {
            header("Location: my_tickets.php?error=Database error.");
        }

    } elseif (($action == 'approve' || $action == 'decline') && $role == 'organizer') {
        // Organizer actions
        // Verify ticket belongs to organizer's event
        $check_stmt = $conn->prepare("
            SELECT t.id FROM tickets t 
            JOIN events e ON t.event_id = e.id 
            WHERE t.id = ? AND e.organizer_id = ?
        ");
        if ($check_stmt) {
            $check_stmt->bind_param("ii", $ticket_id, $user_id);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                $new_status = '';
                if ($action == 'approve') $new_status = 'approved';
                if ($action == 'decline') $new_status = 'declined';

                if ($new_status) {
                    $update_stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $new_status, $ticket_id);
                        if ($update_stmt->execute()) {
                            header("Location: organizer_dashboard.php?success=Action completed");
                        } else {
                            header("Location: organizer_dashboard.php?error=Update failed");
                        }
                        $update_stmt->close();
                    } else {
                        header("Location: organizer_dashboard.php?error=Database error");
                    }
                }
            } else {
                header("Location: organizer_dashboard.php?error=Unauthorized");
            }
            $check_stmt->close();
        } else {
            header("Location: organizer_dashboard.php?error=Database error");
        }
    } else {
        header("Location: index.php");
    }
} else {
    header("Location: index.php");
}
?>
