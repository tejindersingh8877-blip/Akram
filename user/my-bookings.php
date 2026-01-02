<?php
$page_title = "My Bookings";
require_once '../includes/header.php';
requireUserLogin();

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = "b.user_id = $user_id";
if ($filter === 'pending') $where .= " AND b.booking_status = 'pending'";
elseif ($filter === 'confirmed') $where .= " AND b.booking_status = 'confirmed'";
elseif ($filter === 'completed') $where .= " AND b.booking_status = 'completed'";
elseif ($filter === 'cancelled') $where .= " AND b.booking_status = 'cancelled'";

$bookings = $conn->query("
    SELECT b.*, s.service_title, p.business_name, p.phone as provider_phone,
           (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as service_image,
           (SELECT review_id FROM reviews WHERE booking_id = b.booking_id) as has_review
    FROM bookings b
    JOIN services s ON b.service_id = s.service_id
    JOIN providers p ON b.provider_id = p.provider_id
    WHERE $where
    ORDER BY b.created_at DESC
");
?>

<div class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="dashboard-sidebar-header">
            <h3>User Dashboard</h3>
        </div>
        <nav class="dashboard-nav">
            <a href="/user/dashboard.php" class="dashboard-nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/user/search.php" class="dashboard-nav-item">
                <i class="fas fa-search"></i>
                <span>Browse Services</span>
            </a>
            <a href="/user/my-bookings.php" class="dashboard-nav-item active">
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
            <h1>My Bookings</h1>
            <p>View and manage your service bookings</p>
        </div>
        
        <!-- Filter -->
        <div class="filter-bar">
            <div class="d-flex gap-2">
                <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                    All Bookings
                </a>
                <a href="?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-warning' : 'btn-outline'; ?>">
                    Pending
                </a>
                <a href="?filter=confirmed" class="btn <?php echo $filter === 'confirmed' ? 'btn-info' : 'btn-outline'; ?>">
                    Confirmed
                </a>
                <a href="?filter=completed" class="btn <?php echo $filter === 'completed' ? 'btn-success' : 'btn-outline'; ?>">
                    Completed
                </a>
                <a href="?filter=cancelled" class="btn <?php echo $filter === 'cancelled' ? 'btn-danger' : 'btn-outline'; ?>">
                    Cancelled
                </a>
            </div>
        </div>
        
        <!-- Bookings List -->
        <?php if ($bookings->num_rows > 0): ?>
            <div class="grid grid-cols-1">
                <?php while ($booking = $bookings->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-between align-center mb-3">
                                <h3><?php echo e($booking['service_title']); ?></h3>
                                <?php echo status_badge($booking['booking_status']); ?>
                            </div>
                            
                            <div class="grid grid-cols-2" style="gap: 2rem;">
                                <div>
                                    <img src="<?php echo $booking['service_image'] ? '/uploads/services/' . e($booking['service_image']) : '/assets/images/placeholder.png'; ?>" 
                                         alt="Service"
                                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                                </div>
                                
                                <div>
                                    <div class="service-info">
                                        <div class="service-info-item">
                                            <i class="fas fa-building"></i>
                                            <span><strong>Provider:</strong> <?php echo e($booking['business_name']); ?></span>
                                        </div>
                                        <div class="service-info-item">
                                            <i class="fas fa-phone"></i>
                                            <span><strong>Phone:</strong> <?php echo e($booking['provider_phone']); ?></span>
                                        </div>
                                        <div class="service-info-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><strong>Date:</strong> <?php echo format_date($booking['booking_date']); ?></span>
                                        </div>
                                        <div class="service-info-item">
                                            <i class="fas fa-clock"></i>
                                            <span><strong>Time:</strong> <?php echo date('g:i A', strtotime($booking['booking_time'])); ?></span>
                                        </div>
                                        <div class="service-info-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><strong>Address:</strong> <?php echo e($booking['service_address']); ?></span>
                                        </div>
                                        <div class="service-info-item">
                                            <i class="fas fa-dollar-sign"></i>
                                            <span><strong>Amount:</strong> <?php echo format_currency($booking['total_amount']); ?></span>
                                        </div>
                                    </div>
                                    
                                    <?php if (!empty($booking['special_notes'])): ?>
                                        <div class="mt-2">
                                            <strong>Special Notes:</strong>
                                            <p style="color: var(--text-light);"><?php echo e($booking['special_notes']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="mt-3 d-flex gap-2">
                                        <?php if ($booking['booking_status'] === 'completed' && !$booking['has_review']): ?>
                                            <a href="/user/review.php?booking_id=<?php echo $booking['booking_id']; ?>" 
                                               class="btn btn-primary btn-sm">
                                                <i class="fas fa-star"></i> Write Review
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($booking['booking_status'] === 'pending'): ?>
                                            <button onclick="confirmAction('Cancel this booking?', function() { 
                                                window.location.href='cancel-booking.php?id=<?php echo $booking['booking_id']; ?>';
                                            })" class="btn btn-danger btn-sm">
                                                <i class="fas fa-times"></i> Cancel Booking
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="/user/booking-details.php?id=<?php echo $booking['booking_id']; ?>" 
                                           class="btn btn-outline btn-sm">
                                            <i class="fas fa-eye"></i> View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No bookings found</h3>
                <p>Start booking services from our marketplace</p>
                <a href="/user/search.php" class="btn btn-primary mt-2">Browse Services</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
