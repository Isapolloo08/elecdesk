<?php
session_start();
include '../includes/db.php'; // Ensure database connection is correct

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "You must be logged in to reply."]);
        exit();
    }

    $user_id = $_SESSION['user_id']; // ID of the user who is replying
    $comment_id = $_POST['comment_id'];
    $reply = trim($_POST['reply']);

    if (empty($reply)) {
        echo json_encode(["status" => "error", "message" => "Reply cannot be empty."]);
        exit();
    }

    // Get the user ID of the original comment owner
    $stmt = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Comment not found."]);
        exit();
    }

    $comment = $result->fetch_assoc();
    $comment_owner_id = $comment['user_id']; // The user who made the original comment

    // Insert the reply into the replies table
    $stmt = $conn->prepare("INSERT INTO replies (comment_id, user_id, reply, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $comment_id, $user_id, $reply);

    if ($stmt->execute()) {
        // Fetch the name of the user who replied
        $stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_result = $stmt->get_result();
        $user_data = $user_result->fetch_assoc();
        $reply_user_name = $user_data['username'];

        // Insert notification for the comment owner
        $notif_message ="<b>\"$reply_user_name\"</b> replied to your comment: <b>\"$reply\"</b>";

$stmt = $conn->prepare("INSERT INTO notifications (user_id, message, is_read, created_at, comment_id) VALUES (?, ?, 0, NOW(), ?)");
$stmt->bind_param("isi", $comment_owner_id, $notif_message, $comment_id); 
$stmt->execute();


        echo json_encode(["status" => "success", "message" => "Reply posted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to post reply."]);
    }
}
?>
