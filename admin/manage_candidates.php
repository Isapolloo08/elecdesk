<?php
session_start();
include '../includes/db.php';

// Redirect if not an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: /index.php");
    exit();
}

// Fetch all candidates
$query = "SELECT * FROM candidates";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Candidates</title>
    <link rel="icon" type="image/jgp" href="../logo.jpg">
    <link rel="stylesheet" href="../assets/manage_candidates.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- ✅ SweetAlert -->
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
        <h2>Manage Candidates</h2>
        <input type="text" id="search" placeholder="Search candidates..." onkeyup="filterCandidates()">
        <a href="../api/add_candidate.php" class="btn">+ Add Candidate</a>

        <table class="styled-table" id="candidatesTable">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Grade Level</th>
                <th>Platform</th>
                <th>Photo</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td>
                        <a href="#" class="view-details" 
                            data-name="<?= $row['name'] ?>" 
                            data-grade="<?= $row['grade_level'] ?>"
                            data-platform="<?= htmlspecialchars($row['platform']) ?>"
                            data-image="../assets/<?= $row['image'] ?>"
                            data-email="<?= $row['email'] ?>"
                            data-contact="<?= $row['contact'] ?>">
                            <?= $row['name'] ?>
                        </a>
                    </td>
                    <td><?= $row['grade_level'] ?></td>
                    <td><?= substr($row['platform'], 0, 50) ?>...</td>
                    <td>
                        <img src="../assets/<?= $row['image'] ?>" 
                             onerror="this.onerror=null; this.src='../assets/default.jpg';" 
                             width="50" height="50" alt="Candidate Photo">
                    </td>
                    <td>
                        <a href="/api/edit_candidate.php?id=<?= $row['id'] ?>" class="btn">Edit</a>
                        <button class="btn-danger delete-btn" data-id="<?= $row['id'] ?>">Delete</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>

    <script>
        // 🔍 Candidate Search Function
        function filterCandidates() {
            let input = document.getElementById("search").value.toLowerCase();
            let table = document.getElementById("candidatesTable");
            let rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                let nameCell = rows[i].getElementsByTagName("td")[1];
                if (nameCell) {
                    let nameText = nameCell.textContent.toLowerCase();
                    rows[i].style.display = nameText.includes(input) ? "" : "none";
                }
            }
        }

        // 🗑 Delete Confirmation
        document.querySelectorAll(".delete-btn").forEach(button => {
            button.addEventListener("click", function () {
                let candidateId = this.getAttribute("data-id");

                Swal.fire({
                    title: "Are you sure?",
                    text: "This will permanently delete the candidate!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = "../delete_candidates.php?id=" + candidateId;
                    }
                });
            });
        });

        // 📜 Candidate Details Popup
        document.querySelectorAll(".view-details").forEach(link => {
            link.addEventListener("click", function (event) {
                event.preventDefault();

                let name = this.getAttribute("data-name");
                let grade = this.getAttribute("data-grade");
                let platform = this.getAttribute("data-platform");
                let image = this.getAttribute("data-image");
                let email = this.getAttribute("data-email");
                let contact = this.getAttribute("data-contact");

                Swal.fire({
                    title: name,
                    html: `
                        <img src="${image}" onerror="this.src='../assets/default.png';" width="100%" height="250px">
                        <p><strong>Grade Level:</strong> ${grade}</p>
                        <p><strong>Platform:</strong> ${platform}</p>
                        <p><strong>Email:</strong> ${email}</p>
                        <p><strong>Contact:</strong> ${contact}</p>
                    `,
                    confirmButtonText: "Close"
                });
            });
        });
    </script>
</body>
</html>
