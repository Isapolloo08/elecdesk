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

// Get data from POST
$user_id = $_SESSION['user_id'];
$comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;
$reply = isset($_POST['reply']) ? trim($_POST['reply']) : '';

// Validate data
if (empty($reply)) {
    echo json_encode(['status' => 'error', 'message' => 'Reply cannot be empty']);
    exit();
}

if ($comment_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid comment ID']);
    exit();
}

// Check if comment exists
$checkQuery = "SELECT * FROM comments WHERE id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $comment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Comment not found']);
    exit();
}

$commentData = $result->fetch_assoc();

// Insert reply into database
$query = "INSERT INTO replies (comment_id, user_id, reply, created_at) VALUES (?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iis", $comment_id, $user_id, $reply);

if ($stmt->execute()) {
    $reply_id = $stmt->insert_id;
    
    // Get the reply with user info for response
    $replyQuery = "SELECT replies.*, users.username 
                   FROM replies 
                   JOIN users ON replies.user_id = users.id 
                   WHERE replies.id = ?";
    $stmt = $conn->prepare($replyQuery);
    $stmt->bind_param("i", $reply_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $replyData = $result->fetch_assoc();
    
    // Create notification for the comment author
    $comment_user_id = $commentData['user_id'];
    
    // Only create notification if the replier is not the comment author
    if ($user_id != $comment_user_id) {
        $notifQuery = "INSERT INTO notifications (user_id, message, is_read, created_at, comment_id, reply_id) 
                      VALUES (?, 'New reply to your comment', 0, NOW(), ?, ?)";
        $stmt = $conn->prepare($notifQuery);
        $stmt->bind_param("iii", $comment_user_id, $comment_id, $reply_id);
        $stmt->execute();
    }
    
    echo json_encode(['status' => 'success', 'data' => $replyData]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add reply: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>