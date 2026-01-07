<?php 
include '../includes/db.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics</title>
    <link rel="stylesheet" href="../assets/get_analytics.css">
    <link rel="stylesheet" href="../assets/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- ✅ Chart.js -->
    <link rel="icon" type="image/jgp" href="../logo.jpg">
</head>
<body>

   <!-- 🔵 Custom Admin Navigation -->
   <header class="admin-header">
        <div class="admin-nav">
            <h1 class="logo">Admin Panel</h1>
            <ul class="nav-links">
                <li><a href="../admin.php">Home</a></li>
                <li><a href="./dashboard.php">Dashboard</a></li>
                <li><a href="manage_candidates.php">Manage Candidates</a></li>
                <li><a href="account_management.php">Account Management</a></li>
                <li><a href="../pages/logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </header>
    
    <div class="container">
        <h2 class="title">Comment Analytics</h2>

        <!-- 📊 Total Analytics Boxes -->
        <div class="analytics-box-container">
            <div class="analytics-box" id="total-comments-box" onclick="scrollToComments()">
                <h3>Total Comments</h3>
                <p id="total-comments">0</p>
            </div>
            <div class="analytics-box" id="total-candidates-box" onclick="navigateTo('./manage_candidates.php')">
                <h3>Total Candidates</h3>
                <p id="total-candidates">0</p>
            </div>
            <div class="analytics-box" id="total-users-box" onclick="navigateTo('./account_management.php')">
                <h3>Total Users</h3>
                <p id="total-users">0</p>
            </div>
        </div>

        <!-- 🔍 Candidate Filter -->
        <select id="candidate-filter">
            <option value="all">All Candidates</option>
            <?php
                $candidateQuery = "SELECT id, name FROM candidates";
                $candidateResult = $conn->query($candidateQuery);
                while ($candidate = $candidateResult->fetch_assoc()) {
                    echo "<option value='{$candidate['id']}'>{$candidate['name']}</option>";
                }
            ?>
        </select>

        <!-- 📊 Bar Chart -->
        <canvas id="commentChart"></canvas>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Total Comments</th>
                    </tr>
                </thead>
                <tbody id="comment-data">
                    <?php
                    $query = "SELECT candidates.id, candidates.name, COUNT(comments.id) AS comment_count 
                              FROM candidates 
                              LEFT JOIN comments ON candidates.id = comments.candidate_id 
                              GROUP BY candidates.id";
                    $result = $conn->query($query);
                    $chartData = [];
                    
                    while ($row = $result->fetch_assoc()) {
                        $chartData[] = $row;
                        echo "<tr data-candidate='{$row['id']}'>
                                <td>{$row['name']}</td>
                                <td>{$row['comment_count']}</td>
                              </tr>";
                    }
                    ?>

                    <?php
                    // ✅ Get total candidates
                    $candidateCountQuery = "SELECT COUNT(id) AS total_candidates FROM candidates";
                    $candidateCountResult = $conn->query($candidateCountQuery);
                    $totalCandidates = $candidateCountResult->fetch_assoc()['total_candidates'];

                    // ✅ Get total users (students + candidates)
                    $userCountQuery = "SELECT COUNT(id) AS total_users FROM users WHERE role IN ('student', 'candidate')";
                    $userCountResult = $conn->query($userCountQuery);
                    $totalUsers = $userCountResult->fetch_assoc()['total_users'];
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        let chartData = <?php echo json_encode($chartData); ?>;
        let totalComments = chartData.reduce((sum, row) => sum + parseInt(row.comment_count), 0);
        document.getElementById("total-comments").innerText = totalComments;

        // ✅ Display total candidates & users
        document.getElementById("total-candidates").innerText = "<?php echo $totalCandidates; ?>";
        document.getElementById("total-users").innerText = "<?php echo $totalUsers; ?>";

        // 📊 Initialize Chart.js
        let ctx = document.getElementById('commentChart').getContext('2d');
        let commentChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(row => row.name),
                datasets: [{
                    label: 'Total Comments',
                    data: chartData.map(row => row.comment_count),
                    backgroundColor: '#2563eb'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // 🔍 Filter Candidates
        document.getElementById("candidate-filter").addEventListener("change", function () {
            let selected = this.value;
            let filteredData = chartData;

            if (selected !== "all") {
                filteredData = chartData.filter(row => row.id == selected);
            }

            // Update Table
            document.querySelectorAll("#comment-data tr").forEach(row => {
                row.style.display = (selected === "all" || row.getAttribute("data-candidate") === selected) ? "" : "none";
            });

            // Update Chart
            commentChart.data.labels = filteredData.map(row => row.name);
            commentChart.data.datasets[0].data = filteredData.map(row => row.comment_count);
            commentChart.update();
        });

         // 📜 Smooth Scroll to Comments Table
        function scrollToComments() {
            document.getElementById("comment-data").scrollIntoView({ behavior: 'smooth' });
        }

        function navigateTo(page) {
            window.location.href = page;
        }
    </script>

</body>
</html>
