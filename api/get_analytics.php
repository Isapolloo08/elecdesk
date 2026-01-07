<?php
include '../includes/db.php';

$query = "SELECT candidates.name, COUNT(comments.id) AS comment_count 
          FROM candidates 
          LEFT JOIN comments ON candidates.id = comments.candidate_id 
          GROUP BY candidates.id";
$result = $conn->query($query);

$analytics = [];
while ($row = $result->fetch_assoc()) {
    $analytics[] = $row;
}

echo json_encode($analytics);
?>
