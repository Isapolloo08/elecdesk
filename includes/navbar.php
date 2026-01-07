<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<head>
<link rel="stylesheet" href="../assets/admin_header.css"> <!-- Link to CSS file -->
</head>
<nav class="navbar">
    <header>
    <ul>
        <li><a href="/index.php">Home</a></li>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="/admin/dashboard.php">Analytics</a></li>
        <?php endif; ?>
        
        <li><a href="/pages/candidate.php">Candidates</a></li>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="/admin/account_management.php">Account Management</a></li>
        <?php endif; ?>

        <li><a href="/pages/logout.php">Logout</a></li>
    </ul>
    </header>
</nav>
