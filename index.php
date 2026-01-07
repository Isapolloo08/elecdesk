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
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="./assets/comments_and_notifications.js"></script>
    <style>
        :root {
            --primary: #0056b3;          /* Deeper blue */
            --primary-dark: #003d82;     /* Navy blue */
            --primary-light: #e6f2ff;    /* Light blue */
            --secondary: #f0f7ff;        /* Ice blue background */
            --accent: #ffc107;           /* Gold accent for Mabuhay flair */
            --accent-blue: #0d47a1;
            --text-dark: #102a43;        /* Dark blue text */
            --text-light: #486581;       /* Medium blue text */
            --white: #ffffff;
            --border: #cce4ff;           /* Light blue border */
            --success: #28a745;          /* Green for success messages */
            --gradient-blue: linear-gradient(135deg, #0056b3 0%, #0091ff 100%);
        }
        
     
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            color: #333;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary), var(--accent-blue));
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
        /* Enhanced Buttons */
        .btn-login, .btn-logout {
            background-color: var(--white);
            color: var(--primary);
            border: none;
            padding: 0.6rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-login:hover, .btn-logout:hover {
            background-color: var(--accent);
            color: var(--text-dark);
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        
        /* Animated Page Header */
        .page-header {
            background: var(--gradient-blue);
            color: var(--white);
            padding: 3rem 0 4rem;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
            border-bottom: none;
        }
        
        .page-header::after {
            content: '';
            position: absolute;
            bottom: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--white);
            transform: skewY(-3deg);
        }
        
        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
            display: inline-block;
            z-index: 1;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 80px;
            height: 4px;
            background-color: var(--accent);
            border-radius: 2px;
        }
        
        /* Enhanced Search Box */
        .search-box-container {
            margin-top: -2rem;
            margin-bottom: 3rem;
            position: relative;
            z-index: 10;
        }
        
        .search-box {
            padding: 1.2rem 1.5rem;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .search-box:focus {
            outline: none;
            box-shadow: 0 5px 25px rgba(0,83,179,0.25);
        }
        
        .input-group-text {
            border-radius: 50px 0 0 50px;
            padding: 0 1.5rem;
        }
        
        .search-box {
            border-radius: 0 50px 50px 0;
        }
        
        /* Enhanced Candidate Cards */
        .candidate-card {
            background-color: var(--white);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            text-decoration: none;
            color: var(--text-dark);
            position: relative;
            overflow: hidden;
            border: none;
            height: 100%;
        }
        
        .candidate-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: var(--gradient-blue);
        }
        
        .candidate-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.1);
        }
        
        .candidate-card:hover::after {
            opacity: 1;
        }
        
        .candidate-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(255,255,255,0) 80%, var(--primary-light) 100%);
            opacity: 0;
            transition: opacity 0.4s;
            pointer-events: none;
        }
        
        /* Enhanced Profile Image */
        .profile-img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--white);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .candidate-card:hover .profile-img {
            transform: scale(1.05);
            border-color: var(--accent);
        }
        
        .candidate-profile {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            transition: transform 0.3s;
        }
        
        .candidate-card:hover .candidate-profile {
            transform: translateY(-5px);
        }
        
        .candidate-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-left: 1.5rem;
            color: var(--primary);
            transition: color 0.3s;
        }
        
        .candidate-card:hover .candidate-name {
            color: var(--primary-dark);
        }
        
        .candidate-info {
            margin-top: 1.5rem;
            position: relative;
            transition: transform 0.3s;
        }
        
        .candidate-card:hover .candidate-info {
            transform: translateY(-3px);
        }
        
        .candidate-info p {
            margin-bottom: 0.8rem;
            font-size: 0.95rem;
            color: var(--text-light);
            padding-left: 1.5rem;
            position: relative;
        }
        
        .candidate-info p:last-child {
            margin-bottom: 0;
        }
        
        .candidate-info p strong {
            color: var(--primary);
            font-weight: 600;
            display: block;
            margin-bottom: 0.3rem;
        }
        
        .candidate-info p::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.3rem;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--accent);
        }
        
        /* Vibrant Footer */
        footer {
            background: var(--gradient-blue);
            color: var(--white);
            padding: 3rem 0;
            margin-top: 5rem;
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: -50px;
            left: 0;
            right: 0;
            height: 100px;
            background: var(--gradient-blue);
            transform: skewY(-3deg);
        }
        
        footer h5 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        footer p {
            font-size: 1rem;
            opacity: 0.9;
        }
        
        /* Notification Enhancements */
        .notification-bell {
            font-size: 1.2rem;
            transition: transform 0.3s;
        }
        
        .notification-bell:hover {
            transform: rotate(15deg);
        }
        
        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            min-width: 22px;
            height: 22px;
            background: var(--accent);
            color: var(--text-dark);
            border-radius: 50%;
            font-size: 0.7rem;
            padding: 0 4px;
            font-weight: 700;
        }
        
        .notification-dropdown {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .notification-header {
            background: var(--gradient-blue);
            color: var(--white);
            font-weight: 600;
            padding: 1rem;
            text-align: center;
        }
        
        .notification-item {
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--border);
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item:hover {
            background-color: var(--primary-light);
            border-left-color: var(--primary);
        }
        
        .notification-link {
            text-decoration: none;
            color: var(--text-dark);
            display: block;
            transition: color 0.3s;
        }
        
        .notification-link:hover {
            color: var(--primary);
        }
        
        .notification-time {
            display: block;
            color: var(--text-light);
            font-size: 0.75rem;
        }
        
        /* Mabuhay Accent Elements */
        .mabuhay-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background-color: var(--accent);
            color: var(--text-dark);
            font-weight: 700;
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transform: rotate(5deg);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            z-index: 2;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s forwards;
        }
        
        .fade-out {
            animation: fadeOut 0.5s forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(10px); }
        }
        
        /* Loading animation */
        .loader {
            width: 30px;
            height: 30px;
            border: 3px solid var(--primary-light);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Button styles */
        .btn-primary {
            background: var(--gradient-blue);
            border: none;
            border-radius: 50px;
            padding: 0.7rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, #003d82 0%, #0078d4 100%);
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .section-title {
                font-size: 2rem;
            }
            
            .page-header {
                padding: 2rem 0 3rem;
            }
            
            .candidate-card {
                padding: 1.5rem;
            }
            
            .profile-img {
                width: 70px;
                height: 70px;
            }
        }
    </style>
</head>
<body>
  <!-- Navbar -->
<!-- Navbar -->
<nav class="navbar navbar-expand-lg fixed-top" style="background-color: var(--dark-gray); border-bottom: 2px solid var(--orange);">
    <div class="container">
        <!-- Brand Logo -->
        <a class="navbar-brand" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : (isset($_SESSION['role']) && $_SESSION['role'] === 'student' ? 'mainhomepage.php' : 'pages/candidate.php'); ?>">
            <img src="logo.jpg" alt="Elecdesk Logo" class="me-2" style="height: 40px; border-radius: 50%; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2); transition: transform 0.3s;">
            <span class="fw-bold text-white">ElecDesk</span>
        </a>

        <!-- Main Navigation Links (Moved to the left) -->
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : (isset($_SESSION['role']) && $_SESSION['role'] === 'student' ? 'mainhomepage.php' : 'pages/candidate.php'); ?>">
                    <i class="fas fa-home me-1"></i> Home
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active text-white" href="index.php">
                    <i class="fas fa-users me-1"></i> Candidates
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item">
                <a class="nav-link text-white" href="profile.php">
                    <i class="fas fa-user-circle me-1"></i> My Profile
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content for the right side -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <!-- Admin Links -->
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link text-white" href="pages/dashboard.php">
                        <i class="fas fa-chart-bar me-1"></i> Analytics
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="pages/account_management.php">
                        <i class="fas fa-user-cog me-1"></i> Accounts
                    </a>
                </li>
                <?php endif; ?>

                <!-- Notification Dropdown -->
                <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white notification-bell" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell"></i>
                        <span class="notification-count badge bg-danger" id="notif-count">0</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown">
                        <h6 class="dropdown-header notification-header">Notifications</h6>
                        <div id="notif-list" class="px-3">
                            <div class="text-center py-3">
                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2 mb-0">Loading notifications...</p>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <div class="p-2">
                            <button id="delete-all-notif" class="btn btn-primary w-100">Delete All</button>
                        </div>
                    </div>
                </li>
                <?php endif; ?>
            </ul>

            <!-- Login/Logout Button -->
            <?php if (isset($_SESSION['user_id'])): ?>
            <a href="pages/logout.php" class="btn btn-logout ms-3 text-white" style="background-color: var(--orange); border-radius: 20px; padding: 0.5rem 1.2rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
            <?php else: ?>
            <a href="pages/login.php" class="btn btn-login ms-3 text-white" style="background-color: var(--orange); border-radius: 20px; padding: 0.5rem 1.2rem; font-weight: 600; transition: all 0.3s; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);">
                <i class="fas fa-sign-in-alt me-1"></i> Login
            </a>
            <?php endif; ?>
        </div>
    </div>
</nav>

    <!-- Page Header -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <h2 class="section-title mt-5">Candidate List</h2>
                    <p class="text-white-50">Learn about candidates and make informed decisions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Box Container -->
    <div class="container search-box-container">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="input-group">
                    <span class="input-group-text bg-white">
                        <i class="fas fa-search text-primary"></i>
                    </span>
                    <input type="text" id="search" placeholder="Search candidates by name, credentials, or platform..." class="form-control search-box border-start-0">
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container pb-5">
        <!-- Candidates Grid -->
        <div class="row g-4" id="search-results">
            <?php
            include 'includes/db.php';
            $query = "SELECT * FROM candidates";    
            $result = $conn->query($query);
            while ($row = $result->fetch_assoc()) {
                echo '<div class="col-12 col-md-6 col-lg-4">
                    <a href="pages/candidate.php?id='.$row['id'].'" class="candidate-card d-block">
                        <span class="mabuhay-badge">Mabuhay!</span>
                        <div class="candidate-profile">
                            <img src="assets/'.$row['image'].'" alt="'.$row['name'].'" class="profile-img">
                            <span class="candidate-name">'.$row['name'].'</span>
                        </div>
                        <div class="candidate-info">
                            <p><strong>Credentials:</strong> '.$row['credentials'].'</p>
                            <p><strong>Background:</strong> '.$row['background'].'</p>
                            <p><strong>Platform:</strong> '.$row['platform'].'</p>
                        </div>
                    </a>
                </div>';
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Candidate Information System</h5>
                    <p class="mb-0">Helping students make informed voting decisions.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; 2025 Elecdesk. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById("search");
        const searchResults = document.getElementById("search-results");

        let debounceTimer;

        searchInput.addEventListener("keyup", function () {
            clearTimeout(debounceTimer);

            debounceTimer = setTimeout(() => {
                let query = searchInput.value.trim();
                let url = "./api/search_candidates.php?query=" + encodeURIComponent(query);

                fetch(url)
                    .then(response => response.text())
                    .then(data => {
                        searchResults.classList.add("fade-out");

                        setTimeout(() => {
                            searchResults.innerHTML = data;
                            searchResults.classList.remove("fade-out");
                            searchResults.classList.add("fade-in");
                        }, 300);
                    })
                    .catch(error => console.error("Search fetch error:", error));
            }, 300);  // Debounce time
        });
    });
    </script>
</body>
</html>