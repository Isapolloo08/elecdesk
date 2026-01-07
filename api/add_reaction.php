<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "You must be logged in."]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['candidate_id'], $_POST['reaction_type'])) {
    $user_id = $_SESSION['user_id'];
    $candidate_id = intval($_POST['candidate_id']);
    $reaction_type = $_POST['reaction_type'];

    if (!in_array($reaction_type, ['like', 'dislike'])) {
        echo json_encode(["status" => "error", "message" => "Invalid reaction type."]);
        exit();
    }

    // Check if the user already reacted
    $stmt = $conn->prepare("SELECT * FROM reactions WHERE user_id = ? AND candidate_id = ?");
    $stmt->bind_param("ii", $user_id, $candidate_id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        // Update reaction
        $stmt = $conn->prepare("UPDATE reactions SET reaction_type = ? WHERE user_id = ? AND candidate_id = ?");
        $stmt->bind_param("sii", $reaction_type, $user_id, $candidate_id);
    } else {
        // Insert new reaction
        $stmt = $conn->prepare("INSERT INTO reactions (user_id, candidate_id, reaction_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $candidate_id, $reaction_type);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error."]);
    }
    exit();
}

?>
