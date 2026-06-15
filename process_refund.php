<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'organizer') {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ticket_id = isset($_POST['ticket_id']) ? intval($_POST['ticket_id']) : 0;
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';
    $user_id = intval($_SESSION['user_id']); // Organizer

    if ($ticket_id === 0 || empty($action)) {
        header("Location: organizer_dashboard.php?error=Invalid request");
        exit;
    }

    // Verify ticket belongs to organizer's event AND is refund-requested
    $check_stmt = $conn->prepare("
        SELECT t.id FROM tickets t 
        JOIN events e ON t.event_id = e.id 
        WHERE t.id = ? AND e.organizer_id = ? AND t.status = 'refund_requested'
    ");
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $ticket_id, $user_id);
        $check_stmt->execute();

        if ($check_stmt->get_result()->num_rows > 0) {
            
            if ($action === 'refund') {
                // Handle File Upload
                $target_dir = "uploads/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }

                $file_name = basename($_FILES["refund_proof"]["name"]);
                $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $new_file_name = "refund_" . uniqid() . "_" . time() . "." . $imageFileType;
                $target_file = $target_dir . $new_file_name;

                // Check file size (Max 5MB)
                if ($_FILES["refund_proof"]["size"] > 5000000) {
                    header("Location: organizer_dashboard.php?error=File is too large.");
                    exit;
                }

                // Allow certain file formats
                if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf" ) {
                    header("Location: organizer_dashboard.php?error=Only JPG, JPEG, PNG & PDF files are allowed.");
                    exit;
                }

                if (move_uploaded_file($_FILES["refund_proof"]["tmp_name"], $target_file)) {
                    // Update database
                    $update_stmt = $conn->prepare("UPDATE tickets SET status = 'refunded', refund_proof_path = ? WHERE id = ?");
                    if ($update_stmt) {
                        $update_stmt->bind_param("si", $target_file, $ticket_id);
                        if ($update_stmt->execute()) {
                            header("Location: organizer_dashboard.php?success=Refund confirmed successfully");
                        } else {
                            header("Location: organizer_dashboard.php?error=Update failed");
                        }
                        $update_stmt->close();
                    } else {
                        header("Location: organizer_dashboard.php?error=Database error");
                    }
                } else {
                    header("Location: organizer_dashboard.php?error=Error uploading proof.");
                }
            } else {
                header("Location: organizer_dashboard.php?error=Invalid action");
            }
        } else {
            header("Location: organizer_dashboard.php?error=Unauthorized or invalid ticket status");
        }
        $check_stmt->close();
    } else {
        header("Location: organizer_dashboard.php?error=Database error");
    }
} else {
    header("Location: organizer_dashboard.php");
}
?>
