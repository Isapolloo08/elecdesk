<?php
session_start();
include '../includes/db.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

// Check if candidate ID is provided
if (!isset($_GET['id'])) {
    header("Location: /admin/manage_candidates.php");
    exit();
}

$id = $_GET['id'];

// Fetch candidate details
$query = "SELECT * FROM candidates WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$candidate = $result->fetch_assoc();

if (!$candidate) {
    header("Location: /admin/manage_candidates.php");
    exit();
}

// Initialize status message (for SweetAlert)
$statusMessage = '';
$statusType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $grade_level = $_POST['grade_level'];
    $platform = $_POST['platform'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $image_tmp = $_FILES['image']['tmp_name'];
        $image_path = "../assets/" . $image_name;

        if (move_uploaded_file($image_tmp, $image_path)) {
            if (!empty($candidate['image']) && file_exists("../assets/" . $candidate['image'])) {
                unlink("../assets/" . $candidate['image']);
            }
            $update_query = "UPDATE candidates SET name=?, grade_level=?, platform=?, email=?, contact=?, image=? WHERE id=?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssssi", $name, $grade_level, $platform, $email, $contact, $image_name, $id);
        }
    } else {
        $update_query = "UPDATE candidates SET name=?, grade_level=?, platform=?, email=?, contact=? WHERE id=?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssi", $name, $grade_level, $platform, $email, $contact, $id);
    }

    if ($stmt->execute()) {
        $statusMessage = 'Candidate updated successfully!';
        $statusType = 'success';
    } else {
        $statusMessage = 'Failed to update candidate. Please try again.';
        $statusType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Candidate</title>
    <link rel="stylesheet" href="../assets/admin.css">
    <link rel="stylesheet" href="../assets/edit_candidates.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jpeg" href="../logo.jpg">
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <h1 class="logo">Admin Panel</h1>
            <ul class="nav-links">
                <li><a href="./admin.php">Home</a></li>
                <li><a href="/admin/dashboard.php">Dashboard</a></li>
                <li><a href="/admin/manage_candidates.php">Manage Candidates</a></li>
                <li><a href="/admin/account_management.php">Account Management</a></li>
                <li><a href="./logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <h2>Edit Candidate</h2>
        <form method="POST" enctype="multipart/form-data">
            <label>Name:</label>
            <input type="text" name="name" value="<?= htmlspecialchars($candidate['name']) ?>" required>

            <label for="grade_level">Grade Level:</label>
            <select name="grade_level" required>
                <option value="Grade 11" <?= $candidate['grade_level'] === 'Grade 11' ? 'selected' : '' ?>>Grade 11</option>
                <option value="Grade 12" <?= $candidate['grade_level'] === 'Grade 12' ? 'selected' : '' ?>>Grade 12</option>
            </select>

            <label>Platform:</label>
            <textarea name="platform" required><?= htmlspecialchars($candidate['platform']) ?></textarea>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($candidate['email']) ?>" required>

            <label>Contact:</label>
            <input type="text" name="contact" value="<?= htmlspecialchars($candidate['contact']) ?>" required>

            <label>Current Image:</label><br>
            <img src="../assets/<?= htmlspecialchars($candidate['image']) ?>" width="100" height="100" alt="Candidate Photo"><br>

            <label>Upload New Image:</label>
            <input type="file" name="image">

            <button type="submit" class="btn">Update Candidate</button>
        </form>
    </div>

    <!-- SweetAlert Feedback Script -->
    <?php if (!empty($statusMessage)): ?>
    <script>
        Swal.fire({
            title: "<?= $statusType === 'success' ? 'Success!' : 'Error!' ?>",
            text: "<?= $statusMessage ?>",
            icon: "<?= $statusType ?>",
            confirmButtonText: "OK"
        }).then(() => {
            <?php if ($statusType === 'success'): ?>
                window.location.href = '/api/edit_candidate.php';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>
