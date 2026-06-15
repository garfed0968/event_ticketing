<?php
include 'db_connect.php';

$query = "ALTER TABLE tickets ADD COLUMN refund_proof_path VARCHAR(255) NULL AFTER payment_proof_path";

if ($conn->query($query) === TRUE) {
    echo "Column added successfully";
} else {
    echo "Error adding column: " . $conn->error;
}
$conn->close();
?>
