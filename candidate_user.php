<?php
session_start();
include 'includes/db.php';

$candidate = null;

// Check if user is logged in and is a candidate
if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'candidate') {
    $user_id = $_SESSION['user_id'];

    // Fetch candidate details
    $query = "SELECT * FROM candidates WHERE user_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Information System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jpg" href="../logo.jpg">
    <script src="./assets/comments_and_notifications.js"></script>
    <style>
        :root {
            --primary-color: #1a73e8;
            --primary-light: #4285f4;
            --primary-dark: #0d47a1;
            --accent-color: #4dabf7;
            --light-color: #f8f9fa;
            --text-color: #212529;
            --white: #fff;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f7fa;
            color: var(--text-color);
        }
        
         /* Navbar Styling */
         .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 0.7rem 1rem;
        }
        
        .navbar-brand img {
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }
        
        .navbar-brand img:hover {
            transform: scale(1.1);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            padding: 0.5rem 1rem !important;
            border-radius: 20px;
            transition: all 0.3s;
            margin: 0 0.2rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateY(-2px);
        }
        
        .logout-btn {
            background-color: white;
            color: var(--primary-blue) !important;
            border-radius: 20px;
            padding: 0.5rem 1.2rem !important;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            background-color: #f8f9fa;
        }
        
        /* Notification Badge */
        .notification-badge {
            position: relative;
            padding-right: 2rem !important;
        }
        
        #notif-count {
            position: absolute;
            top: -5px;
            right: 5px;
            background-color: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 0.15rem 0.4rem;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .dropdown-menu {
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            border: none;
            width: 320px;
            padding: 0.5rem;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .dropdown-header {
            color: var(--primary-blue);
            font-weight: 600;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e9ecef;
        }
        
        .notification-item {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            transition: all 0.2s;
        }
        
        .notification-item:hover {
            background-color: var(--light-blue);
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #6c757d;
        }
        
        .delete-notif-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin-top: 10px;
            width: 100%;
            transition: all 0.3s;
        }
        
        .delete-notif-btn:hover {
            background-color: #c82333;
        }
        
        /* Main content */
        .main-container {
            padding: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        
        /* Profile card */
        .profile-container {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            transition: transform 0.3s;
        }
        
        .profile-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .profile-container h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }
        
        .profile-card {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        .profile-card img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--primary-light);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .profile-info {
            width: 100%;
        }
        
        .profile-info p {
            margin-bottom: 10px;
            padding: 10px;
            border-radius: 8px;
            background-color: rgba(26, 115, 232, 0.05);
        }
        
        .profile-info strong {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        /* Candidates grid */
        .container h2 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
            text-align: center;
        }
        
        .candidates-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .candidate-card {
            height: 350px;
            perspective: 1000px;
            text-decoration: none;
            color: var(--text-color);
        }
        
        .card-inner {
            position: relative;
            width: 100%;
            height: 100%;
            text-align: center;
            transition: transform 0.6s;
            transform-style: preserve-3d;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
        }
        
        .candidate-card:hover .card-inner {
            transform: rotateY(180deg);
        }
        
        .card-front, .card-back {
            position: absolute;
            width: 100%;
            height: 100%;
            -webkit-backface-visibility: hidden;
            backface-visibility: hidden;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .card-front {
            background-color: var(--white);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .card-front img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid var(--primary-light);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        
        .card-front h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-top: 15px;
        }
        
        .card-back {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: var(--white);
            transform: rotateY(180deg);
            padding: 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .card-back h3 {
            font-weight: 600;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .card-back p {
            margin-bottom: 10px;
            text-align: left;
        }
        
        .card-back strong {
            font-weight: 600;
            color: #b8daff;
        }
        
        /* Comments section */
        .comments-section {
            background-color: var(--white);
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            padding: 2rem;
        }
        
        .comments-section h3 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-light);
            padding-bottom: 10px;
        }
        
        .comment {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
        }
        
        .comment:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .comment strong, .reply strong {
            color: var(--primary-color);
        }
        
        .comment small, .reply small {
            display: block;
            color: #6c757d;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        
        .reply {
            background-color: rgba(26, 115, 232, 0.05);
            border-left: 3px solid var(--primary-light);
            border-radius: 0 10px 10px 0;
            padding: 10px 15px;
            margin: 10px 0 10px 20px;
        }
        
        .reply-form {
            margin: 10px 0 10px 20px;
        }
        
        .reply-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            resize: none;
            margin-bottom: 10px;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }
        
        .reply-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.25);
        }
        
        .btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 50px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .btn:disabled {
            background-color: #6c757d;
            cursor: not-allowed;
        }
        
        /* Animation for highlight */
        .highlight {
            animation: highlight 3s;
        }
        
        @keyframes highlight {
            0% { background-color: rgba(26, 115, 232, 0.3); }
            100% { background-color: inherit; }
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .main-container {
                grid-template-columns: 1fr;
            }
            
            .profile-card {
                flex-direction: column;
            }
        }
        
        @media (max-width: 768px) {
            .navbar {
                padding: 0.5rem;
            }
            
            .nav-link {
                padding: 0.3rem 0.7rem;
                font-size: 0.9rem;
            }
            
            .candidates-list {
                grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            }
            
            .main-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : 'index.php'; ?>">
                <img src="logo.jpg" alt="Elecdesk Logo" class="me-2">
                <span class="fw-bold">ElecDesk</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <!-- Home Link -->
                    <li class="nav-item">
                        <a class="nav-link active" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : 'mainhomepage.php'; ?>">
                            <i class="fas fa-home"></i> Home
                        </a>
                    </li>
                    
                    <!-- Candidates Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-users"></i> Candidates
                        </a>
                    </li>
                    
                    <!-- Profile Link -->
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user-circle"></i> My Profile
                        </a>
                    </li>
                    
                    <!-- Admin-only links -->
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/dashboard.php">
                            <i class="fas fa-chart-bar"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/account_management.php">
                            <i class="fas fa-user-cog"></i> Accounts
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Notification Dropdown -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <div class="nav-item dropdown me-3">
                    <a class="nav-link dropdown-toggle notification-badge" href="#" id="notif-bell" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span id="notif-count" class="d-none">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end" id="notif-dropdown">
                        <h6 class="dropdown-header">Notifications</h6>
                        <div id="notif-list" class="p-2">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mb-0 mt-2">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <button id="delete-all-notif" class="btn btn-sm btn-light w-100">
                            <i class="fas fa-trash-alt me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                
                <!-- Logout Button -->
                <a href="pages/logout.php" class="nav-link logout-btn">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
                <?php else: ?>
                <a href="pages/login.php" class="nav-link logout-btn">
                    <i class="fas fa-sign-in-alt me-1"></i> Login
                </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>  

    <div class="main-container">
        <!-- Left: Candidate Profile or Candidate List -->
        <?php if ($candidate): ?>
            <div class="profile-container">
                <h2><i class="fas fa-user-circle me-2"></i>Welcome, <?= htmlspecialchars($candidate['name']) ?>!</h2>
                <div class="profile-card">
                    <img src="assets/<?= htmlspecialchars($candidate['image']) ?>" alt="Profile Picture" class="profile-image">
                    <div class="profile-info mt-3">
                        <p><strong><i class="fas fa-history me-2"></i>Background:</strong> <?= htmlspecialchars($candidate['background']) ?></p>
                        <p><strong><i class="fas fa-award me-2"></i>Credentials:</strong> <?= htmlspecialchars($candidate['credentials']) ?></p>
                        <p><strong><i class="fas fa-bullhorn me-2"></i>Platform:</strong> <?= htmlspecialchars($candidate['platform']) ?></p>
                        <p><strong><i class="fas fa-graduation-cap me-2"></i>Grade Level:</strong> <?= htmlspecialchars($candidate['grade_level']) ?></p>
                        <p><strong><i class="fas fa-book me-2"></i>Strand:</strong> <?= htmlspecialchars($candidate['strand']) ?></p>
                        <p><strong><i class="fas fa-school me-2"></i>School:</strong> <?= htmlspecialchars($candidate['school_name']) ?></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Default Homepage Content - Candidate List -->
            <div class="container">
                <h2><i class="fas fa-users me-2"></i>Candidate List</h2>
                <div class="candidates-list">
                    <?php
                    $query = "SELECT * FROM candidates";
                    $result = $conn->query($query);
                    while ($row = $result->fetch_assoc()) {
                        echo "<a href='pages/candidate.php?id={$row['id']}' class='candidate-card'>
                                <div class='card-inner'>
                                    <!-- Front Side -->
                                    <div class='card-front'>
                                        <img src='assets/{$row['image']}' alt='{$row['name']}'>
                                        <h3>{$row['name']}</h3>
                                        <span class='badge bg-primary mt-2'>{$row['strand']}</span>
                                    </div>
                                    <!-- Back Side -->
                                    <div class='card-back'>
                                        <h3>{$row['name']}</h3>
                                        <p><strong><i class='fas fa-award me-1'></i> Credentials:</strong> {$row['credentials']}</p>
                                        <p><strong><i class='fas fa-history me-1'></i> Background:</strong> {$row['background']}</p>
                                        <p><strong><i class='fas fa-bullhorn me-1'></i> Platform:</strong> {$row['platform']}</p>
                                    </div>
                                </div>
                              </a>";
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Right: Comment Section -->
        <div class="comments-section">
            <h3><i class="fas fa-comments me-2"></i>Comments</h3>
            <?php
            if ($candidate) {
                $comment_query = "SELECT c.id, c.comment, c.created_at, u.username AS commenter
                                  FROM comments c
                                  JOIN users u ON c.user_id = u.id
                                  WHERE c.candidate_id = ?
                                  ORDER BY c.created_at DESC";

                $stmt = $conn->prepare($comment_query);
                $stmt->bind_param("i", $candidate['id']);
                $stmt->execute();
                $comments_result = $stmt->get_result();

                if ($comments_result->num_rows > 0) {
                    while ($comment = $comments_result->fetch_assoc()):
                    ?>
                        <div class="comment" id="comment-<?= $comment['id'] ?>">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <p class="mb-0"><strong><i class="fas fa-user me-1"></i><?= htmlspecialchars($comment['commenter']) ?>:</strong> <?= htmlspecialchars($comment['comment']) ?></p>
                            </div>
                            <small><i class="far fa-clock me-1"></i><?= $comment['created_at'] ?></small>

                            <?php
                            $reply_query = "SELECT r.reply, r.created_at, u.username AS replier, r.id AS reply_id
                                            FROM replies r
                                            JOIN users u ON r.user_id = u.id
                                            WHERE r.comment_id = ?
                                            ORDER BY r.created_at DESC";

                            $reply_stmt = $conn->prepare($reply_query);
                            $reply_stmt->bind_param("i", $comment['id']);
                            $reply_stmt->execute();
                            $replies_result = $reply_stmt->get_result();

                            while ($reply = $replies_result->fetch_assoc()):
                            ?>
                                <div class="reply" id="reply-<?= $reply['reply_id'] ?>">
                                    <p class="mb-0"><strong><i class="fas fa-reply me-1"></i><?= htmlspecialchars($reply['replier']) ?>:</strong> <?= htmlspecialchars($reply['reply']) ?></p>
                                    <small><i class="far fa-clock me-1"></i><?= $reply['created_at'] ?></small>
                                </div>
                            <?php endwhile; ?>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <form class="reply-form" data-comment-id="<?= $comment['id'] ?>">
                                    <div class="input-group mt-2">
                                        <textarea name="reply" class="form-control reply-input" placeholder="Write a reply..." required></textarea>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-paper-plane me-1"></i> Reply
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile;
                } else {
                    echo '<div class="alert alert-info" role="alert">
                            <i class="fas fa-info-circle me-2"></i>No comments yet. Be the first to comment!
                          </div>';
                }
            } else {
                echo '<div class="alert alert-primary" role="alert">
                        <i class="fas fa-info-circle me-2"></i>Select a candidate to view comments.
                      </div>';
            }
            ?>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        // Reply Form Submission
        document.querySelectorAll(".reply-form").forEach(form => {
            form.addEventListener("submit", function (e) {
                e.preventDefault();

                let replyText = this.querySelector(".reply-input").value.trim();
                let commentId = this.getAttribute("data-comment-id");

                if (replyText === "") {
                    Swal.fire({
                        icon: "warning",
                        title: "⚠️ Warning!",
                        text: "Reply cannot be empty.",
                        confirmButtonColor: "#1a73e8",
                    });
                    return;
                }

                let submitButton = this.querySelector("button[type='submit']");
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Replying...';

                fetch("api/post_reply.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `comment_id=${commentId}&reply=${encodeURIComponent(replyText)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "✅ Success!",
                            text: "Reply added successfully!",
                            confirmButtonColor: "#1a73e8",
                        }).then(() => {
                            localStorage.setItem("scrollToComment", `comment-${commentId}`);
                            location.reload();
                        });
                    } else {
                            Swal.fire({
                            icon: "error",
                            title: "❌ Error!",
                            text: data.message || "Failed to add reply. Please try again.",
                            confirmButtonColor: "#1a73e8",
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        icon: "error",
                        title: "❌ Error!",
                        text: "An unexpected error occurred. Please try again.",
                        confirmButtonColor: "#1a73e8",
                    });
                })
                .finally(() => {
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Reply';
                });
            });
        });

        // Scroll to the comment after page reload
        const scrollToComment = localStorage.getItem("scrollToComment");
        if (scrollToComment) {
            const commentElement = document.getElementById(scrollToComment);
            if (commentElement) {
                commentElement.scrollIntoView({ behavior: "smooth", block: "center" });
                commentElement.classList.add("highlight");
                setTimeout(() => commentElement.classList.remove("highlight"), 3000);
            }
            localStorage.removeItem("scrollToComment");
        }

        // Notification Dropdown Toggle
        const notifBell = document.getElementById("notif-bell");
        const notifDropdown = document.getElementById("notif-dropdown");

        if (notifBell && notifDropdown) {
            notifBell.addEventListener("click", function (e) {
                e.preventDefault();
                notifDropdown.classList.toggle("show");
            });

            // Close dropdown when clicking outside
            document.addEventListener("click", function (e) {
                if (!notifBell.contains(e.target) && !notifDropdown.contains(e.target)) {
                    notifDropdown.classList.remove("show");
                }
            });
        }

        // Fetch Notifications
        function fetchNotifications() {
            fetch("api/fetch_notificat_admin.php")
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        const notifList = document.getElementById("notif-list");
                        notifList.innerHTML = "";

                        if (data.notifications.length === 0) {
                            notifList.innerHTML = `
                                <div class="text-center py-3">
                                    <p class="mb-0">No new notifications.</p>
                                </div>
                            `;
                        } else {
                            data.notifications.forEach(notif => {
                                const notifItem = document.createElement("div");
                                notifItem.className = "notif-item";
                                notifItem.innerHTML = `
                                    <div class="d-flex justify-content-between align-items-center">
                                        <p class="mb-0">${notif.message}</p>
                                        <button class="btn btn-sm btn-outline-danger delete-notif" data-id="${notif.id}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">${notif.created_at}</small>
                                `;
                                notifList.appendChild(notifItem);
                            });
                        }

                        // Update notification count
                        const notifCount = document.getElementById("notif-count");
                        notifCount.textContent = data.notifications.length;
                    } else {
                        console.error("Failed to fetch notifications:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error fetching notifications:", error);
                });
        }

        // Delete Notification
        document.getElementById("notif-list").addEventListener("click", function (e) {
            if (e.target.closest(".delete-notif")) {
                const notifId = e.target.closest(".delete-notif").dataset.id;

                fetch(`api/delete_notification.php?id=${notifId}`, {
                    method: "DELETE"
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        fetchNotifications(); // Refresh notifications
                    } else {
                        console.error("Failed to delete notification:", data.message);
                    }
                })
                .catch(error => {
                    console.error("Error deleting notification:", error);
                });
            }
        });

        // Delete All Notifications
        document.getElementById("delete-all-notif").addEventListener("click", function () {
            fetch("api/delete_all_notifications.php", {
                method: "DELETE"
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    fetchNotifications(); // Refresh notifications
                } else {
                    console.error("Failed to delete all notifications:", data.message);
                }
            })
            .catch(error => {
                console.error("Error deleting all notifications:", error);
            });
        });

        // Fetch notifications on page load
        fetchNotifications();
    });
    </script>
</body>
</html>