<?php
$page_title = "My Services";
require_once '../includes/header.php';
requireProviderLogin();

$provider_id = $_SESSION['provider_id'];
$conn = getDBConnection();

// Handle delete
if (isset($_GET['delete'])) {
    $service_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM services WHERE service_id = ? AND provider_id = ?");
    $stmt->bind_param("ii", $service_id, $provider_id);
    $stmt->execute();
    setFlashMessage("Service deleted successfully", "success");
    redirect('/provider/my-services.php');
}

// Get services
$services = $conn->query("
    SELECT s.*, c.category_name,
           (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as image,
           (SELECT AVG(rating) FROM reviews WHERE service_id = s.service_id) as avg_rating,
           (SELECT COUNT(*) FROM bookings WHERE service_id = s.service_id) as booking_count
    FROM services s
    JOIN service_categories c ON s.category_id = c.category_id
    WHERE s.provider_id = $provider_id
    ORDER BY s.created_at DESC
");
?>

<div class="dashboard-container">
    <div class="dashboard-sidebar">
        <div class="dashboard-sidebar-header">
            <h3>Provider Panel</h3>
        </div>
        <nav class="dashboard-nav">
            <a href="/provider/dashboard.php" class="dashboard-nav-item">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
            <a href="/provider/my-services.php" class="dashboard-nav-item active">
                <i class="fas fa-briefcase"></i>
                <span>My Services</span>
            </a>
            <a href="/provider/add-service.php" class="dashboard-nav-item">
                <i class="fas fa-plus"></i>
                <span>Add Service</span>
            </a>
            <a href="/provider/bookings.php" class="dashboard-nav-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Bookings</span>
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
            <h1>My Services</h1>
            <p>Manage your service listings</p>
        </div>
        
        <div class="mb-3">
            <a href="/provider/add-service.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Service
            </a>
        </div>
        
        <?php if ($services->num_rows > 0): ?>
            <div class="grid grid-cols-2">
                <?php while ($service = $services->fetch_assoc()): ?>
                    <div class="card">
                        <img src="<?php echo $service['image'] ? '/uploads/services/' . e($service['image']) : '/assets/images/placeholder.png'; ?>" 
                             alt="<?php echo e($service['service_title']); ?>" 
                             class="card-img">
                        <div class="card-body">
                            <div class="d-flex justify-between align-center mb-2">
                                <div class="badge badge-info"><?php echo e($service['category_name']); ?></div>
                                <?php echo status_badge($service['status']); ?>
                            </div>
                            
                            <h3 class="card-title"><?php echo e($service['service_title']); ?></h3>
                            <p class="card-text"><?php echo e(substr($service['description'], 0, 100)); ?>...</p>
                            
                            <div class="service-info mb-2">
                                <div class="service-info-item">
                                    <i class="fas fa-dollar-sign"></i>
                                    <span><?php echo format_currency($service['price']); ?></span>
                                </div>
                                <div class="service-info-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $service['duration']; ?> mins</span>
                                </div>
                                <div class="service-info-item">
                                    <i class="fas fa-calendar-check"></i>
                                    <span><?php echo $service['booking_count']; ?> bookings</span>
                                </div>
                                <?php if ($service['avg_rating']): ?>
                                    <div class="service-info-item">
                                        <i class="fas fa-star" style="color: var(--accent-color);"></i>
                                        <span><?php echo number_format($service['avg_rating'], 1); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="d-flex gap-2">
                                <a href="/user/service-detail.php?id=<?php echo $service['service_id']; ?>" 
                                   class="btn btn-primary btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/provider/edit-service.php?id=<?php echo $service['service_id']; ?>" 
                                   class="btn btn-secondary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <button onclick="deleteItem('?delete=<?php echo $service['service_id']; ?>', 'this service')" 
                                        class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-briefcase"></i>
                <h3>No services yet</h3>
                <p>Add your first service to start receiving bookings</p>
                <a href="/provider/add-service.php" class="btn btn-primary mt-2">
                    <i class="fas fa-plus"></i> Add Service
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
