<?php
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>ProFix Masters</title>
    <meta name="description" content="ProFix Masters - Your trusted platform for booking local service providers">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <a href="/index.php">
                    <h1 class="logo">ProFix Masters</h1>
                </a>
            </div>
            <button class="navbar-toggler" id="navbarToggler">
                <i class="fas fa-bars"></i>
            </button>
            <div class="navbar-menu" id="navbarMenu">
                <ul class="navbar-nav">
                    <li><a href="/index.php" class="<?php echo $current_page === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="/user/search.php">Services</a></li>
                    <li><a href="/about.php">About</a></li>
                    <li><a href="/contact.php">Contact</a></li>
                    
                    <?php if (isUser()): ?>
                        <li><a href="/user/dashboard.php">Dashboard</a></li>
                        <li><a href="/user/my-bookings.php">My Bookings</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-circle"></i> <?php echo e($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/user/profile.php">Profile</a></li>
                                <li><a href="/user/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php elseif (isProvider()): ?>
                        <li><a href="/provider/dashboard.php">Dashboard</a></li>
                        <li><a href="/provider/my-services.php">My Services</a></li>
                        <li><a href="/provider/bookings.php">Bookings</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-circle"></i> <?php echo e($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/provider/profile.php">Profile</a></li>
                                <li><a href="/provider/earnings.php">Earnings</a></li>
                                <li><a href="/provider/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php elseif (isAdmin()): ?>
                        <li><a href="/admin/index.php">Admin Panel</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle">
                                <i class="fas fa-user-shield"></i> <?php echo e($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a href="/admin/settings.php">Settings</a></li>
                                <li><a href="/admin/logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li><a href="/user/login.php" class="btn btn-outline">Login</a></li>
                        <li><a href="/user/register.php" class="btn btn-primary">Sign Up</a></li>
                        <li><a href="/provider/register.php" class="btn btn-secondary">Become a Provider</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <?php
    // Display flash messages
    $flash = getFlashMessage();
    if ($flash):
    ?>
    <div class="alert alert-<?php echo e($flash['type']); ?> alert-dismissible">
        <div class="container">
            <?php echo e($flash['message']); ?>
            <button type="button" class="alert-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
    <?php endif; ?>

    <main class="main-content">
