<?php
$page_title = "Admin Dashboard";
require_once '../includes/header.php';
requireAdminLogin();

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total users
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$stats['total_users'] = $result->fetch_assoc()['count'];

// Total providers
$result = $conn->query("SELECT COUNT(*) as count FROM providers");
$stats['total_providers'] = $result->fetch_assoc()['count'];

// Pending provider approvals
$result = $conn->query("SELECT COUNT(*) as count FROM providers WHERE verification_status = 'pending'");
$stats['pending_providers'] = $result->fetch_assoc()['count'];

// Total services
$result = $conn->query("SELECT COUNT(*) as count FROM services WHERE status = 'active'");
$stats['total_services'] = $result->fetch_assoc()['count'];

// Total bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings");
$stats['total_bookings'] = $result->fetch_assoc()['count'];

// Pending bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'pending'");
$stats['pending_bookings'] = $result->fetch_assoc()['count'];

// Completed bookings
$result = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE booking_status = 'completed'");
$stats['completed_bookings'] = $result->fetch_assoc()['count'];

// Total revenue
$result = $conn->query("SELECT SUM(total_amount) as total FROM bookings WHERE booking_status = 'completed'");
$stats['total_revenue'] = $result->fetch_assoc()['total'] ?? 0;

// Total commission
$result = $conn->query("SELECT SUM(commission_amount) as total FROM commissions");
$stats['total_commission'] = $result->fetch_assoc()['total'] ?? 0;

// Recent bookings
$recent_bookings = $conn->query("
    SELECT b.*, s.service_title, u.full_name as user_name, p.business_name
    FROM bookings b
    JOIN services s ON b.service_id = s.service_id
    JOIN users u ON b.user_id = u.user_id
    JOIN providers p ON b.provider_id = p.provider_id
    ORDER BY b.created_at DESC
    LIMIT 10
");
?>

<div class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="dashboard-sidebar-header">
            <h3>Admin Panel</h3>
        </div>
        <nav class="dashboard-nav">
            <a href="/admin/index.php" class="dashboard-nav-item active">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/admin/users.php" class="dashboard-nav-item">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
            <a href="/admin/providers.php" class="dashboard-nav-item">
                <i class="fas fa-user-tie"></i>
                <span>Providers</span>
                <?php if ($stats['pending_providers'] > 0): ?>
                    <span class="badge badge-warning"><?php echo $stats['pending_providers']; ?></span>
                <?php endif; ?>
            </a>
            <a href="/admin/services.php" class="dashboard-nav-item">
                <i class="fas fa-briefcase"></i>
                <span>Services</span>
            </a>
            <a href="/admin/categories.php" class="dashboard-nav-item">
                <i class="fas fa-tags"></i>
                <span>Categories</span>
            </a>
            <a href="/admin/bookings.php" class="dashboard-nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Bookings</span>
            </a>
            <a href="/admin/commissions.php" class="dashboard-nav-item">
                <i class="fas fa-money-bill-wave"></i>
                <span>Commissions</span>
            </a>
            <a href="/admin/logout.php" class="dashboard-nav-item">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </nav>
    </div>
    
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Admin Dashboard</h1>
            <p>Manage your service marketplace platform</p>
        </div>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_users']; ?></h3>
                    <p>Total Users</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_providers']; ?></h3>
                    <p>Total Providers</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $stats['total_services']; ?></h3>
                    <p>Total Services</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon primary">
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
                    <h3><?php echo format_currency($stats['total_revenue']); ?></h3>
                    <p>Total Revenue</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo format_currency($stats['total_commission']); ?></h3>
                    <p>Admin Commission</p>
                </div>
            </div>
        </div>
        
        <!-- Pending Provider Approvals -->
        <?php if ($stats['pending_providers'] > 0): ?>
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                <h3>Pending Provider Approvals</h3>
                <a href="/admin/providers.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="dashboard-card-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    You have <?php echo $stats['pending_providers']; ?> provider(s) waiting for approval
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Recent Bookings -->
        <div class="table-container">
            <div class="table-header">
                <h2>Recent Bookings</h2>
                <a href="/admin/bookings.php" class="btn btn-primary btn-sm">View All</a>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Provider</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td><?php echo e($booking['service_title']); ?></td>
                                <td><?php echo e($booking['user_name']); ?></td>
                                <td><?php echo e($booking['business_name']); ?></td>
                                <td><?php echo format_date($booking['booking_date']); ?></td>
                                <td><?php echo format_currency($booking['total_amount']); ?></td>
                                <td><?php echo status_badge($booking['booking_status']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
