<?php
include 'includes/session.php';
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php");
    exit();
}

include 'includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <link rel="stylesheet" href="assets/admin.css"> <!-- Link to CSS file -->
    <link rel="icon" type="image/jgp" href="../logo.jpg">
</head>
<body>
    <!-- 🔵 Custom Admin Navigation -->
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

    
    
    <div class="admin-container">
        <h2>Admin Panel</h2>

        <div class="card">
            <h3>Comment Moderation</h3>
            <table>
                <tr>
                    <th>Comment</th>
                    <th>Action</th>
                </tr>
                <?php
                $query = "SELECT * FROM comments";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['comment']}</td>
                            <td>
                                <form method='POST' action='/api/delete_comment.php'>
                                    <input type='hidden' name='comment_id' value='{$row['id']}'>
                                    <button type='submit' class='btn-delete'>Delete</button>
                                </form>
                            </td>
                          </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>
</html>
