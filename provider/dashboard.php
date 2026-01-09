<?php
$page_title = "Provider Dashboard";
require_once '../includes/header.php';
requireProviderLogin();

$provider_id = $_SESSION['provider_id'];
$conn = getDBConnection();

// Get provider details
$provider = get_provider_details($provider_id);

// Show verification message if pending
if ($provider['verification_status'] !== 'approved') {
    $message = $provider['verification_status'] === 'pending' 
        ? "Your account is pending verification. You'll be able to add services once approved by admin."
        : "Your account has been rejected. Please contact support.";
}

// Get statistics
$stats = [];

// Total services
$result = $conn->query("SELECT COUNT(*) as count FROM services WHERE provider_id = $provider_id");
$stats['total_services'] = $result->fetch_assoc()['count'];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE provider_id = $provider_id");
$stats['total_bookings'] = $result->fetch_assoc()['count'];

// Pending bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE provider_id = $provider_id AND booking_status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['count'];

// Completed bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE provider_id = $provider_id AND booking_status = 'completed'");
$stats['completed_bookings'] = $result->fetch_assoc()['count'];

// Total earnings
$result = $conn->query("SELECT SUM(provider_earnings) as total FROM commissions WHERE provider_id = $provider_id");
$stats['total_earnings'] = $result->fetch_assoc()['total'] ?? 0;

// Pending earnings
$result = $conn->query("SELECT SUM(provider_earnings) as total FROM commissions WHERE provider_id = $provider_id AND payment_status = 'pending'");
$stats['pending_earnings'] = $result->fetch_assoc()['total'] ?? 0;

// Average rating
$stats['avg_rating'] = get_provider_rating($provider_id);

// Recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, s.service_title, u.full_name as user_name, u.phone as user_phone
    FROM bookings b
    JOIN services s ON b.service_id = s.service_id
    JOIN users u ON b.user_id = u.user_id
    WHERE b.provider_id = $provider_id
    ORDER BY b.created_at DESC
    LIMIT 10
");
?>

<div class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="dashboard-sidebar-header">
            <h3>Provider Panel</h3>
        </div>
        <nav class="dashboard-nav">
            <a href="/provider/dashboard.php" class="dashboard-nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/provider/my-services.php" class="dashboard-nav-item">
                <i class="fas fa-briefcase"></i>
                <span>My Services</span>
            </a>
            <?php if ($provider['verification_status'] === 'approved'): ?>
                <a href="/provider/add-service.php" class="dashboard-nav-item">
                    <i class="fas fa-plus"></i>
                    <span>Add Service</span>
                </a>
            <?php endif; ?>
            <a href="/provider/bookings.php" class="dashboard-nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Bookings</span>
                <?php if ($stats['pending_bookings'] > 0): ?>
                    <span class="badge badge-warning"><?php echo $stats['pending_bookings']; ?></span>
                <?php endif; ?>
            </a>
            <a href="/provider/earnings.php" class="dashboard-nav-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Earnings</span>
            </a>
            <a href="/provider/profile.php" class="dashboard-nav-item">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
            <a href="/provider/logout.php" class="dashboard-nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Welcome, <?php echo e($provider['business_name']); ?>!</h1>
            <p>Manage your services and bookings</p>
        </div>
        
        <!-- Verification Status -->
        <?php if ($provider['verification_status'] !== 'approved'): ?>
        <div class="alert alert-<?php echo $provider['verification_status'] === 'pending' ? 'warning' : 'danger'; ?>">
            <i class="fas fa-<?php echo $provider['verification_status'] === 'pending' ? 'clock' : 'times-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_services']; ?></h3>
                    <p>Total Services</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-calendar-check"></i>
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
                    <h3><?php echo $stats['completed_bookings']; ?></h3>
                    <p>Completed Bookings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo format_currency($stats['total_earnings']); ?></h3>
                    <p>Total Earnings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo format_currency($stats['pending_earnings']); ?></h3>
                    <p>Pending Earnings</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-star"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['avg_rating'], 1); ?></h3>
                    <p>Average Rating</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $provider['experience_years']; ?> yrs</h3>
                    <p>Experience</p>
                </div>
            </div>
        </div>
        
        <!-- Pending Bookings Alert -->
        <?php if ($stats['pending_bookings'] > 0): ?>
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>Pending Booking Requests</h3>
                <a href="/provider/bookings.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="dashboard-card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    You have <?php echo $stats['pending_bookings']; ?> booking request(s) waiting for your response
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Bookings -->
        <div class="table-container">
            <div class="table-header">
                <h2>Recent Bookings</h2>
                <a href="/provider/bookings.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Date & Time</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td><?php echo e($booking['service_title']); ?></td>
                                <td>
                                    <?php echo e($booking['user_name']); ?><br>
                                    <small><?php echo e($booking['user_phone']); ?></small>
                                </td>
                                <td><?php echo format_date($booking['booking_date']) . '<br>' . date('g:i A', strtotime($booking['booking_time'])); ?></td>
                                <td><?php echo format_currency($booking['total_amount']); ?></td>
                                <td><?php echo status_badge($booking['booking_status']); ?></td>
                                <td>
                                    <a href="/provider/booking-detail.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary btn-sm">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <?php if ($provider['verification_status'] === 'approved'): ?>
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>Quick Actions</h3>
            </div>
            <div class="dashboard-card-body">
                <div class="grid grid-cols-3 gap-2">
                    <a href="/provider/add-service.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Service
                    </a>
                    <a href="/provider/bookings.php" class="btn btn-secondary">
                        <i class="fas fa-calendar"></i> View Bookings
                    </a>
                    <a href="/provider/earnings.php" class="btn btn-outline">
                        <i class="fas fa-money-bill"></i> View Earnings
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
