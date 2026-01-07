    <?php
    include '../includes/db.php';
    ob_start(); // Prevents header issues

    // Enable error reporting for debugging
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Log errors to a file
    function logError($message) {
        $logFile = '../logs/error_log.txt';
        $date = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$date] $message\n", FILE_APPEND);
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>";

        // Check database connection
        if (!$conn) {
            logError("Database connection failed: " . mysqli_connect_error());
            die("<script>Swal.fire('Error', 'Database connection failed.', 'error');</script>");
        }

        try {
            // Get user input and trim spaces
            $name = trim($_POST['name'] ?? '');
            $position = trim($_POST['position'] ?? ''); // Changed to dropdown selection
            $credentials = trim($_POST['credentials'] ?? '');
            $background = trim($_POST['background'] ?? '');
            $platform = trim($_POST['platform'] ?? '');
            $grade_level = trim($_POST['grade_level'] ?? '');
            $strand = trim($_POST['strand'] ?? '');
            $school_name = trim($_POST['school_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contact = trim($_POST['contact'] ?? '');
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Check required fields
            if (!$name || !$position || !$credentials || !$background || !$platform || !$grade_level || !$strand || !$school_name || !$email || !$contact || !$username || !$password) {
                throw new Exception("All fields are required.");
            }

            // Validate username
            if (!preg_match('/^[a-zA-Z0-9_]{5,20}$/', $username)) {
                throw new Exception("Invalid username. Must be 5-20 characters (letters, numbers, underscores only).");
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Check if username exists
            $query_check = "SELECT id FROM users WHERE username = ?";
            $stmt_check = $conn->prepare($query_check);
            $stmt_check->bind_param("s", $username);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                throw new Exception("Username already exists. Choose another.");
            }

            // File Upload Handling
            if (!isset($_FILES["image"]) || $_FILES["image"]["error"] !== UPLOAD_ERR_OK) {
                throw new Exception("Please upload an image.");
            }

            // Check file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($_FILES["image"]["type"], $allowed_types)) {
                throw new Exception("Only JPG and PNG files are allowed.");
            }

            // Save image file
            $target_dir = "../assets/";
            $image_name = time() . "_" . basename($_FILES["image"]["name"]); // Unique file name
            $target_file = $target_dir . $image_name;

            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                throw new Exception("Failed to save uploaded file.");
            }

            // Begin Transaction
            $conn->begin_transaction();

            // Insert into `users` table
            $role = "candidate";
            $query_user = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
            $stmt_user = $conn->prepare($query_user);
            $stmt_user->bind_param("sss", $username, $hashed_password, $role);
            $stmt_user->execute();
            $user_id = $stmt_user->insert_id; // Get last inserted ID

            // Insert into `candidates` table with position field
            $query_candidate = "INSERT INTO candidates (user_id, name, position, credentials, background, platform, grade_level, strand, school_name, email, contact, image) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_candidate = $conn->prepare($query_candidate);
            $stmt_candidate->bind_param("isssssssssss", $user_id, $name, $position, $credentials, $background, $platform, $grade_level, $strand, $school_name, $email, $contact, $image_name);
            $stmt_candidate->execute();

            // Commit transaction
            $conn->commit();

            echo "<script>
                    Swal.fire({
                        title: 'Success!',
                        text: 'Candidate added successfully.',
                        icon: 'success'
                    }).then(() => {
                    window.location.href = 'add_candidate.php';
                    });
                </script>";
        } catch (Exception $e) {
            $conn->rollback(); // Rollback if any error occurs

            // Log the error
            logError("Error: " . $e->getMessage());

            echo "<script>
                    Swal.fire({
                        title: 'Error!',
                        text: 'Error: " . addslashes($e->getMessage()) . "',
                        icon: 'error'
                    }).then(() => {
                        window.history.back();
                    });
                </script>";
        }
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Candidate</title>
        <link rel="stylesheet" href="../assets/styles.css">
        <link rel="stylesheet" href="../assets/admin.css">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link rel="icon" type="image/jgp" href="../logo.jpg">
        <style>
            .container {
                max-width: 900px;
                margin: 80px auto;
                padding: 100px;
                background-color: #fff;
                border-radius: 8px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            }
            
            h2 {
                text-align: center;
                margin-bottom: 25px;
                color: #333;
                border-bottom: 2px solid #ddd;
                padding-bottom: 10px;
            }
            
            form {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                grid-gap: 20px;
            }
            
            textarea, input[type="text"], input[type="email"], input[type="password"], select {
                width: 100%;
                padding: 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 16px;
                transition: border-color 0.3s;
            }
            
            textarea {
                min-height: 120px;
                grid-column: span 2;
            }
            
            input[type="file"] {
                grid-column: span 2;
                padding: 15px;
                background-color: #f8f9fa;
                border: 2px dashed #ddd;
                border-radius: 6px;
                cursor: pointer;
            }
            
            button[type="submit"] {
                grid-column: span 2;
                padding: 14px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 18px;
                cursor: pointer;
                transition: background-color 0.3s;
            }
            
            button[type="submit"]:hover {
                background-color: #0056b3;
            }
            
            .field-group {
                grid-column: span 1;
                display: flex;
                flex-direction: column;

            }
            
            .field-group.full-width {
                grid-column: span 2;
            }
            
            label {
                margin-bottom: 8px;
                font-weight: 600;
                color: #555;
            }
            
            .form-header {
                grid-column: span 2;
                margin-bottom: 5px;
                padding-bottom: 10px;
                border-bottom: 1px solid #eee;
                color: #555;
                font-weight: 600;
            }
            
            @media (max-width: 768px) {
                form {
                    grid-template-columns: 1fr;
                }
                
                .field-group, .field-group.full-width, textarea, input[type="file"], button[type="submit"] {
                    grid-column: span 1;
                }
            }
        </style>
    </head>
    <body>
        <!-- 🔵 Custom Admin Navigation -->
        <header class="admin-header">
            <div class="admin-nav">
                <h1 class="logo">Admin Panel</h1>
                <ul class="nav-links">
                    <li><a href="/admin.php">Home</a></li>
                    <li><a href="/admin/dashboard.php">Dashboard</a></li>
                    <li><a href="../admin/manage_candidates.php">Manage Candidates</a></li>
                    <li><a href="/admin/account_management.php">Account Management</a></li>
                    <li><a href="../pages/logout.php" class="logout-btn">Logout</a></li>
                </ul>
            </div>
        </header>

        <div class="container">
            <h2>Add New Candidate</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-header">Personal Information</div>
                
                <div class="field-group">
                    <label for="name">Full Name:</label>
                    <input type="text" id="name" name="name" placeholder="Enter full name" required>
                </div>
                
                <div class="field-group">
                    <label for="position">Position:</label>
                    <select id="position" name="position" required>
                        <option value="">Select Position</option>
                        <option value="President">President</option>
                        <option value="Vice President">Vice President</option>
                        <option value="Secretary">Secretary</option>
                        <option value="Treasurer">Treasurer</option>
                        <option value="Auditor">Auditor</option>
                        <option value="Public Information Officer">Public Information Officer</option>
                        <option value="Protocol Officer">Protocol Officer</option>
                        <option value="Grade 12 Representative">Grade 12 Representative</option>
                        <option value="Grade 11 Representative">Grade 11 Representative</option>
                    </select>
                </div>
                
                <div class="field-group">
                    <label for="grade_level">Grade Level:</label>
                    <select id="grade_level" name="grade_level" required>
                        <option value="">Select Grade Level</option>
                        <option value="Grade 11">Grade 11</option>
                        <option value="Grade 12">Grade 12</option>
                    </select>
                </div>
                
                <div class="field-group">
                    <label for="strand">Strand:</label>
                    <select id="strand" name="strand" required>
                        <option value="">Select Strand</option>
                        <option value="STEM">STEM (Science, Technology, Engineering, Math)</option>
                        <option value="HUMSS">HUMSS (Humanities & Social Sciences)</option>
                        <option value="ABM">ABM (Accountancy, Business, Management)</option>
                        <option value="TVL">TVL (Technical-Vocational-Livelihood)</option>
                        <option value="Arts & Design">Arts & Design</option>
                        <option value="Sports">Sports</option>
                    </select>
                </div>
                
                <div class="field-group">
                    <label for="school_name">School Name:</label>
                    <input type="text" id="school_name" name="school_name" placeholder="Enter school name" required>
                </div>
                
                <div class="field-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" placeholder="Enter email address" required>
                </div>
                
                <div class="field-group">
                    <label for="contact">Contact Number:</label>
                    <input type="text" id="contact" name="contact" placeholder="Enter contact number" required>
                </div>
                
                <div class="form-header">Candidate Profile</div>
                
                <div class="field-group full-width">
                    <label for="credentials">Credentials:</label>
                    <textarea id="credentials" name="credentials" placeholder="Enter candidate credentials" required></textarea>
                </div>
                
                <div class="field-group full-width">
                    <label for="background">Background:</label>
                    <textarea id="background" name="background" placeholder="Enter candidate background" required></textarea>
                </div>
                
                <div class="field-group full-width">
                    <label for="platform">Platform:</label>
                    <textarea id="platform" name="platform" placeholder="Enter candidate platform" required></textarea>
                </div>
                
                <div class="form-header">Account Information</div>
                
                <div class="field-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" placeholder="5-20 characters (letters, numbers, underscores)" required>
                </div>
                
                <div class="field-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>
                </div>
                
                <div class="field-group full-width">
                    <label for="image">Profile Photo:</label>
                    <input type="file" id="image" name="image" accept="image/png, image/jpeg, image/jpg" required>
                </div>
                
                <button type="submit">Add Candidate</button>
            </form>
        </div>

        <?php if (!empty($statusMessage)): ?>
        <script>
            Swal.fire({
                title: "<?= $statusType === 'success' ? 'Success!' : 'Error!' ?>",
                text: "<?= $statusMessage ?>",
                icon: "<?= $statusType ?>",
                confirmButtonText: "OK"
            }).then(() => {
                if ("<?= $statusType ?>" === "success") {
                    window.location.href = 'manage_candidates.php';
                }
            });
        </script>
        <?php endif; ?>
    </body>
    </html>