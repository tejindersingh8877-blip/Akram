<?php
$page_title = "Provider Registration";
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$success = false;

// Get service categories
$conn = getDBConnection();
$categories = $conn->query("SELECT * FROM service_categories WHERE status = 'active' ORDER BY category_name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $business_name = sanitize_input($_POST['business_name'] ?? '');
    $business_address = sanitize_input($_POST['business_address'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    $service_category = sanitize_input($_POST['service_category'] ?? '');
    $experience_years = intval($_POST['experience_years'] ?? 0);
    
    // Validation
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($email) || !validate_email($email)) $errors[] = "Valid email is required";
    if (empty($phone) || !validate_phone($phone)) $errors[] = "Valid phone number is required";
    if (empty($password) || !validate_password($password)) $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and numbers";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match";
    if (empty($business_name)) $errors[] = "Business name is required";
    if (empty($business_address)) $errors[] = "Business address is required";
    if (empty($city)) $errors[] = "City is required";
    if (empty($service_category)) $errors[] = "Service category is required";
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT provider_id FROM providers WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }
    
    // Handle ID proof upload
    $id_proof_path = null;
    if (empty($errors) && isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
        $upload_result = upload_file(
            $_FILES['id_proof'],
            __DIR__ . '/../uploads/documents',
            ['application/pdf', 'image/jpeg', 'image/png'],
            5242880 // 5MB
        );
        
        if ($upload_result['success']) {
            $id_proof_path = $upload_result['file_path'];
        } else {
            $errors[] = $upload_result['error'];
        }
    }
    
    // Insert provider
    if (empty($errors)) {
        $hashed_password = hash_password($password);
        $stmt = $conn->prepare("
            INSERT INTO providers (full_name, email, phone, password, business_name, business_address, 
                                 city, service_category, experience_years, id_proof_path, verification_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("sssssssis", $full_name, $email, $phone, $hashed_password, $business_name, 
                         $business_address, $city, $service_category, $experience_years, $id_proof_path);
        
        if ($stmt->execute()) {
            setFlashMessage("Registration successful! Your account will be verified by admin soon.", "success");
            redirect('/provider/login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Become a Service Provider</h2>
        <p class="text-center mb-3" style="color: var(--text-light);">Join our platform and grow your business</p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" onsubmit="return validateForm(this)">
            <h4>Personal Information</h4>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required 
                       value="<?php echo e($_POST['full_name'] ?? ''); ?>" data-name="Full Name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required 
                       value="<?php echo e($_POST['email'] ?? ''); ?>" data-name="Email">
            </div>
            
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="tel" name="phone" class="form-control" required 
                       value="<?php echo e($_POST['phone'] ?? ''); ?>" data-name="Phone">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required data-name="Password">
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            
            <h4 class="mt-3">Business Information</h4>
            <div class="form-group">
                <label class="form-label">Business Name *</label>
                <input type="text" name="business_name" class="form-control" required 
                       value="<?php echo e($_POST['business_name'] ?? ''); ?>" data-name="Business Name">
            </div>
            
            <div class="form-group">
                <label class="form-label">Business Address *</label>
                <textarea name="business_address" class="form-control" required data-name="Business Address"><?php echo e($_POST['business_address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" required 
                       value="<?php echo e($_POST['city'] ?? ''); ?>" data-name="City">
            </div>
            
            <div class="form-group">
                <label class="form-label">Service Category *</label>
                <select name="service_category" class="form-control" required data-name="Service Category">
                    <option value="">Select Category</option>
                    <?php while ($cat = $categories->fetch_assoc()): ?>
                        <option value="<?php echo e($cat['category_name']); ?>" 
                                <?php echo (($_POST['service_category'] ?? '') === $cat['category_name']) ? 'selected' : ''; ?>>
                            <?php echo e($cat['category_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Years of Experience *</label>
                <input type="number" name="experience_years" class="form-control" required min="0" 
                       value="<?php echo e($_POST['experience_years'] ?? '0'); ?>" data-name="Experience">
            </div>
            
            <div class="form-group">
                <label class="form-label">ID Proof (PDF, JPEG, PNG - Max 5MB)</label>
                <input type="file" name="id_proof" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                <small class="form-text">Upload your government ID or business license</small>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register as Provider</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="/provider/login.php">Login here</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
