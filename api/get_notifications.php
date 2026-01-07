<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login first']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get notifications for the user
$query = "SELECT n.*, 
            c.candidate_id, c.comment,
            r.reply,
            u.username as source_username
          FROM notifications n
          LEFT JOIN comments c ON n.comment_id = c.id
          LEFT JOIN replies r ON n.reply_id = r.id
          LEFT JOIN users u ON COALESCE(c.user_id, r.user_id) = u.id
          WHERE n.user_id = ?
          ORDER BY n.created_at DESC
          LIMIT 15";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    // Format the time to be more readable
    $created = new DateTime($row['created_at']);
    $now = new DateTime();
    $interval = $created->diff($now);
    
    if ($interval->d > 0) {
        $time = $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
    } elseif ($interval->h > 0) {
        $time = $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
    } elseif ($interval->i > 0) {
        $time = $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
    } else {
        $time = 'just now';
    }
    
    $row['time_ago'] = $time;
    $notifications[] = $row;
}

// Get unread notification count
$countQuery = "SELECT COUNT(*) as unread FROM notifications WHERE user_id = ? AND is_read = 0";
$stmt = $conn->prepare($countQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$countResult = $stmt->get_result();
$count = $countResult->fetch_assoc();

echo json_encode([
    'status' => 'success', 
    'data' => $notifications,
    'unread_count' => $count['unread']
]);

$stmt->close();
$conn->close();
?>