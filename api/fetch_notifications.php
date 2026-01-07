<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch notifications
$stmt = $conn->prepare("
    SELECT n.id, n.user_id, n.message, n.is_read, n.created_at, 
           n.comment_id, n.reply_id, 
           CASE 
               WHEN n.reply_id IS NOT NULL THEN 
                   (SELECT c.candidate_id FROM comments c WHERE c.id = (SELECT r.comment_id FROM replies r WHERE r.id = n.reply_id)) 
               ELSE 
                   (SELECT c.candidate_id FROM comments c WHERE c.id = n.comment_id)
           END AS candidate_id
    FROM notifications n
    LEFT JOIN replies r ON n.reply_id = r.id 
    WHERE n.user_id = ?
    ORDER BY n.created_at DESC
");

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

$stmt->close();
$conn->close();

// ✅ Return notifications
echo json_encode(["status" => "success", "notifications" => $notifications]);
?>
