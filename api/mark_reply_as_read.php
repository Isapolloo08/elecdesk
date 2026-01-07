<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

// Check if request is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
    exit();
}

$user_id = $_SESSION['user_id'];
$notification_id = isset($_POST['notification_id']) ? intval($_POST['notification_id']) : 0;

// If notification_id is 0, mark all as read
if ($notification_id === 0) {
    $query = "UPDATE notifications SET is_read = 1 WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
} else {
    // Mark specific notification as read
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $notification_id, $user_id);
}

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to mark notifications as read: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>