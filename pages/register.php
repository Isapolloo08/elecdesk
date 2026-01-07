<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $gmail = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (empty($username) || empty($gmail) || empty($password) || empty($confirm_password)) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'All fields are required!'];
        header("Location: register.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Passwords do not match!'];
        header("Location: register.php");
        exit();
    }

    $check_query = "SELECT * FROM users WHERE username = ? OR gmail = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("ss", $username, $gmail);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['username'] === $username) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Username already exists!'];
        } elseif ($row['gmail'] === $gmail) {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Gmail already exists!'];
        }
        header("Location: register.php");
        exit();
    }

 // ✅ Handle Profile Picture Upload
 if (!empty($_FILES['profile_picture']['name'])) {
    $targetDir = "../assets/";
    $fileName = basename($_FILES["profile_picture"]["name"]);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // ✅ Role-based allowed file types
    $allowedTypes = ($role === 'student') ? ["jpg", "png"] : ["jpg", "jpeg", "png", "gif"];
    $maxFileSize = 3 * 1024 * 1024; // 2MB

    if (in_array($fileType, $allowedTypes) && $_FILES["profile_picture"]["size"] <= $maxFileSize) {
        $newFileName = uniqid("IMG_", true) . "." . $fileType; // ✅ Unique filename
        $targetFilePath = $targetDir . $newFileName;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFilePath)) {
            $image = $newFileName; // ✅ Save new filename to database
        } else {
            $_SESSION['message'] = ['type' => 'error', 'text' => 'Failed to upload image.'];
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Invalid image format or size too large.'];
        header("Location: register.php");
        exit();
    }
}

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, gmail, password, image, role) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssss", $username, $gmail, $hashed_password, $image, $role);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Registration Successful!'];
        header("Location: register.php");
        exit();
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Something went wrong. Please try again.'];
        header("Location: register.php");
        exit();
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jgp" href="../logo.jpg">
</head>
<body>
<div class="form-container">
        <h2>Register</h2>
<form method="POST" enctype="multipart/form-data">
            <!-- Username -->
            <div class="form-grid">
            <div class="wave-group">
                <input required type="text" class="input" name="username">
                <span class="bar"></span>
                <label class="label">
                    <span class="label-char" style="--index: 0">U</span>
                    <span class="label-char" style="--index: 1">s</span>
                    <span class="label-char" style="--index: 2">e</span>
                    <span class="label-char" style="--index: 3">r</span>
                    <span class="label-char" style="--index: 4">n</span>
                    <span class="label-char" style="--index: 5">a</span>
                    <span class="label-char" style="--index: 6">m</span>
                    <span class="label-char" style="--index: 7">e</span>
                </label>
            </div>

            <!-- Email -->
            <div class="wave-group">
                <input required type="email" class="input" name="email">
                <span class="bar"></span>
                <label class="label">
                    <span class="label-char" style="--index: 0">G</span>
                    <span class="label-char" style="--index: 1">m</span>
                    <span class="label-char" style="--index: 2">a</span>
                    <span class="label-char" style="--index: 3">i</span>
                    <span class="label-char" style="--index: 4">l</span>
                </label>
            </div>

               <!-- Email -->
            <div class="profile-upload">
                    <label for="profile_picture">Upload Picture</label>
                    <input type="file" name="profile_picture" accept="image/png, image/jpeg, image/jpg" >
                </div>
            <!-- Password -->
            <div class="wave-group password-container">
                <input required type="password" class="input" name="password" id="password">
                <span class="bar"></span>
                <label class="label">
                    <span class="label-char" style="--index: 0">P</span>
                    <span class="label-char" style="--index: 1">a</span>
                    <span class="label-char" style="--index: 2">s</span>
                    <span class="label-char" style="--index: 3">s</span>
                    <span class="label-char" style="--index: 4">w</span>
                    <span class="label-char" style="--index: 5">o</span>
                    <span class="label-char" style="--index: 6">r</span>
                    <span class="label-char" style="--index: 7">d</span>
                </label>
                <span class="toggle-password" onclick="togglePassword('password')">👁</span>
            </div>

            <!-- Confirm Password -->
            <div class="wave-group password-container">
                <input required type="password" class="input" name="confirm_password" id="confirm_password">
                <span class="bar"></span>
                <label class="label">
                    <span class="label-char" style="--index: 0">C</span>
                    <span class="label-char" style="--index: 1">o</span>
                    <span class="label-char" style="--index: 2">n</span>
                    <span class="label-char" style="--index: 3">f</span>
                    <span class="label-char" style="--index: 4">i</span>
                    <span class="label-char" style="--index: 5">r</span>
                    <span class="label-char" style="--index: 6">m</span>
                </label>
                <span class="toggle-password" onclick="togglePassword('confirm_password')">👁</span>
            </div>

            <!-- Role Selection -->
            <div class="wave-group">
                <select name="role" class="input" required>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                </select>
                <span class="bar"></span>
                <label class="label">
                    <span class="label-char" style="--index: 0">R</span>
                    <span class="label-char" style="--index: 1">o</span>
                    <span class="label-char" style="--index: 2">l</span>
                    <span class="label-char" style="--index: 3">e</span>
                </label>
            </div>
</div>
            <button type="submit">Register</button>
        </form>

        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
    <script>
        function togglePassword(id) {
            let passwordInput = document.getElementById(id);
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
        }

        // This runs AFTER the page loads
window.onload = function() {
    <?php
    if (isset($_SESSION['message'])) {
        $icon = $_SESSION['message']['type'];
        $text = $_SESSION['message']['text'];

        echo "showSweetAlert('{$icon}', '{$text}');";

        // Clear session message after showing
        unset($_SESSION['message']);
    }
    ?>

    // Function to show SweetAlert (centralized here for cleaner code)
    function showSweetAlert(icon, text) {
        Swal.fire({
            icon: icon,
            title: icon === 'success' ? 'Success' : 'Error',
            text: text,
            confirmButtonColor: icon === 'success' ? '#3085d6' : '#d33'
        });
    }
}
    </script>
</body>
</html>
