<?php
session_start();
include '../includes/db.php';

if (isset($_SESSION['user_id'])) {
    // Redirect based on role
    if ($_SESSION['role'] === 'admin') {
        header("Location: /admin.php");
    } elseif ($_SESSION['role'] === 'candidate') {
        header("Location: /candidate_user.php");
    } else {
        header("Location: index.php");
    }
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'admin') {
            header("Location: ../admin.php");
        } elseif ($user['role'] === 'candidate') {
            header("Location: /candidate_user.php");
        } else {
            header("Location: /index.php");
        }
        exit();
    } else {
        $error = "Invalid username or password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login</title>
    <link rel="stylesheet" href="../assets/styles.css">
    <link rel="icon" type="image/jgp" href="../logo.jpg">
</head>
<body>
<div class="page-container">
<div class="logo-container">
        <img src="../logo.jpg" alt="Elecdesk Logo">
    </div>
    </div>
    <div class="form-container">
        <h2>Login</h2>
        <form method="POST">
            <!-- Username Input -->
            <div class="wave-group">
                <input required="" type="text" class="input" name="username">
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

            <!-- Password Input with Toggle -->
            <div class="wave-group password-container">
                <input required="" type="password" class="input" name="password" id="password">
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
                <span class="toggle-password" onclick="togglePassword()">👁</span>
            </div>

            <button type="submit">Login</button>
        </form>

        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
    </div>
    <script>
        function togglePassword() {
            let passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
    </script>
</body>
</html>
