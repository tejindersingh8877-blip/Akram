<?php
$page_title = "Search Services";
require_once '../includes/header.php';

$conn = getDBConnection();

// Get search parameters
$search_query = sanitize_input($_GET['q'] ?? '');
$category_id = intval($_GET['category'] ?? 0);
$city = sanitize_input($_GET['city'] ?? '');
$min_price = floatval($_GET['min_price'] ?? 0);
$max_price = floatval($_GET['max_price'] ?? 999999);
$sort = sanitize_input($_GET['sort'] ?? 'recent');

// Get all categories for filter
$categories = $conn->query("SELECT * FROM service_categories WHERE status = 'active' ORDER BY category_name");

// Build query
$where_conditions = ["s.status = 'active'", "p.verification_status = 'approved'", "p.status = 'active'"];
$params = [];
$types = "";

if (!empty($search_query)) {
    $where_conditions[] = "(s.service_title LIKE ? OR s.description LIKE ? OR p.business_name LIKE ?)";
    $search_param = "%$search_query%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if ($category_id > 0) {
    $where_conditions[] = "s.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

if (!empty($city)) {
    $where_conditions[] = "p.city LIKE ?";
    $params[] = "%$city%";
    $types .= "s";
}

if ($min_price > 0) {
    $where_conditions[] = "s.price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if ($max_price < 999999) {
    $where_conditions[] = "s.price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

$where_clause = implode(" AND ", $where_conditions);

// Sorting
$order_by = "s.created_at DESC";
if ($sort === 'price_low') $order_by = "s.price ASC";
elseif ($sort === 'price_high') $order_by = "s.price DESC";
elseif ($sort === 'rating') $order_by = "avg_rating DESC";

$query = "
    SELECT s.*, p.business_name, p.city as provider_city, c.category_name,
           (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as image,
           (SELECT AVG(rating) FROM reviews WHERE service_id = s.service_id) as avg_rating,
           (SELECT COUNT(*) FROM reviews WHERE service_id = s.service_id) as review_count
    FROM services s
    JOIN providers p ON s.provider_id = p.provider_id
    JOIN service_categories c ON s.category_id = c.category_id
    WHERE $where_clause
    ORDER BY $order_by
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$services_result = $stmt->get_result();
?>

<div class="container" style="padding: 2rem 20px;">
    <h1>Find Services</h1>
    
    <!-- Search & Filter Form -->
    <div class="filter-bar">
        <form method="GET" action="">
            <div class="filter-grid">
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <input type="text" name="q" class="form-control" placeholder="Service name, provider..."
                           value="<?php echo e($search_query); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php while ($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category_id']; ?>" 
                                    <?php echo $category_id == $cat['category_id'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['category_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" placeholder="Enter city"
                           value="<?php echo e($city); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sort By</label>
                    <select name="sort" class="form-control">
                        <option value="recent" <?php echo $sort === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                        <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="rating" <?php echo $sort === 'rating' ? 'selected' : ''; ?>>Highest Rated</option>
                    </select>
                </div>
                
                <div class="form-group" style="display: flex; align-items: end; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <a href="/user/search.php" class="btn btn-outline">Clear</a>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Results -->
    <div class="mb-3">
        <h3><?php echo $services_result->num_rows; ?> Services Found</h3>
    </div>
    
    <?php if ($services_result->num_rows > 0): ?>
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
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?php echo e($service['provider_city']); ?></span>
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
                            <a href="/user/service-detail.php?id=<?php echo $service['service_id']; ?>" class="btn btn-primary btn-sm">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No services found</h3>
            <p>Try adjusting your search criteria</p>
            <a href="/user/search.php" class="btn btn-primary mt-2">Clear Filters</a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
