<?php
// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>10Tables - <?php echo $page_title ?? 'Home'; ?></title>
    <link rel="stylesheet" href="/food_ordering_system/assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="logo">
                    <h1>10Tables</h1>
                </div>
                <ul class="nav-menu">
                    <li><a href="home.php">Home</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- User Profile Section -->
                        <li class="user-profile-header">
                            <div class="user-avatar-container">
                                <?php if(!empty($_SESSION['profile_image'])): ?>
                                    <img src="<?php echo UPLOAD_URL . $_SESSION['profile_image']; ?>" 
                                         alt="<?php echo htmlspecialchars($_SESSION['username']); ?>"
                                         class="user-avatar">
                                <?php else: ?>
                                    <div class="user-avatar default-avatar">
                                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="username-display">
                                <?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']); ?>
                            </span>
                        </li>
                        
                        <li><a href="dashboard.php">Dashboard</a></li>
                        <li><a href="profile.php">Profile</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main>