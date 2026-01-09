<?php
$page_title = "Home";
require_once 'includes/header.php';

// Fetch featured service categories
$conn = getDBConnection();
$categories_query = "SELECT * FROM service_categories WHERE status = 'active' LIMIT 8";
$categories_result = $conn->query($categories_query);

// Fetch featured services
$services_query = "
    SELECT s.*, p.business_name, c.category_name,
           (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as image,
           (SELECT AVG(rating) FROM reviews WHERE service_id = s.service_id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE service_id = s.service_id) as review_count
    FROM services s
    JOIN providers p ON s.provider_id = p.provider_id
    JOIN service_categories c ON s.category_id = c.category_id
    WHERE s.status = 'active' AND p.verification_status = 'approved'
    ORDER BY s.created_at DESC
    LIMIT 6
";
$services_result = $conn->query($services_query);
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container">
        <h1>Find the Best Local Service Providers</h1>
        <p>Book trusted professionals for all your home and business needs</p>
        
        <form action="/user/search.php" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search for services..." required>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>
        </form>
    </div>
</section>

<!-- Service Categories Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">Popular Service Categories</h2>
        <p class="section-subtitle">Choose from our wide range of service categories</p>
        
        <div class="category-grid">
            <?php while ($category = $categories_result->fetch_assoc()): ?>
                <a href="/user/search.php?category=<?php echo $category['category_id']; ?>" class="category-card">
                    <i class="fas <?php echo e($category['category_icon']); ?>"></i>
                    <h3><?php echo e($category['category_name']); ?></h3>
                </a>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- Featured Services Section -->
<?php if ($services_result->num_rows > 0): ?>
<section class="section" style="background-color: white;">
    <div class="container">
        <h2 class="section-title">Featured Services</h2>
        <p class="section-subtitle">Discover our top-rated services</p>
        
        <div class="grid grid-cols-3">
            <?php while ($service = $services_result->fetch_assoc()): ?>
                <div class="card">
                    <img src="<?php echo $service['image'] ? '/uploads/services/' . e($service['image']) : '/assets/images/placeholder.png'; ?>" 
                         alt="<?php echo e($service['service_title']); ?>" 
                         class="card-img">
                    <div class="card-body">
                        <div class="badge badge-info mb-2"><?php echo e($service['category_name']); ?></div>
                        <h3 class="card-title"><?php echo e($service['service_title']); ?></h3>
                        <p class="card-text"><?php echo e(substr($service['description'], 0, 100)); ?>...</p>
                        
                        <div class="service-info mb-2">
                            <div class="service-info-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo e($service['business_name']); ?></span>
                            </div>
                            <div class="service-info-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $service['duration']; ?> mins</span>
                            </div>
                            <?php if ($service['avg_rating']): ?>
                                <div class="service-info-item">
                                    <div class="rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star<?php echo $i <= round($service['avg_rating']) ? '' : '-o'; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span>(<?php echo $service['review_count']; ?>)</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="d-flex justify-between align-center">
                            <span class="price"><?php echo format_currency($service['price']); ?></span>
                            <a href="/user/service-detail.php?id=<?php echo $service['service_id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <div class="text-center mt-4">
            <a href="/user/search.php" class="btn btn-primary">Browse All Services</a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- How It Works Section -->
<section class="section">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <p class="section-subtitle">Get started in just 3 simple steps</p>
        
        <div class="grid grid-cols-3">
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 4rem; color: var(--primary-color); margin-bottom: 1rem;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3 class="card-title">1. Search Services</h3>
                    <p class="card-text">Browse through our wide range of services and find the perfect provider for your needs.</p>
                </div>
            </div>
            
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 4rem; color: var(--secondary-color); margin-bottom: 1rem;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="card-title">2. Book Service</h3>
                    <p class="card-text">Select your preferred date and time, provide details, and confirm your booking instantly.</p>
                </div>
            </div>
            
            <div class="card text-center">
                <div class="card-body">
                    <div style="font-size: 4rem; color: var(--accent-color); margin-bottom: 1rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h3 class="card-title">3. Get Service</h3>
                    <p class="card-text">Enjoy professional service from verified providers and rate your experience.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="section" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white;">
    <div class="container text-center">
        <h2 style="color: white; margin-bottom: 1rem;">Ready to Get Started?</h2>
        <p style="font-size: 1.125rem; margin-bottom: 2rem; opacity: 0.9;">
            Join thousands of satisfied customers and service providers on our platform
        </p>
        <div class="d-flex justify-center gap-2">
            <?php if (!isLoggedIn()): ?>
                <a href="/user/register.php" class="btn btn-outline" style="background: white; color: var(--primary-color); border-color: white;">Sign Up as Customer</a>
                <a href="/provider/register.php" class="btn" style="background: white; color: var(--primary-color);">Become a Provider</a>
            <?php else: ?>
                <a href="/user/search.php" class="btn" style="background: white; color: var(--primary-color);">Browse Services</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
