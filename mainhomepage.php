<?php
include './includes/db.php';
session_start();

$featuredQuery = "SELECT id, user_id, name, position, image, credentials, background, platform 
                  FROM candidates 
                  WHERE created_at >= NOW() - INTERVAL 7 DAY 
                  ORDER BY created_at DESC 
                  LIMIT 2";
$featuredResult = $conn->query($featuredQuery);
$featuredCandidates = [];

if ($featuredResult && $featuredResult->num_rows > 0) {
    while ($row = $featuredResult->fetch_assoc()) {
        $featuredCandidates[] = $row;
    }
}
// Get candidate IDs from URL
$candidateIds = [];
if (isset($_GET['candidates'])) {
    $candidateIds = explode(',', $_GET['candidates']);
}

// Fetch candidate data
$candidates = [];
if (!empty($candidateIds)) {
    $placeholders = implode(',', array_fill(0, count($candidateIds), '?'));
    
    $stmt = $conn->prepare("
        SELECT c.*, 
               COUNT(DISTINCT cm.id) AS comment_count,
               SUM(CASE WHEN r.reaction_type = 'like' THEN 1 WHEN r.reaction_type = 'dislike' THEN -1 ELSE 0 END) AS reaction_score
        FROM candidates c
        LEFT JOIN comments cm ON c.id = cm.candidate_id
        LEFT JOIN reactions r ON c.id = r.candidate_id
        WHERE c.id IN ($placeholders)
        GROUP BY c.id
    ");
    
    // Bind parameters
    $types = str_repeat('i', count($candidateIds));
    $stmt->bind_param($types, ...$candidateIds);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $candidates[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ElecDesk - Candidate Information System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="./assets/comments_and_notifications.js"> </script>
    <style>
        :root {
            --primary-color: #0066cc;
            --primary-light: #4d94ff;
            --primary-dark: #004e9e;
            --secondary-color: #ff6b35;
            --accent-color: #ffce00;
            --light-bg: #f5f7fa;
            --dark-text: #2d3748;
            --light-text: #718096;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            color: var(--dark-text);
        }
        
        /* Animated Background Header */
        .navbar {
            background: linear-gradient(-45deg, #0066cc, #4d94ff, #0059b3, #1a75ff);
            background-size: 400% 400%;
            animation: gradient 15s ease infinite;
            padding: 15px 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        @keyframes gradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
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
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .btn-logout {
            background-color: rgba(255, 255, 255, 0.2);
            color: white !important;
            border: none;
            padding: 8px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        /* Hero Section with Animated Elements */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 80px 0 120px;
            position: relative;
            overflow: hidden;
        }
        
        .hero-particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            animation: float 15s infinite linear;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }
        
        .hero-title {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 20px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 1s ease;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .hero-subtitle {
            font-size: 18px;
            margin-bottom: 30px;
            opacity: 0.9;
            animation: fadeInUp 1s ease 0.2s both;
        }
        
        .hero-btn {
            animation: fadeInUp 1s ease 0.4s both;
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .hero-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.2);
        }
        
        .hero-image {
            animation: float-image 4s ease-in-out infinite;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }
        
        @keyframes float-image {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .wave-shape {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            z-index: 2;
        }
        
        .wave-shape svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 56px;
            animation: wave 25s linear infinite;
        }
        
        @keyframes wave {
            0% { transform: translateX(0); }
            50% { transform: translateX(-50%); }
            100% { transform: translateX(0); }
        }
        
        .wave-shape .shape-fill {
            fill: #FFFFFF;
        }
        
        /* Search and Categories Section with Hover Effects */
        .search-container {
            background-color: white;
            border-radius: 30px;
            padding: 12px 20px;
            box-shadow: 0 8px 16px rgba(0, 102, 204, 0.1);
            margin: -30px auto 30px;
            max-width: 700px;
            position: relative;
            z-index: 10;
            transition: all 0.3s ease;
        }
        
        .search-container:hover {
            box-shadow: 0 12px 24px rgba(0, 102, 204, 0.15);
            transform: translateY(-3px);
        }
        
        .search-input {
            border: none;
            font-size: 16px;
            width: 100%;
            padding: 8px;
            outline: none;
        }
        
        .categories-container {
            margin: 30px 0;
            text-align: center;
            animation: fadeInUp 1s ease 0.6s both;
        }
        
        .category-badge {
            background-color: white;
            color: var(--primary-dark);
            border: 1px solid var(--primary-light);
            padding: 10px 18px;
            border-radius: 20px;
            margin-right: 10px;
            margin-bottom: 12px;
            display: inline-block;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .category-badge:hover {
            background-color: var(--primary-light);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.2);
        }
        
        .category-badge.active {
            background-color: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.3);
        }
        
        /* Enhanced Candidate Cards with Hover Effects */
        .candidates-section {
            padding: 60px 0;
        }
        
        .section-title {
            position: relative;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            color: var(--primary-dark);
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: var(--accent-color);
            border-radius: 2px;
        }
        
        .candidate-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 1px solid rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        .candidate-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 30px rgba(0, 0, 0, 0.12);
        }
        
        .candidate-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }
        
        .candidate-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMCAwIEwxMDAgMTAwIE0wIDUwIEw1MCAwIE01MCAxMDAgTDEwMCA1MCBNMCAxMDAgTDEwMCAwIiBzdHJva2U9InJnYmEoMjU1LCAyNTUsIDI1NSwgMC4xKSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiAvPjwvc3ZnPg==');
            opacity: 0.2;
        }
        
        .candidate-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
            margin-right: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .candidate-card:hover .candidate-avatar {
            transform: scale(1.1);
        }
        
        .candidate-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .candidate-position {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 500;
        }
        
        .candidate-badge {
            background-color: var(--accent-color);
            color: var(--dark-text);
            font-size: 12px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            margin-left: auto;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }
        
        .candidate-body {
            padding: 25px;
        }
        
        .candidate-info-list {
            list-style: none;
            padding: 0;
            margin-bottom: 25px;
        }
        
        .candidate-info-item {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .candidate-info-item:hover {
            background-color: rgba(0, 102, 204, 0.02);
            padding-left: 5px;
            border-bottom-color: var(--primary-light);
        }
        
        .candidate-info-label {
            font-weight: 600;
            width: 120px;
            color: var(--primary-dark);
        }
        
        .candidate-card .btn {
            padding: 10px 15px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .candidate-card .btn-outline-primary {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }
        
        .candidate-card .btn-outline-primary:hover {
            background-color: var(--primary-color);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 102, 204, 0.2);
        }
        
        .candidate-card .btn-outline-secondary {
            border-color: var(--light-text);
            color: var(--light-text);
        }
        
        .candidate-card .btn-outline-secondary:hover {
            background-color: var(--light-text);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Stats Section with Animated Counters */
        .stats-section {
            background: linear-gradient(135deg, var(--primary-dark), #003366);
            color: white;
            padding: 80px 0;
            position: relative;
            overflow: hidden;
        }
        
        .stats-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cGF0aCBkPSJNMCAwIEwxMDAgMTAwIE0wIDUwIEw1MCAwIE01MCAxMDAgTDEwMCA1MCBNMCAxMDAgTDEwMCAwIiBzdHJva2U9InJnYmEoMjU1LCAyNTUsIDI1NSwgMC4xKSIgc3Ryb2tlLXdpZHRoPSIxIiBmaWxsPSJub25lIiAvPjwvc3ZnPg==');
            opacity: 0.1;
        }
        
        .stat-item {
            text-align: center;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            height: 100%;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .stat-icon {
            font-size: 36px;
            margin-bottom: 20px;
            color: var(--accent-color);
        }
        
        .stat-number {
            font-size: 48px;
            font-weight: 700;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }
        
        .stat-label {
            font-size: 16px;
            opacity: 0.9;
            font-weight: 500;
        }
        
        /* Call To Action Section */
        .cta-section {
            padding: 80px 0;
            background-color: var(--light-bg);
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .cta-section::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMTAwIDEwMCIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZGVmcz48bGluZWFyR3JhZGllbnQgaWQ9ImdyYWQiIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iMCIgeTE9IjAiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+PHN0b3Agb2Zmc2V0PSIwJSIgc3RvcC1jb2xvcj0iI2YzZjVmOCIgc3RvcC1vcGFjaXR5PSIwLjEiLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0b3AtY29sb3I9IiNlNmViZjUiIHN0b3Atb3BhY2l0eT0iMC40Ii8+PC9saW5lYXJHcmFkaWVudD48L2RlZnM+PHBhdGggZmlsbD0idXJsKCNncmFkKSIgZD0iTTAgMCBMMTAwIDAgTDEwMCAxMDAgTDAgMTAwIFoiPjwvcGF0aD48L3N2Zz4=');
            opacity: 0.5;
        }
        
        .cta-content {
            position: relative;
            z-index: 1;
        }
        
        .cta-title {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--primary-dark);
        }
        
        .cta-text {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto 30px;
            color: var(--light-text);
        }
        
        .cta-btn {
            padding: 12px 24px;
            font-weight: 600;
            margin: 0 10px 10px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .cta-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        /* Floating Comparison Tray */
        .comparison-tray {
            background-color: white;
            box-shadow: 0 -5px 25px rgba(0, 0, 0, 0.1);
            border-top: 4px solid var(--primary-color);
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
            transform: translateY(100%);
            opacity: 0;
            z-index: 1000;
        }
        
        .comparison-tray.active {
            transform: translateY(0);
            opacity: 1;
        }
        
        .comparison-candidates-container {
            min-height: 50px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .empty-message {
            color: var(--light-text);
            font-style: italic;
        }
        
        .candidate-item {
            background-color: #f8f9fa;
            transition: all 0.3s ease;
        }
        
        .candidate-item:hover {
            background-color: #e9ecef;
            transform: translateY(-2px);
        }
        
        .remove-candidate {
            background: none;
            border: none;
            padding: 0;
            line-height: 1;
        }
        
        .compare-now-btn, .clear-comparison-btn {
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .compare-now-btn:hover, .clear-comparison-btn:hover {
            transform: translateY(-2px);
        }
        
        /* Enhanced Footer */
        footer {
            background-color: #2c3e50;
            color: white;
            padding: 60px 0 20px;
            position: relative;
        }
        
        footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color), var(--accent-color));
        }
        
        .footer-logo {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .footer-logo img {
            margin-right: 12px;
            filter: brightness(1.2);
        }
        
        .footer-links {
            list-style: none;
            padding: 0;
        }
        
        .footer-links li {
            margin-bottom: 12px;
            transition: all 0.3s ease;
        }
        
        .footer-links li:hover {
            transform: translateX(5px);
        }
        
        .footer-links a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
            position: relative;
        }
        
        .footer-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: white;
            transition: all 0.3s ease;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .footer-links a:hover::after {
            width: 100%;
        }
        
        .social-links {
            display: flex;
            margin-top: 20px;
        }
        
        .social-links a {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(0, 0, 0, 0.2);
        }
        
        .newsletter-input {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border: none;
            padding: 12px;
        }
        
        .subscribe-btn {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            padding: 12px 20px;
        }
        
        .subscribe-btn:hover {
            background-color: #ff5722;
            border-color: #ff5722;
        }
        
        .copyright {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
            opacity: 0.7;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
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

    <!-- Hero Section with Particles<!-- Hero Section with Particles -->
    <section class="hero-section">
        <div class="hero-particles" id="particles-js">
            <!-- Particles will be added here via JS -->
        </div>
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="hero-title">Find & Compare Electoral Candidates</h1>
                    <p class="hero-subtitle">Access comprehensive information about candidates, their platforms, and compare their positions on key issues to make informed voting decisions.</p>
                    <div class="d-flex flex-wrap">
                        <a href="#candidates" class="btn btn-light hero-btn me-3 mb-3"><i class="fas fa-search me-2"></i>Explore Candidates</a>
                       
                    </div>
                </div>
               
            </div>
        </div>
        <div class="wave-shape">
            <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
            </svg>
        </div>
    </section>

    <!-- Search Section -->
    <div class="container">
        <div class="search-container">
            <div class="input-group">
                <span class="input-group-text bg-transparent border-0"><i class="fas fa-search text-primary"></i></span>
                <input type="text" class="search-input" placeholder="Search candidates by name, position, or platform...">
                <button class="btn btn-primary" type="button">Search</button>
            </div>
        </div>
        
        <div class="categories-container">
            <span class="category-badge active">All posistion</span>
            <span class="category-badge">Vice President</span>
            <span class="category-badge">Secretary  </span>
            <span class="category-badge">Treasurer</span>
            <span class="category-badge">Auditor</span>
            <span class="category-badge">Public Information </span>
            <span class="category-badge">Officer</span>
            <span class="category-badge">Protocol Officer</span>
            <span class="category-badge">Grade 12 Representative</span>
            <span class="category-badge">Grade 11 Representative</span>
        </div>
    </div>

    <!-- Candidates Section -->
    <section class="candidates-section" id="candidates">
        <div class="container">
            <h2 class="section-title">Featured Candidates</h2>
            
            <div class="row">
                <!-- Candidate Card 1 -->
                <div class="col-lg-4 col-md-6">
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <img src="https://via.placeholder.com/150" alt="John Smith" class="candidate-avatar">
                            <div>
                                <h3 class="candidate-name">John Smith</h3>
                                <p class="candidate-position">Senate Candidate</p>
                            </div>
                            <span class="candidate-badge">Federal</span>
                        </div>
                        <div class="candidate-body">
                            <ul class="candidate-info-list">
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Party:</span>
                                    <span>Democratic Party</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Experience:</span>
                                    <span>10+ years in public service</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Key Issues:</span>
                                    <span>Healthcare, Education, Climate</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Campaign Status:</span>
                                    <span>Active</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary"><i class="fas fa-info-circle me-2"></i>View Profile</button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Candidate Card 2 -->
                <div class="col-lg-4 col-md-6">
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <img src="https://via.placeholder.com/150" alt="Sarah Johnson" class="candidate-avatar">
                            <div>
                                <h3 class="candidate-name">Sarah Johnson</h3>
                                <p class="candidate-position">Governor Candidate</p>
                            </div>
                            <span class="candidate-badge">State</span>
                        </div>
                        <div class="candidate-body">
                            <ul class="candidate-info-list">
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Party:</span>
                                    <span>Republican Party</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Experience:</span>
                                    <span>Former State Senator</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Key Issues:</span>
                                    <span>Economy, Jobs, Infrastructure</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Campaign Status:</span>
                                    <span>Active</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary"><i class="fas fa-info-circle me-2"></i>View Profile</button>
                
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Candidate Card 3 -->
                <div class="col-lg-4 col-md-6">
                    <div class="candidate-card">
                        <div class="candidate-header">
                            <img src="https://via.placeholder.com/150" alt="Michael Chen" class="candidate-avatar">
                            <div>
                                <h3 class="candidate-name">Michael Chen</h3>
                                <p class="candidate-position">City Council</p>
                            </div>
                            <span class="candidate-badge">Local</span>
                        </div>
                        <div class="candidate-body">
                            <ul class="candidate-info-list">
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Party:</span>
                                    <span>Independent</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Experience:</span>
                                    <span>Community Organizer, Educator</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Key Issues:</span>
                                    <span>Housing, Transport, Safety</span>
                                </li>
                                <li class="candidate-info-item">
                                    <span class="candidate-info-label">Campaign Status:</span>
                                    <span>Active</span>
                                </li>
                            </ul>
                            <div class="d-flex justify-content-between">
                                <button class="btn btn-outline-primary"><i class="fas fa-info-circle me-2"></i>View Profile</button>
                             
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="text-center mt-4">
                <a href="./index.php" class="btn btn-primary"><i class="fas fa-users me-2"></i>View All Candidates</a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-number" data-count="350">350</div>
                        <div class="stat-label">Registered Candidates</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="stat-number" data-count="52">52</div>
                        <div class="stat-label">Register User</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-newspaper"></i>
                        </div>
                        <div class="stat-number" data-count="1200">1,200</div>
                        <div class="stat-label">Platform Positions</div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="stat-item">
                        <div class="stat-icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="stat-number" data-count="25000">25,000</div>
                        <div class="stat-label">Monthly Visitors</div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>

<!-- CTA Section -->
<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Discover Local Candidate Profiles</h2>
            <p class="cta-text">Access comprehensive information about political candidates, their platforms, and upcoming public appearances in your area.</p>
            <div class="d-flex justify-content-center flex-wrap">
                <a href="#" class="btn btn-primary cta-btn"><i class="fas fa-user me-2"></i>Browse Candidates</a>
                <a href="#" class="btn btn-outline-primary cta-btn"><i class="fas fa-thumbs-up me-2"></i>Recommend Candidates</a>
            </div>
        </div>
    </div>
</section>


    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="footer-logo">
                        <img src="https://via.placeholder.com/40" alt="ElecDesk Logo" width="40" height="40" class="rounded-circle">
                        <h4 class="mb-0 ms-2">ElecDesk</h4>
                    </div>
                    <p>A comprehensive platform dedicated to providing accurate, unbiased information about electoral candidates to help voters make informed decisions.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-4">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">Candidates</a></li>
                        <li><a href="#">Elections</a></li>
                    
                        <li><a href="#">About Us</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-4">Resources</h5>
                    <ul class="footer-links">
                        <li><a href="#">Voter Guide</a></li>
                        <li><a href="#">Election Calendar</a></li>
                        <li><a href="#">Voting Requirements</a></li>
                        <li><a href="#">Polling Locations</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>
                <div class="col-lg-4 col-md-4">
                    <h5 class="mb-4">Newsletter</h5>
                    <p>Subscribe to our newsletter for election updates, candidate news, and voter resources.</p>
                   
                </div>
            </div>
            <div class="copyright">
                <p>&copy; 2025 ElecDesk. All rights reserved. | <a href="#" class="text-white">Privacy Policy</a> | <a href="#" class="text-white">Terms of Service</a></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/particles.js/2.0.0/particles.min.js"></script>
    <script>
        // Initialize the particle.js for background effects
        document.addEventListener("DOMContentLoaded", function() {
            // Create particles
            if(document.getElementById('particles-js')) {
                for(let i = 0; i < 30; i++) {
                    let particle = document.createElement('div');
                    particle.className = 'particle';
                    particle.style.width = Math.random() * 10 + 2 + 'px';
                    particle.style.height = particle.style.width;
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animationDelay = Math.random() * 5 + 's';
                    particle.style.animationDuration = Math.random() * 10 + 10 + 's';
                    document.querySelector('.hero-particles').appendChild(particle);
                }
            }
            
          
        
            // Count animation for stats section
            function animateStats() {
                const stats = document.querySelectorAll('.stat-number');
                stats.forEach(stat => {
                    const target = parseInt(stat.getAttribute('data-count'));
                    const duration = 2000; // ms
                    const step = target / 200;
                    let current = 0;
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            clearInterval(timer);
                            stat.textContent = target.toLocaleString();
                        } else {
                            stat.textContent = Math.floor(current).toLocaleString();
                        }
                    }, 10);
                });
            }
            
            // Intersection Observer for stats animation
            const statsSection = document.querySelector('.stats-section');
            if (statsSection) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            animateStats();
                            observer.unobserve(entry.target);
                        }
                    });
                }, {threshold: 0.1});
                
                observer.observe(statsSection);
            }
        });
    </script>
</body>
</html>