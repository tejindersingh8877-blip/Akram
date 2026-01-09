<?php
$page_title = "Service Details";
require_once '../includes/header.php';

$service_id = intval($_GET['id'] ?? 0);

if ($service_id === 0) {
    redirect('/user/search.php');
}

$service = get_service_details($service_id);

if (!$service) {
    setFlashMessage("Service not found", "error");
    redirect('/user/search.php');
}

$conn = getDBConnection();

// Get service images
$images_query = "SELECT * FROM service_images WHERE service_id = ? ORDER BY is_primary DESC";
$stmt = $conn->prepare($images_query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$images = $stmt->get_result();

// Get provider details
$provider = get_provider_details($service['provider_id']);

// Get reviews
$reviews_query = "
    SELECT r.*, u.full_name
    FROM reviews r
    JOIN users u ON r.user_id = u.user_id
    WHERE r.service_id = ?
    ORDER BY r.created_at DESC
    LIMIT 10
";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $service_id);
$stmt->execute();
$reviews = $stmt->get_result();
?>

<div class="container" style="padding: 2rem 20px;">
    <a href="/user/search.php" class="btn btn-outline mb-3">
        <i class="fas fa-arrow-left"></i> Back to Search
    </a>
    
    <div class="grid grid-cols-2" style="gap: 3rem;">
        <!-- Service Images -->
        <div>
            <div class="card">
                <img src="<?php echo $service['primary_image'] ? '/uploads/services/' . e($service['primary_image']) : '/assets/images/placeholder.png'; ?>" 
                     alt="<?php echo e($service['service_title']); ?>" 
                     style="width: 100%; height: 400px; object-fit: cover; border-radius: 12px;">
            </div>
            
            <?php if ($images->num_rows > 1): ?>
                <div class="image-preview-container mt-2">
                    <?php while ($img = $images->fetch_assoc()): ?>
                        <img src="/uploads/services/<?php echo e($img['image_path']); ?>" 
                             alt="Service image"
                             style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px; cursor: pointer;">
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Service Info -->
        <div>
            <div class="badge badge-info mb-2"><?php echo e($service['category_name']); ?></div>
            <h1><?php echo e($service['service_title']); ?></h1>
            
            <?php if ($service['avg_rating']): ?>
                <div class="d-flex align-center gap-2 mb-3">
                    <div class="rating" style="font-size: 1.25rem;">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star<?php echo $i <= round($service['avg_rating']) ? '' : '-o'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span style="font-size: 1.125rem; font-weight: 500;">
                        <?php echo number_format($service['avg_rating'], 1); ?> (<?php echo $service['review_count']; ?> reviews)
                    </span>
                </div>
            <?php endif; ?>
            
            <div class="price mb-3" style="font-size: 2rem;">
                <?php echo format_currency($service['price']); ?>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h3>Service Description</h3>
                    <p><?php echo nl2br(e($service['description'])); ?></p>
                </div>
            </div>
            
            <div class="card mb-3">
                <div class="card-body">
                    <h3>Service Details</h3>
                    <div class="service-info">
                        <div class="service-info-item">
                            <i class="fas fa-clock"></i>
                            <span>Duration: <?php echo $service['duration']; ?> minutes</span>
                        </div>
                        <div class="service-info-item">
                            <i class="fas fa-user"></i>
                            <span>Provider: <?php echo e($service['business_name']); ?></span>
                        </div>
                        <div class="service-info-item">
                            <i class="fas fa-star"></i>
                            <span>Provider Rating: <?php echo number_format(get_provider_rating($service['provider_id']), 1); ?></span>
                        </div>
                        <div class="service-info-item">
                            <i class="fas fa-briefcase"></i>
                            <span>Experience: <?php echo $provider['experience_years']; ?> years</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (isUser()): ?>
                <a href="/user/booking.php?service_id=<?php echo $service_id; ?>" class="btn btn-primary btn-block" style="font-size: 1.125rem;">
                    <i class="fas fa-calendar-check"></i> Book This Service
                </a>
            <?php else: ?>
                <div class="alert alert-info">
                    Please <a href="/user/login.php">login</a> to book this service
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Reviews Section -->
    <div class="section">
        <h2 class="section-title">Customer Reviews</h2>
        
        <?php if ($reviews->num_rows > 0): ?>
            <div class="grid grid-cols-2">
                <?php while ($review = $reviews->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-between align-center mb-2">
                                <h4><?php echo e($review['full_name']); ?></h4>
                                <div class="rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?php echo $i <= $review['rating'] ? '' : '-o'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p><?php echo e($review['review_text']); ?></p>
                            <small style="color: var(--text-light);">
                                <?php echo format_date($review['created_at']); ?>
                            </small>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-comments"></i>
                <h3>No reviews yet</h3>
                <p>Be the first to review this service</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
