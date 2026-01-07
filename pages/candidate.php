    <?php
    session_start();
    include '../includes/db.php';


    if (!isset($_GET['id'])) {
        header("Location: /index.php");
        exit();
    }

    $candidate_id = $_GET['id'];

    // Fetch candidate details
    $query = "SELECT name, image, credentials, created_at, background, platform, grade_level, strand, school_name, email, contact FROM candidates WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $candidate = $result->fetch_assoc();

    if (!$candidate) {
        echo "<p>Candidate not found.</p>";
        exit();
    }



    // Fetch comments with replies
    $commentQuery = "SELECT comments.*, users.username 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE candidate_id = ? AND parent_id IS NULL 
                    ORDER BY comments.created_at DESC";
    $stmt = $conn->prepare($commentQuery);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $comments = $stmt->get_result();
    ?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($candidate['name']) ?> - Profile</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/jpg" href="../logo.jpg">
    <script src="../assets/comments_and_notifications.js"></script>
    <style>
        :root {
            --primary-blue: #1a73e8;
            --secondary-blue: #4285f4;
            --light-blue: #e8f0fe;
            --dark-blue: #174ea6;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background-color: var(--primary-blue);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand img {
            height: 40px;
        }
        
        .nav-link {
            color: white !important;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .nav-link:hover {
            color: var(--light-blue) !important;
            transform: translateY(-2px);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.7rem;
        }
        
        .profile-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--secondary-blue), var(--dark-blue));
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        
        .profile-img {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .profile-content {
            padding: 25px;
        }
        
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .info-icon {
            color: var(--primary-blue);
            width: 30px;
            text-align: center;
            margin-right: 10px;
        }
        
        .comment-section {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            padding: 25px;
            margin-top: 30px;
        }
        
        .comment-form textarea {
            border-radius: 10px;
            padding: 15px;
            border: 2px solid #e9ecef;
            transition: border 0.3s;
        }
        
        .comment-form textarea:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.25rem rgba(26, 115, 232, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
            padding: 10px 25px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
        }
        
        .comment {
            background-color: var(--light-blue);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
            border-left: 4px solid var(--primary-blue);
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 10px;
            font-weight: bold;
        }
        
        .replies {
            margin-left: 30px;
            margin-top: 15px;
            padding-left: 15px;
            border-left: 2px solid var(--secondary-blue);
        }
        
        .reply {
            background-color: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .reply-form {
            margin-top: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: white;
            border-radius: 10px;
            display: none;
        }
        
        .notification-dropdown {
            min-width: 300px;
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
        }
        
        .notification-header {
            background-color: var(--light-blue);
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: var(--light-blue);
        }
        
        .badge-pill {
            padding: 5px 10px;
            border-radius: 50px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <!-- Logo -->
            <a class="navbar-brand" href="<?= isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : '../index.php' ?>">
                <img src="../logo.jpg" alt="Elecdesk Logo" class="img-fluid">
            </a>
            
            <!-- Toggle Button for Mobile -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : '../mainhomepage.php' ?>">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= isset($_SESSION['role']) ? '../index.php' : 'pages/candidate.php' ?>">
                            <i class="fas fa-users me-1"></i> Candidates
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../profile.php">
                            <i class="fas fa-user-circle me-1"></i> My Profile
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/dashboard.php">
                            <i class="fas fa-chart-bar me-1"></i> Analytics
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pages/account_management.php">
                            <i class="fas fa-user-cog me-1"></i> Accounts
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <!-- Notification & User Actions -->
                <div class="d-flex">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Notification Dropdown -->
                    <div class="dropdown me-3">
                        <a class="nav-link position-relative" href="#" id="notif-bell" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fs-5"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notif-count">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end notification-dropdown" id="notif-dropdown">
                            <li>
                                <div class="notification-header d-flex justify-content-between align-items-center">
                                    <h6 class="m-0 fw-bold">Notifications</h6>
                                    <button class="btn btn-sm text-danger" id="delete-all-notif">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </li>
                            <div id="notif-list" class="py-2">
                                <div class="text-center p-3">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </ul>
                    </div>
                    <!-- Logout Button -->
                    <a href="logout.php" class="btn btn-light rounded-pill">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                    <?php else: ?>
                    <!-- Login Button -->
                    <a href="login.php" class="btn btn-light rounded-pill">
                        <i class="fas fa-sign-in-alt me-1"></i> Login
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <div class="row">
            <!-- Candidate Profile Section -->
            <div class="col-lg-8 mx-auto">
                <div class="profile-card mb-4">
                    <div class="profile-header">
                        <img src="../assets/<?= htmlspecialchars($candidate['image']) ?>" alt="<?= htmlspecialchars($candidate['name']) ?>" class="profile-img rounded-circle mb-3">
                        <h1 class="fw-bold"><?= htmlspecialchars($candidate['name']) ?></h1>
                        <div class="d-flex justify-content-center">
                            <span class="badge bg-white text-primary me-2">
                                <i class="fas fa-graduation-cap me-1"></i>
                                <?= htmlspecialchars($candidate['grade_level']) ?>
                            </span>
                            <span class="badge bg-white text-primary me-2">
                                <i class="fas fa-bookmark me-1"></i>
                                <?= htmlspecialchars($candidate['strand']) ?>
                            </span>
                            <span class="badge bg-white text-primary">
                                <i class="fas fa-school me-1"></i>
                                <?= htmlspecialchars($candidate['school_name']) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="profile-content">
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-user-circle fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Background</h5>
                                <p><?= htmlspecialchars($candidate['background']) ?></p>
                            </div>
                        </div>
                        
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-award fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Credentials</h5>
                                <p><?= htmlspecialchars($candidate['credentials']) ?></p>
                            </div>
                        </div>
                        
                        <div class="info-item d-flex">
                            <div class="info-icon">
                                <i class="fas fa-bullhorn fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold">Platform</h5>
                                <p><?= htmlspecialchars($candidate['platform']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Comment Section -->
                <div class="comment-section">
                    <h3 class="mb-4 fw-bold">
                        <i class="fas fa-comments text-primary me-2"></i>
                        Discussion
                    </h3>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Comment Form -->
                    <form id="comment-form" class="comment-form mb-4">
                        <div class="mb-3">
                            <label for="comment-input" class="form-label">Share your thoughts</label>
                            <textarea class="form-control" id="comment-input" name="comment" rows="3" placeholder="Write a comment..." required></textarea>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i> Post Comment
                            </button>
                        </div>
                        <input type="hidden" id="candidate-id" value="<?= $candidate_id ?>">
                    </form>
                    <?php else: ?>
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        Please <a href="../pages/login.php" class="alert-link">login</a> to join the discussion.
                    </div>
                    <?php endif; ?>
                    
                    <!-- Comments List -->
                    <div class="comments-container">
                        <?php if ($comments->num_rows > 0): ?>
                            <?php while ($row = $comments->fetch_assoc()): ?>
                                <div class="comment" id="comment-<?= $row['id'] ?>">
                                    <div class="comment-header">
                                        <div class="comment-avatar">
                                            <?= substr(htmlspecialchars($row['username']), 0, 1) ?>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 fw-bold"><?= htmlspecialchars($row['username']) ?></h5>
                                            <small class="text-muted"><?= $row['created_at'] ?></small>
                                        </div>
                                    </div>
                                    <div class="comment-body">
                                        <p><?= htmlspecialchars($row['comment']) ?></p>
                                    </div>
                                    
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <div class="comment-actions mt-2">
                                        <button class="btn btn-sm btn-outline-primary toggle-reply-form" data-comment-id="<?= $row['id'] ?>">
                                            <i class="fas fa-reply me-1"></i> Reply
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Replies -->
                                    <?php
                                    $comment_id = $row['id'];
                                    $replyQuery = "SELECT replies.id, replies.reply, users.username, replies.created_at 
                                                FROM replies 
                                                JOIN users ON replies.user_id = users.id 
                                                WHERE comment_id = ?";
                                    $replyStmt = $conn->prepare($replyQuery);
                                    $replyStmt->bind_param("i", $comment_id);
                                    $replyStmt->execute();
                                    $replies = $replyStmt->get_result();
                                    ?>
                                    
                                    <?php if ($replies->num_rows > 0): ?>
                                    <div id="replies-<?= $comment_id ?>" class="replies">
                                        <?php while ($reply = $replies->fetch_assoc()): ?>
                                            <div class="reply" id="reply-<?= $reply['id'] ?>">
                                                <div class="comment-header">
                                                    <div class="comment-avatar" style="background-color: var(--secondary-blue);">
                                                        <?= substr(htmlspecialchars($reply['username']), 0, 1) ?>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold"><?= htmlspecialchars($reply['username']) ?></h6>
                                                        <small class="text-muted"><?= $reply['created_at'] ?></small>
                                                    </div>
                                                </div>
                                                <div class="comment-body">
                                                    <p><?= htmlspecialchars($reply['reply']) ?></p>
                                                </div>
                                            </div>
                                        <?php endwhile; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <!-- Reply Form -->
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                    <form class="reply-form" id="reply-form-<?= $comment_id ?>" data-comment-id="<?= $comment_id ?>">
                                        <div class="mb-3">
                                            <textarea class="form-control reply-input" name="reply" rows="2" placeholder="Write a reply..." required></textarea>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <button type="button" class="btn btn-sm btn-outline-secondary cancel-reply">Cancel</button>
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                <i class="fas fa-paper-plane me-1"></i> Reply
                                            </button>
                                        </div>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <i class="far fa-comment-dots fa-4x text-muted mb-3"></i>
                                <h5>No comments yet</h5>
                                <p class="text-muted">Be the first to share your thoughts!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="mt-5 py-4 bg-primary text-white">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>ElecDesk</h5>
                    <p>Your platform for school election management</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; 2025 ElecDesk. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle reply forms
        document.querySelectorAll('.toggle-reply-form').forEach(button => {
            button.addEventListener('click', function() {
                const commentId = this.getAttribute('data-comment-id');
                const replyForm = document.getElementById(`reply-form-${commentId}`);
                replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
            });
        });
        
        // Cancel reply
        document.querySelectorAll('.cancel-reply').forEach(button => {
            button.addEventListener('click', function() {
                this.closest('.reply-form').style.display = 'none';
            });
        });
        
        // Handle comment form submission
        const commentForm = document.getElementById('comment-form');
        if (commentForm) {
            commentForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const commentInput = document.getElementById('comment-input');
                const candidateId = document.getElementById('candidate-id').value;
                
                if (commentInput.value.trim()) {
                    // Ajax call would go here
                    Swal.fire({
                        title: 'Comment Posted!',
                        text: 'Your comment has been submitted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#1a73e8'
                    }).then(() => {
                        commentInput.value = '';
                        // In a real implementation, you would refresh comments or add the new comment to the DOM
                    });
                }
            });
        }
        
        // Handle reply form submission
        document.querySelectorAll('.reply-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const commentId = this.getAttribute('data-comment-id');
                const replyInput = this.querySelector('.reply-input');
                
                if (replyInput.value.trim()) {
                    // Ajax call would go here
                    Swal.fire({
                        title: 'Reply Posted!',
                        text: 'Your reply has been submitted successfully.',
                        icon: 'success',
                        confirmButtonColor: '#1a73e8'
                    }).then(() => {
                        replyInput.value = '';
                        this.style.display = 'none';
                        // In a real implementation, you would refresh replies or add the new reply to the DOM
                    });
                }
            });
        });
        
        // Simulate notification loading
        setTimeout(() => {
            document.getElementById('notif-count').textContent = '3';
            document.getElementById('notif-list').innerHTML = `
                <div class="notification-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1"><strong>New comment</strong> on your profile</p>
                            <small class="text-muted">2 minutes ago</small>
                        </div>
                        <span class="badge bg-primary badge-pill">New</span>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1"><strong>Election results</strong> are now available</p>
                            <small class="text-muted">1 hour ago</small>
                        </div>
                    </div>
                </div>
                <div class="notification-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1">Welcome to <strong>ElecDesk</strong>!</p>
                            <small class="text-muted">1 day ago</small>
                        </div>
                    </div>
                </div>
            `;
        }, 1000);
    });
    </script>
</body>
</html>
