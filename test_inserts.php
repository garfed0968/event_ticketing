<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db_connect.php';

echo "Testing save_event.php:\n";
$organizer_id = 1;
$title = "Test Event";
$date = "2026-12-12";
$time = "10:00";
$location = "Test Loc";
$theme = "Music";
$price = 100.00;
$items = "Nothing";

$stmt = $conn->prepare("INSERT INTO events (organizer_id, title, date, time, location, theme, price, items) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    echo "Prepare failed: " . $conn->error . "\n";
} else {
    $stmt->bind_param("isssssds", $organizer_id, $title, $date, $time, $location, $theme, $price, $items);
    if (!$stmt->execute()) {
        echo "Execute failed: " . $stmt->error . "\n";
    } else {
        echo "save_event.php Insert successful! ID: " . $stmt->insert_id . "\n";
    }
}

echo "\nTesting save_event_action.php:\n";
$user_id = 1;
$event_id = 1;

$insert = $conn->prepare("INSERT INTO saved_events (user_id, event_id) VALUES (?, ?)");
if (!$insert) {
    echo "Prepare failed: " . $conn->error . "\n";
} else {
    $insert->bind_param("ii", $user_id, $event_id);
    if (!$insert->execute()) {
        echo "Execute failed: " . $insert->error . "\n";
    } else {
        echo "save_event_action.php Insert successful! ID: " . $insert->insert_id . "\n";
    }
}
?>
