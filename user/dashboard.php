<?php
$page_title = "Dashboard";
require_once '../includes/header.php';
requireUserLogin();

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get user details
$user = get_user_details($user_id);

// Get booking stats
$stats_query = "
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
        SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
        SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed_bookings
    FROM bookings
    WHERE user_id = ?
";
$stmt = $conn->prepare($stats_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// Get recent bookings
$bookings_query = "
    SELECT b.*, s.service_title, p.business_name,
           (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as service_image
    FROM bookings b
    JOIN services s ON b.service_id = s.service_id
    JOIN providers p ON b.provider_id = p.provider_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
    LIMIT 5
";
$stmt = $conn->prepare($bookings_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_bookings = $stmt->get_result();
?>

<div class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="dashboard-sidebar-header">
            <h3>User Dashboard</h3>
        </div>
        <nav class="dashboard-nav">
            <a href="/user/dashboard.php" class="dashboard-nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/user/search.php" class="dashboard-nav-item">
                <i class="fas fa-search"></i>
                <span>Browse Services</span>
            </a>
            <a href="/user/my-bookings.php" class="dashboard-nav-item">
                <i class="fas fa-calendar-check"></i>
                <span>My Bookings</span>
            </a>
            <a href="/user/profile.php" class="dashboard-nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="/user/logout.php" class="dashboard-nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo e($user['full_name']); ?>!</h1>
            <p>Manage your bookings and profile from here</p>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_bookings']; ?></h3>
                    <p>Total Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['pending_bookings']; ?></h3>
                    <p>Pending Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['confirmed_bookings']; ?></h3>
                    <p>Confirmed Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['completed_bookings']; ?></h3>
                    <p>Completed Bookings</p>
                </div>
            </div>
        </div>
        
        <!-- Recent Bookings -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>Recent Bookings</h3>
                <a href="/user/my-bookings.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="dashboard-card-body">
                <?php if ($recent_bookings->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Service</th>
                                    <th>Provider</th>
                                    <th>Date & Time</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo e($booking['service_title']); ?></td>
                                        <td><?php echo e($booking['business_name']); ?></td>
                                        <td><?php echo format_date($booking['booking_date']) . ' ' . date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                        <td><?php echo format_currency($booking['total_amount']); ?></td>
                                        <td><?php echo status_badge($booking['booking_status']); ?></td>
                                        <td>
                                            <a href="/user/booking-details.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No bookings yet</h3>
                        <p>Start booking services from our marketplace</p>
                        <a href="/user/search.php" class="btn btn-primary mt-2">Browse Services</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="dashboard-card-body">
                <div class="grid grid-cols-3 gap-2">
                    <a href="/user/search.php" class="btn btn-primary">
                        <i class="fas fa-search"></i> Browse Services
                    </a>
                    <a href="/user/my-bookings.php" class="btn btn-secondary">
                        <i class="fas fa-calendar"></i> My Bookings
                    </a>
                    <a href="/user/profile.php" class="btn btn-outline">
                        <i class="fas fa-user"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
