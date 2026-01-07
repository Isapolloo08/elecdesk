<?php
session_start();
include '../includes/db.php';

$notifications = [];

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'candidate') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch candidate ID
$candidate_query = "SELECT id FROM candidates WHERE user_id = ?";
$candidate_stmt = $conn->prepare($candidate_query);
$candidate_stmt->bind_param("i", $user_id);
$candidate_stmt->execute();
$candidate_result = $candidate_stmt->get_result();
$candidate = $candidate_result->fetch_assoc();

if (!$candidate) {
    echo json_encode(['status' => 'error', 'message' => 'Candidate not found']);
    exit();
}

$candidate_id = $candidate['id'];

// ✅ Fetch comments related to the candidate
$comment_query = "SELECT c.id AS comment_id, c.comment, c.created_at, u.username AS commenter
                  FROM comments c
                  JOIN users u ON c.user_id = u.id
                  WHERE c.candidate_id = ?
                  ORDER BY c.created_at DESC";
$comment_stmt = $conn->prepare($comment_query);
$comment_stmt->bind_param("i", $candidate_id);
$comment_stmt->execute();
$comment_result = $comment_stmt->get_result();

while ($comment = $comment_result->fetch_assoc()) {
    $notifications[] = [
        'comment_id' => $comment['comment_id'],
        'message' => "New comment from {$comment['commenter']}: {$comment['comment']}",
        'created_at' => $comment['created_at']
    ];

    // ✅ Fetch replies to the comment, EXCLUDING candidate's own replies
    $reply_query = "SELECT r.id AS reply_id, r.reply, r.created_at, u.username AS replier
                    FROM replies r
                    JOIN users u ON r.user_id = u.id
                    WHERE r.comment_id = ? AND r.user_id != ? -- ✅ Exclude candidate’s own replies
                    ORDER BY r.created_at DESC";
    $reply_stmt = $conn->prepare($reply_query);
    $reply_stmt->bind_param("ii", $comment['comment_id'], $user_id);
    $reply_stmt->execute();
    $reply_result = $reply_stmt->get_result();

    while ($reply = $reply_result->fetch_assoc()) {
        $notifications[] = [
            'comment_id' => $comment['comment_id'],
            'reply_id' => $reply['reply_id'],
            'message' => "New reply from {$reply['replier']}: {$reply['reply']}",
            'created_at' => $reply['created_at']
        ];
    }
}

echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>
