<?php
include 'db_connect.php';

$sql = "ALTER TABLE events 
    ADD COLUMN cbe_account VARCHAR(50) NULL,
    ADD COLUMN awash_account VARCHAR(50) NULL,
    ADD COLUMN dashen_account VARCHAR(50) NULL,
    ADD COLUMN abissinia_account VARCHAR(50) NULL,
    ADD COLUMN telebirr_account VARCHAR(50) NULL";

if ($conn->query($sql) === TRUE) {
    echo "Columns added successfully";
} else {
    echo "Error adding columns: " . $conn->error;
}
$conn->close();
?>
