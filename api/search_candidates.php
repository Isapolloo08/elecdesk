<?php
// Include database connection
include '../includes/db.php';

// Get search query
$query = isset($_GET['query']) ? $_GET['query'] : '';

// Prepare SQL with search
$sql = "SELECT * FROM candidates";

if (!empty($query)) {
    $search = "%{$query}%";
    $sql .= " WHERE name LIKE ? OR credentials LIKE ? OR background LIKE ? OR platform LIKE ?";
}

// Prepare and execute statement
$stmt = $conn->prepare($sql);

if (!empty($query)) {
    $stmt->bind_param("ssss", $search, $search, $search, $search);
}

$stmt->execute();
$result = $stmt->get_result();

// Output search results in Bootstrap blue theme format
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo '<div class="col-12 col-md-6 col-lg-4">
            <a href="pages/candidate.php?id='.$row['id'].'" class="candidate-card d-block">
                <div class="candidate-profile">
                    <img src="assets/'.$row['image'].'" alt="'.$row['name'].'" class="profile-img">
                    <span class="candidate-name">'.$row['name'].'</span>
                </div>
                <div class="candidate-info">
                    <p><strong>Credentials:</strong> '.$row['credentials'].'</p>
                    <p><strong>Background:</strong> '.$row['background'].'</p>
                    <p><strong>Platform:</strong> '.$row['platform'].'</p>
                </div>
            </a>
        </div>';
    }
} else {
    echo '<div class="col-12 text-center py-5">
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> No candidates found matching your search.
        </div>
    </div>';
}

$stmt->close();
$conn->close();
?>