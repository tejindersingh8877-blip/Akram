<?php
$page_title = "Add Service";
require_once '../includes/header.php';
requireProviderLogin();

$provider_id = $_SESSION['provider_id'];
$provider = get_provider_details($provider_id);

// Check if provider is approved
if ($provider['verification_status'] !== 'approved') {
    setFlashMessage("Your account must be approved before adding services", "error");
    redirect('/provider/dashboard.php');
}

$conn = getDBConnection();
$errors = [];

// Get categories
$categories = $conn->query("SELECT * FROM service_categories WHERE status = 'active' ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_title = sanitize_input($_POST['service_title'] ?? '');
    $description = sanitize_input($_POST['description'] ?? '');
    $category_id = intval($_POST['category_id'] ?? 0);
    $price = floatval($_POST['price'] ?? 0);
    $duration = intval($_POST['duration'] ?? 60);
    
    // Validation
    if (empty($service_title)) $errors[] = "Service title is required";
    if (empty($description)) $errors[] = "Description is required";
    if ($category_id === 0) $errors[] = "Please select a category";
    if ($price <= 0) $errors[] = "Price must be greater than 0";
    if ($duration <= 0) $errors[] = "Duration must be greater than 0";
    
    // Create service
    if (empty($errors)) {
        $stmt = $conn->prepare("
            INSERT INTO services (provider_id, category_id, service_title, description, price, duration, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $stmt->bind_param("iissdi", $provider_id, $category_id, $service_title, $description, $price, $duration);
        
        if ($stmt->execute()) {
            $service_id = $conn->insert_id;
            
            // Handle image uploads
            if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
                $is_primary = true;
                foreach ($_FILES['images']['name'] as $key => $name) {
                    if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                        $file = [
                            'name' => $_FILES['images']['name'][$key],
                            'type' => $_FILES['images']['type'][$key],
                            'tmp_name' => $_FILES['images']['tmp_name'][$key],
                            'error' => $_FILES['images']['error'][$key],
                            'size' => $_FILES['images']['size'][$key]
                        ];
                        
                        $upload_result = upload_file(
                            $file,
                            __DIR__ . '/../uploads/services',
                            ['image/jpeg', 'image/png', 'image/jpg'],
                            5242880
                        );
                        
                        if ($upload_result['success']) {
                            $stmt = $conn->prepare("
                                INSERT INTO service_images (service_id, image_path, is_primary)
                                VALUES (?, ?, ?)
                            ");
                            $stmt->bind_param("isi", $service_id, $upload_result['file_path'], $is_primary);
                            $stmt->execute();
                            $is_primary = false; // Only first image is primary
                        }
                    }
                }
            }
            
            setFlashMessage("Service added successfully!", "success");
            redirect('/provider/my-services.php');
        } else {
            $errors[] = "Failed to add service. Please try again.";
        }
    }
}
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
            <a href="/provider/my-services.php" class="dashboard-nav-item">
                <i class="fas fa-briefcase"></i>
                <span>My Services</span>
            </a>
            <a href="/provider/add-service.php" class="dashboard-nav-item active">
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
            <h1>Add New Service</h1>
            <p>Create a new service offering</p>
        </div>
        
        <div class="dashboard-card" style="max-width: 800px;">
            <div class="dashboard-card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error mb-3">
                        <?php foreach ($errors as $error): ?>
                            <div><?php echo e($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm(this)">
                    <div class="form-group">
                        <label class="form-label">Service Title *</label>
                        <input type="text" name="service_title" class="form-control" required 
                               value="<?php echo e($_POST['service_title'] ?? ''); ?>" 
                               placeholder="e.g., AC Installation & Repair"
                               data-name="Service Title">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-control" required data-name="Category">
                            <option value="">Select Category</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo $cat['category_id']; ?>"
                                        <?php echo (($_POST['category_id'] ?? 0) == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo e($cat['category_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description *</label>
                        <textarea name="description" class="form-control" required rows="5"
                                  placeholder="Describe your service in detail..."
                                  data-name="Description"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-2">
                        <div class="form-group">
                            <label class="form-label">Price (USD) *</label>
                            <input type="number" name="price" class="form-control" required min="1" step="0.01"
                                   value="<?php echo e($_POST['price'] ?? ''); ?>"
                                   placeholder="0.00"
                                   data-name="Price">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Duration (minutes) *</label>
                            <input type="number" name="duration" class="form-control" required min="15" step="15"
                                   value="<?php echo e($_POST['duration'] ?? '60'); ?>"
                                   data-name="Duration">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Service Images</label>
                        <input type="file" name="images[]" class="form-control" 
                               accept="image/jpeg,image/png,image/jpg" multiple>
                        <small class="form-text">Upload up to 5 images (JPEG, PNG - Max 5MB each). First image will be primary.</small>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Service
                        </button>
                        <a href="/provider/my-services.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
