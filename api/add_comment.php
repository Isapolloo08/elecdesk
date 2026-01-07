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
$candidate_id = isset($_POST['candidate_id']) ? intval($_POST['candidate_id']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
$parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;

// Validate data
if (empty($comment)) {
    echo json_encode(['status' => 'error', 'message' => 'Comment cannot be empty']);
    exit();
}

if ($candidate_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid candidate ID']);
    exit();
}

// Insert comment into database
$query = "INSERT INTO comments (candidate_id, user_id, comment, parent_id, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("iisi", $candidate_id, $user_id, $comment, $parent_id);

if ($stmt->execute()) {
    $comment_id = $stmt->insert_id;
    
    // Get the comment with user info for response
    $commentQuery = "SELECT comments.*, users.username 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE comments.id = ?";
    $stmt = $conn->prepare($commentQuery);
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $commentData = $result->fetch_assoc();
    
    // Create notification for candidate if this is a comment on their profile
    // First get the candidate's user_id
    $candidateQuery = "SELECT user_id FROM candidates WHERE id = ?";
    $stmt = $conn->prepare($candidateQuery);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $candidateResult = $stmt->get_result();
    
    if ($candidateResult->num_rows > 0) {
        $candidateData = $candidateResult->fetch_assoc();
        $candidate_user_id = $candidateData['user_id'];
        
        // Only create notification if the commenter is not the candidate
        if ($user_id != $candidate_user_id) {
            $notifQuery = "INSERT INTO notifications (user_id, message, is_read, created_at, comment_id) 
                          VALUES (?, 'New comment on your profile', 0, NOW(), ?)";
            $stmt = $conn->prepare($notifQuery);
            $stmt->bind_param("ii", $candidate_user_id, $comment_id);
            $stmt->execute();
        }
    }
    
    echo json_encode(['status' => 'success', 'data' => $commentData]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to add comment: ' . $conn->error]);
}

$stmt->close();
$conn->close();
?>