<?php
session_start();
include './includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT id, username, gmail, role, created_at, image FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $username = $_POST['username'];
    $gmail = $_POST['gmail'];
    $role = $_POST['role'];
    $image = $user['image']; // Keep the old image by default

    // ✅ Handle Profile Picture Upload
    if (!empty($_FILES['profile_picture']['name'])) {
        $targetDir = "../assets/";
        $fileName = basename($_FILES["profile_picture"]["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedTypes = ["jpg", "jpeg", "png"];

        if (in_array($fileType, $allowedTypes) && $_FILES["profile_picture"]["size"] <= 2 * 1024 * 1024) {
            $newFileName = uniqid("IMG_", true) . "." . $fileType;
            $targetFilePath = $targetDir . $newFileName;

            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
                $image = $newFileName; // Save new filename in DB
            }
        }
    }

    // ✅ Update User Data in Database
    $query = "UPDATE users SET username = ?, gmail = ?, role = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssi", $username, $gmail, $role, $image, $id);
    $stmt->execute();

    echo json_encode(["status" => "success", "message" => "User updated successfully!"]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- ✅ Include SweetAlert -->
    <link rel="icon" type="image/jgp" href="../logo.jpg">
</head>
<body>

    <!-- 🔵 Admin Header -->
    <header class="admin-header">
        <div class="admin-nav">
            <h1 class="logo">Admin Panel</h1>
            <ul class="nav-links">
                <li><a href="admin.php">Home</a></li>
                <li><a href="./admin/dashboard.php">Dashboard</a></li>
                <li><a href="admin/manage_candidates.php">Manage Candidates</a></li>
                <li><a href="./admin/account_management.php">Account Management</a></li>
                <li><a href="pages/logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <h2>Edit User Profile</h2>
        <form id="editUserForm" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= $id ?>">

            <!-- Profile Picture Upload & Preview -->
            <div class="profile-section">
                <label>Profile Picture:</label><br>
                <img id="profile-preview" src="../assets/<?= htmlspecialchars($user['image'] ?? 'default.jpg') ?>" alt="Profile Image" class="profile-img">
                <input type="file" name="profile_picture" accept="image/png, image/jpeg, image/jpg" onchange="previewImage(event)">
            </div>

            <!-- Username -->
            <label>Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>

            <!-- Gmail -->
            <label>Gmail:</label>
            <input type="email" name="gmail" value="<?= htmlspecialchars($user['gmail']) ?>" required>

            <!-- Created At (Disabled) -->
            <label>Created At:</label>
            <input type="text" value="<?= htmlspecialchars($user['created_at']) ?>" disabled>

            <!-- Role Selection -->
            <label>Role:</label>
            <select name="role">
                <option value="candidate" <?= $user['role'] === 'candidate' ? 'selected' : '' ?>>Candidate</option>
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
            </select>

            <button type="submit" class="btn">Update</button>
        </form>
    </div>

    <script>
        // ✅ Image Preview
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById("profile-preview").src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }

        // ✅ Handle Form Submission
        document.getElementById("editUserForm").addEventListener("submit", function (e) {
            e.preventDefault(); 

            let formData = new FormData(this);
            let username = formData.get("username");
            let role = formData.get("role");

            Swal.fire({
                title: "Confirm Update",
                text: `Are you sure you want to update ${username}'s profile?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch("edit_user.php", {
                        method: "POST",
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Updated!",
                                text: data.message,
                                confirmButtonColor: "#3085d6"
                            }).then(() => {
                                window.location.href = "account_management.php"; 
                            });
                        } else {
                            Swal.fire({ icon: "error", title: "Error!", text: "Update failed!", confirmButtonColor: "#d33" });
                        }
                    })
                    .catch(error => {
                        Swal.fire({ icon: "error", title: "Error!", text: "Something went wrong!", confirmButtonColor: "#d33" });
                    });
                }
            });
        });
    </script>

</body>
</html>
