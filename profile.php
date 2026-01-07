<?php
session_start();
include './includes/db.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../pages/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch user data based on role
if ($role === 'candidate') {
    $query = "
        SELECT 
            c.name, 
            c.image, 
            c.credentials, 
            c.created_at, 
            c.background, 
            c.platform, 
            c.grade_level, 
            c.strand, 
            c.school_name, 
            c.email, 
            c.contact,
            u.username
        FROM 
            candidates c
        INNER JOIN 
            users u 
        ON 
            c.user_id = u.id
        WHERE 
            c.user_id = ?
    ";
} elseif ($role === 'student') {
    $query = "
       SELECT `username`, `gmail`, `password`, `role`, `created_at`, `image` FROM `users` WHERE id = ?
    ";
} else {
    die("Invalid role");
}


$stmt = $conn->prepare($query);
if ($stmt === false) {
    die('Prepare failed: ' . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("No record found for this user.");
}

// Handle Profile Update for Candidates
if ($_SERVER["REQUEST_METHOD"] === "POST" && $role === 'candidate') {
    $name = $_POST['name'];
    $credentials = $_POST['credentials'];
    $background = $_POST['background'];
    $platform = $_POST['platform'];
    $grade_level = $_POST['grade_level'];
    $strand = $_POST['strand'];
    $school_name = $_POST['school_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $image = $user['image'];

    // Handle Image Upload
    if (!empty($_FILES['image']['name'])) {
        $targetDir = "./assets/";
        $fileName = basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath)) {
            $image = $fileName;
        } else {
            echo "<div class='alert alert-danger'>Image upload failed.</div>";
        }
    }

    // Update candidate record
    $updateQuery = "
        UPDATE candidates 
        SET name = ?, credentials = ?, background = ?, platform = ?, 
            grade_level = ?, strand = ?, school_name = ?, email = ?, 
            contact = ?, image = ? 
        WHERE user_id = ?";
    
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssssssssssi", $name, $credentials, $background, $platform, $grade_level, $strand, $school_name, $email, $contact, $image, $user_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Profile updated successfully!</div>";
        header("Refresh:1");
    } else {
        echo "<div class='alert alert-danger'>Failed to update profile: " . $stmt->error . "</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($role); ?> Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Favicon -->
    <link rel="icon" type="image/jpg" href="../logo.jpg">
    <script src="./assets/comments_and_notifications.js"></script>
    <style>
        :root {
            --primary-blue: #1a73e8;
            --secondary-blue: #4285f4;
            --accent-blue: #0d47a1;
            --light-blue: #e8f0fe;
            --ultra-light-blue: #f5f9ff;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f4f8;
            color: #333;
        }

        /* Navbar Styling */
        .navbar {
            background: linear-gradient(135deg, var(--primary-blue), var(--accent-blue));
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
        
        /* Profile Header Card */
        .profile-header {
            background: linear-gradient(to right, #ffffff, var(--light-blue));
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 40%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMzkuNiAyNjRjNTQgMzYgMTI2IDE5IDE4MyAtMzIuNWMzOCAtMzQgNjUgLTcwIDY1IC0xMTIuNWMwIC00OCAtMzIgLTkwIC05MCAtOTBjLTYwIDAgLTExMi41IDQ1IC0xNTIuNSA5MGMtMzcuNSA0MiAtNTUuNSAxMDIgLTUuNSAxNDVaIiBmaWxsPSJyZ2JhKDI2LDExNSwyMzIsMC4wNykiLz48L3N2Zz4=') no-repeat right bottom;
            opacity: 0.4;
        }
        
        .profile-image {
            width: 130px;
            height: 130px;
            object-fit: cover;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
            transition: transform 0.4s;
        }
        
        .profile-image:hover {
            transform: scale(1.05);
        }
        
        .badge-role {
            background-color: var(--accent-blue);
            color: white;
            font-weight: 600;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .member-since {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        /* Profile Cards */
        .profile-card {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background: linear-gradient(to right, var(--primary-blue), var(--secondary-blue));
            color: white;
            border-radius: 15px 15px 0 0 !important;
            font-weight: 600;
            font-size: 1.2rem;
            padding: 1rem 1.5rem;
        }
        
        /* Form Controls */
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.6rem 1rem;
            border: 1px solid #e0e0e0;
            background-color: var(--ultra-light-blue);
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.15);
        }
        
        .form-control:disabled, .form-select:disabled {
            background-color: #f8f9fa;
            color: #495057;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 0.5rem;
        }
        
        /* Buttons */
        .btn-primary {
            background: var(--primary-blue);
            border: none;
            box-shadow: 0 2px 5px rgba(26, 115, 232, 0.3);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: var(--accent-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(26, 115, 232, 0.4);
        }
        
        .btn-success {
            background: #34a853;
            border: none;
            box-shadow: 0 2px 5px rgba(52, 168, 83, 0.3);
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-success:hover {
            background: #2e8b57;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(52, 168, 83, 0.4);
        }
        
        /* Animation for page elements */
        .fade-in {
            animation: fadeIn 0.6s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Highlight animation */
        .highlight {
            animation: highlightPulse 3s ease;
        }
        
        @keyframes highlightPulse {
            0% { background-color: rgba(66, 133, 244, 0.2); }
            50% { background-color: rgba(66, 133, 244, 0.3); }
            100% { background-color: transparent; }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .profile-header {
                text-align: center;
            }
            
            .profile-image {
                margin-bottom: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : 'mainhomepage.php'; ?>">
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
                        <a class="nav-link" href="<?php echo isset($_SESSION['role']) && $_SESSION['role'] === 'candidate' ? 'candidate_user.php' : 'mainhomepage.php'; ?>">
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
                        <a class="nav-link active" href="profile.php">
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

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Profile Header -->
        <div class="row mb-4 fade-in">
            <div class="col-12">
                <div class="profile-header p-4">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center text-md-start">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role']): ?>
                                <img src="./assets/<?php echo htmlspecialchars($user['image']); ?>" alt="Profile Image" class="profile-image mb-3 mb-md-0">
                            <?php else: ?>
                                <img src="images/default-profile.jpg" alt="Profile Image" class="profile-image mb-3 mb-md-0">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-9">
                            <h2 class="fw-bold mb-2"><?php echo htmlspecialchars($role === 'candidate' ? $user['name'] : $user['username']); ?></h2>
                            <span class="badge-role mb-3 d-inline-block"><?php echo ucfirst($role); ?></span>
                            <p class="member-since mb-0">
                                <i class="far fa-calendar-alt me-2"></i> Member Since: <?php echo htmlspecialchars($user['created_at']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
      <!-- Profile Information Section -->
<div class="row fade-in justify-content-center" style="animation-delay: 0.2s">
    <div class="col-lg-8 col-xl-6 mb-4"> <!-- Adjusted column width for better centering -->
        <div class="profile-card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-info-circle me-2"></i> Account Information
            </div>
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <p class="form-control bg-light">
                        <?php echo htmlspecialchars($role === 'candidate' ? $user['name'] : $user['username']); ?>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <p class="form-control bg-light">
                        <?php echo htmlspecialchars($role === 'candidate' ? $user['email'] : $user['gmail']); ?>
                    </p>
                </div>
                <?php if ($role !== 'student'): ?>
                <div class="mb-3">
                    <label class="form-label">Contact Number</label>
                    <p class="form-control bg-light">
                        <?php echo htmlspecialchars($role === 'candidate' ? $user['contact'] : 'N/A'); ?>
                    </p>
                </div>
                <?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <p class="form-control bg-light"><?php echo ucfirst($role); ?></p>
                </div>
                <div class="mb-3">
                    <label class="form-label">Account Created</label>
                    <p class="form-control bg-light"><?php echo htmlspecialchars($user['created_at']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php if ($role === 'candidate'): ?>
    <div class="col-lg-8 col-xl-6 mb-4"> <!-- Adjusted column width for better centering -->
        <div class="profile-card h-100">
            <div class="card-header d-flex align-items-center">
                <i class="fas fa-user-tie me-2"></i> Candidate Information
            </div>
            <div class="card-body p-4">
                <form action="profile.php" method="post" enctype="multipart/form-data" id="profileForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Level</label>
                            <input type="text" class="form-control" name="grade_level" value="<?php echo htmlspecialchars($user['grade_level']); ?>" required disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Strand</label>
                            <input type="text" class="form-control" name="strand" value="<?php echo htmlspecialchars($user['strand']); ?>" required disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">School Name</label>
                            <input type="text" class="form-control" name="school_name" value="<?php echo htmlspecialchars($user['school_name']); ?>" required disabled>
                        </div>
                        <div class="col-md-6 mb-3 d-none" id="emailField">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required disabled>
                        </div>
                        <div class="col-md-6 mb-3 d-none" id="contactField">
                            <label class="form-label">Contact</label>
                            <input type="text" class="form-control" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required disabled>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Credentials</label>
                            <textarea class="form-control" name="credentials" rows="3" required disabled><?php echo htmlspecialchars($user['credentials']); ?></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Background</label>
                            <textarea class="form-control" name="background" rows="3" required disabled><?php echo htmlspecialchars($user['background']); ?></textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Platform</label>
                            <textarea class="form-control" name="platform" rows="3" required disabled><?php echo htmlspecialchars($user['platform']); ?></textarea>
                        </div>
                        <div class="col-12 mb-3 d-none" id="imageField">
                            <label class="form-label">Profile Image</label>
                            <input type="file" class="form-control" name="image" disabled>
                        </div>
                        <div class="col-12 text-end mt-4">
                            <button type="button" id="editBtn" class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Edit Profile
                            </button>
                            <button type="submit" class="btn btn-success d-none" id="updateBtn">
                                <i class="fas fa-save me-1"></i> Update Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h5>ElecDesk</h5>
                    <p class="text-muted">Your comprehensive election management platform</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">© <?php echo date('Y'); ?> ElecDesk. All rights reserved.</p>
                    <small class="text-muted">Empowering democratic processes</small>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
   document.addEventListener("DOMContentLoaded", function () {
    // Profile editing functionality
    const editBtn = document.getElementById('editBtn');
    const updateBtn = document.getElementById('updateBtn');
    const inputs = document.querySelectorAll('#profileForm input, #profileForm textarea');
    const emailField = document.getElementById('emailField');
    const contactField = document.getElementById('contactField');
    const imageField = document.getElementById('imageField');

    if (editBtn && updateBtn) {
        editBtn.addEventListener('click', function () {
            const isEditing = editBtn.innerHTML.includes('Cancel');

            if (isEditing) {
                // Cancel editing
                editBtn.innerHTML = '<i class="fas fa-edit me-1"></i> Edit Profile';
                editBtn.classList.remove('btn-danger');
                editBtn.classList.add('btn-primary');
                updateBtn.classList.add('d-none');

                // Hide optional fields & disable all inputs
                if (emailField) emailField.classList.add('d-none');
                if (contactField) contactField.classList.add('d-none');
                if (imageField) imageField.classList.add('d-none');

                inputs.forEach(input => input.disabled = true);
            } else {
                // Enable editing
                editBtn.innerHTML = '<i class="fas fa-times me-1"></i> Cancel';
                editBtn.classList.remove('btn-primary');
                editBtn.classList.add('btn-danger');
                updateBtn.classList.remove('d-none');

                // Show optional fields & enable all inputs
                if (emailField) emailField.classList.remove('d-none');
                if (contactField) contactField.classList.remove('d-none');
                if (imageField) imageField.classList.remove('d-none');

                inputs.forEach(input => input.disabled = false);
            }
        });
    }

    // // Notifications functionality
    // const notifBell = document.getElementById("notif-bell");
    // const notifList = document.getElementById("notif-list");
    // const notifCount = document.getElementById("notif-count");
    // const deleteAllBtn = document.getElementById("delete-all-notif");

    // function fetchNotifications() {
    //     fetch("api/fetch_notificat_admin.php")
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.status === "success") {
    //                 console.log("Notifications Data:", data.notifications);
    //                 notifList.innerHTML = "";
    //                 let count = data.notifications.length;

    //                 if (count === 0) {
    //                     notifList.innerHTML = `
    //                         <div class="text-center py-3">
    //                             <p class="mb-0">No new notifications.</p>
    //                         </div>
    //                     `;
    //                     notifCount.classList.add('d-none');
    //                 } else {
    //                     notifCount.textContent = count;
    //                     notifCount.classList.remove('d-none');

    //                     data.notifications.forEach(notif => {
    //                         const notifItem = document.createElement('div');
    //                         notifItem.className = 'notification-item';
    //                         notifItem.innerHTML = `
    //                             <div class="d-flex justify-content-between align-items-center">
    //                                 <div>
    //                                     <p class="mb-1">${notif.message}</p>
    //                                     <small class="notification-time">${notif.created_at}</small>
    //                                 </div>
    //                                 <button class="btn btn-sm btn-outline-danger delete-notif" data-id="${notif.id}">
    //                                     <i class="fas fa-trash-alt"></i>
    //                                 </button>
    //                             </div>
    //                         `;
    //                         notifList.appendChild(notifItem);
    //                     });
    //                 }
    //             } else {
    //                 console.error("Failed to fetch notifications:", data.message);
    //             }
    //         })
    //         .catch(error => {
    //             console.error("Error fetching notifications:", error);
    //         });
    // }

    // // Delete a single notification
    // notifList.addEventListener('click', function (e) {
    //     if (e.target.closest('.delete-notif')) {
    //         const notifId = e.target.closest('.delete-notif').dataset.id;
    //         fetch(`api/delete_notification.php?id=${notifId}`, {
    //             method: 'DELETE'
    //         })
    //             .then(response => response.json())
    //             .then(data => {
    //                 if (data.status === "success") {
    //                     fetchNotifications(); // Refresh notifications
    //                 } else {
    //                     console.error("Failed to delete notification:", data.message);
    //                 }
    //             })
    //             .catch(error => {
    //                 console.error("Error deleting notification:", error);
    //             });
    //     }
    // });

    // // Delete all notifications
    // deleteAllBtn.addEventListener('click', function () {
    //     fetch('api/delete_all_notifications.php', {
    //         method: 'DELETE'
    //     })
    //         .then(response => response.json())
    //         .then(data => {
    //             if (data.status === "success") {
    //                 fetchNotifications(); // Refresh notifications
    //             } else {
    //                 console.error("Failed to delete all notifications:", data.message);
    //             }
    //         })
    //         .catch(error => {
    //             console.error("Error deleting all notifications:", error);
    //         });
    // });

    // // Fetch notifications on page load
    // fetchNotifications();

    // // Highlight new notifications
    // const highlightNewNotifications = () => {
    //     const notifItems = document.querySelectorAll('.notification-item');
    //     notifItems.forEach(item => {
    //         item.classList.add('highlight');
    //         setTimeout(() => item.classList.remove('highlight'), 3000);
    //     });
    // };

    // // Simulate new notifications (for demo purposes)
    // setTimeout(() => {
    //     fetchNotifications();
    //     highlightNewNotifications();
    // }, 5000); // Simulate new notifications after 5 seconds
});
    </script>
</body>
</html>