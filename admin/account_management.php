<?php
session_start();
include '../includes/db.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

// Filter logic (default is 'all')
$filterRole = isset($_GET['role']) ? $_GET['role'] : 'all';

if ($filterRole === 'all') {
    $query = "SELECT id, username, role FROM users";
} else {
    $query = "SELECT id, username, role FROM users WHERE role = ?";
}

// Prepare and execute query
$stmt = $conn->prepare($query);

if ($filterRole !== 'all') {
    $stmt->bind_param("s", $filterRole);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Account Management</title>
    <link rel="stylesheet" href="../assets/account_management.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jpg" href="../logo.jpg">
</head>
<body>
    <header class="admin-header">
        <div class="admin-nav">
            <h1 class="logo">Admin Panel</h1>
            <ul class="nav-links">
                <li><a href="../admin.php">Home</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="manage_candidates.php">Manage Candidates</a></li>
                <li><a href="account_management.php">Account Management</a></li>
                <li><a href="../pages/logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </header>

    <div class="container">
        <h2>Account Management</h2>

        <!-- Filter Dropdown -->
        <form method="GET" action="account_management.php" style="margin-bottom: 15px;">
            <label for="role-filter">Filter by Role:</label>
            <select name="role" id="role-filter" onchange="this.form.submit()">
                <option value="all" <?= $filterRole === 'all' ? 'selected' : '' ?>>All</option>
                <option value="candidate" <?= $filterRole === 'candidate' ? 'selected' : '' ?>>Candidates</option>
                <option value="student" <?= $filterRole === 'student' ? 'selected' : '' ?>>Students</option>
            </select>
        </form>

        <table class="styled-table">
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Role</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= $row['username'] ?></td>
                    <td><?= ucfirst($row['role']) ?></td>
                    <td>
                        <a href="../edit_user.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                        <button class="btn-danger delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function () {
                let userId = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This action cannot be undone!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "../delete_user.php?id=" + userId;
                    }
                });
            });
        });
    </script>
</body>
</html>
