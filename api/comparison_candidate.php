<?php
include '../includes/db.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get candidate IDs from URL
$candidateIds = [];
if (isset($_GET['candidates'])) {
    $candidateIds = explode(',', $_GET['candidates']);
}

// Fetch candidate data
$candidates = [];
if (!empty($candidateIds)) {
    $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
    
    $stmt = $conn->prepare("
        SELECT c.*, 
               COUNT(DISTINCT cm.id) AS comment_count,
               SUM(CASE WHEN r.reaction_type = 'like' THEN 1 WHEN r.reaction_type = 'dislike' THEN -1 ELSE 0 END) AS reaction_score
        FROM candidates c
        LEFT JOIN comments cm ON c.id = cm.candidate_id
        LEFT JOIN reactions r ON c.id = r.candidate_id
        WHERE c.id IN ($placeholders)
        GROUP BY c.id
    ");
    
    // Bind parameters
    $types = str_repeat('i', count($candidateIds));
    $stmt->bind_param($types, ...$candidateIds);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Head content from your existing template -->
</head>
<body>
    <!-- Navigation Bar from your existing template -->
    
    <!-- Comparison Content -->
    <!-- Add the comparison table HTML here and populate it with PHP -->
    
    <!-- Footer from your existing template -->
</body>
</html>