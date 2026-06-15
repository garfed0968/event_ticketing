<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Ticketing System</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Local Bootstrap CSS -->
    <link href="theevent-1.0.0/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/custom.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="theevent-1.0.0/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <span style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">EventTicketing</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <?php
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                if (isset($_SESSION['user_id'])) {
                    if ($_SESSION['role'] == 'admin') {
                        echo '<li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Admin Panel</a></li>';
                    } elseif ($_SESSION['role'] == 'organizer') {
                        echo '<li class="nav-item"><a class="nav-link" href="organizer_dashboard.php">Dashboard</a></li>';
                    } else {
                        echo '<li class="nav-item"><a class="nav-link" href="my_tickets.php">My Tickets</a></li>';
                    }
                    echo '<li class="nav-item"><a class="nav-link" href="edit_profile.php">Edit Profile</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>';
                } else {
                    echo '<li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>';
                    echo '<li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>';
                }
                ?>
            </ul>
        </div>
    </div>
</nav>

<div style="margin-top: 80px;"></div> <!-- Spacer for fixed navbar -->
