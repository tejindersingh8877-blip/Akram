<?php
$page_title = "User Registration";
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/index.php');
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $address = sanitize_input($_POST['address'] ?? '');
    $city = sanitize_input($_POST['city'] ?? '');
    
    // Validation
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email) || !validate_email($email)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($phone) || !validate_phone($phone)) {
        $errors[] = "Valid phone number is required";
    }
    
    if (empty($password) || !validate_password($password)) {
        $errors[] = "Password must be at least 8 characters with uppercase, lowercase, and numbers";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($address)) {
        $errors[] = "Address is required";
    }
    
    if (empty($city)) {
        $errors[] = "City is required";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already registered";
        }
    }
    
    // Insert user
    if (empty($errors)) {
        $hashed_password = hash_password($password);
        $stmt = $conn->prepare("
            INSERT INTO users (full_name, email, phone, password, address, city)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssss", $full_name, $email, $phone, $hashed_password, $address, $city);
        
        if ($stmt->execute()) {
            $success = true;
            setFlashMessage("Registration successful! Please login to continue.", "success");
            redirect('/user/login.php');
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Create Your Account</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateForm(this)">
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
                       value="<?php echo e($_POST['phone'] ?? ''); ?>" data-name="Phone Number">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required data-name="Password">
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirm Password *</label>
                <input type="password" name="confirm_password" class="form-control" required data-name="Confirm Password">
            </div>
            
            <div class="form-group">
                <label class="form-label">Address *</label>
                <textarea name="address" class="form-control" required data-name="Address"><?php echo e($_POST['address'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" name="city" class="form-control" required 
                       value="<?php echo e($_POST['city'] ?? ''); ?>" data-name="City">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>
        
        <div class="auth-footer">
            Already have an account? <a href="/user/login.php">Login here</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
