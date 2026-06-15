<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : '';
    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($event_id === 0 || $user_id === 0) {
        header("Location: events.php?error=Invalid Event or User");
        exit;
    }

    // Fetch event details to check if it's free
    $stmt_event = $conn->prepare("SELECT price FROM events WHERE id = ?");
    $stmt_event->bind_param("i", $event_id);
    $stmt_event->execute();
    $event_result = $stmt_event->get_result();
    
    if ($event_result->num_rows == 0) {
        $stmt_event->close();
        header("Location: events.php?error=Event not found");
        exit;
    }
    
    $event_data = $event_result->fetch_assoc();
    $is_free = ($event_data['price'] == 0);
    $stmt_event->close();

    $target_file = NULL;
    $status = 'pending';

    if ($is_free) {
        $payment_method = 'Free';
        $status = 'approved'; // Auto-approve free tickets
    } else {
        // File Upload Handling
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (!isset($_FILES["proof"]) || $_FILES["proof"]["error"] != 0) {
            header("Location: buy_ticket.php?event_id=$event_id&error=Payment proof is required for paid events");
            exit;
        }

        $file_name = basename($_FILES["proof"]["name"]);
        $imageFileType = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_file_name = uniqid() . "_" . time() . "." . $imageFileType;
        $target_file = $target_dir . $new_file_name;

        // Check file size (Max 5MB)
        if ($_FILES["proof"]["size"] > 5000000) {
            header("Location: buy_ticket.php?event_id=$event_id&error=File is too large.");
            exit;
        }

        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "pdf" ) {
            header("Location: buy_ticket.php?event_id=$event_id&error=Only JPG, JPEG, PNG & PDF files are allowed.");
            exit;
        }

        if (!move_uploaded_file($_FILES["proof"]["tmp_name"], $target_file)) {
            header("Location: buy_ticket.php?event_id=$event_id&error=Sorry, there was an error uploading your file.");
            exit;
        }
    }

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO tickets (event_id, user_id, status, payment_method, payment_proof_path) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("iisss", $event_id, $user_id, $status, $payment_method, $target_file);

        if ($stmt->execute()) {
            if ($is_free) {
                header("Location: my_tickets.php?success=Free ticket secured successfully.");
            } else {
                header("Location: my_tickets.php?success=Ticket requested successfully. Pending approval.");
            }
        } else {
            header("Location: buy_ticket.php?event_id=$event_id&error=" . urlencode($conn->error));
        }
        $stmt->close();
    } else {
        header("Location: buy_ticket.php?event_id=$event_id&error=" . urlencode("Database prepare error: " . $conn->error));
    }
}
?>
