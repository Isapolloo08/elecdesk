<?php
session_start();
include './includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Step 1: Delete replies related to comments of this candidate
    $query = "DELETE replies FROM replies 
              INNER JOIN comments ON replies.comment_id = comments.id 
              WHERE comments.candidate_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Step 2: Delete comments related to this candidate
    $query = "DELETE FROM comments WHERE candidate_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Step 3: Finally, delete the candidate
    $query = "DELETE FROM candidates WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

// Redirect back to manage candidates page
header("Location: admin/manage_candidates.php");
exit();
?>
