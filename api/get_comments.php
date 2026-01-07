<?php
include '../includes/db.php';

$candidate_id = $_GET['candidate_id'];
$query = "SELECT * FROM comments WHERE candidate_id = $candidate_id";
$result = $conn->query($query);

$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}

echo json_encode($comments);
?>
