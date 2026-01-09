<?php
$page_title = "User Login";
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation
    if (empty($email) || !validate_email($email)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // Check credentials
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password, status FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Check if account is blocked
            if ($user['status'] === 'blocked') {
                $errors[] = "Your account has been blocked. Please contact support.";
            }
            // Verify password
            else if (verify_password($password, $user['password'])) {
                // Set session
                setUserSession($user['user_id'], $user['full_name'], $user['email']);
                setFlashMessage("Welcome back, " . $user['full_name'] . "!", "success");
                redirect('/user/dashboard.php');
            } else {
                $errors[] = "Invalid email or password";
            }
        } else {
            $errors[] = "Invalid email or password";
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>User Login</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateForm(this)">
            <div class="form-group">
                <label class="form-label">Email *</label>
                <input type="email" name="email" class="form-control" required 
                       value="<?php echo e($_POST['email'] ?? ''); ?>" data-name="Email">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required data-name="Password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-footer">
            Don't have an account? <a href="/user/register.php">Register here</a><br>
            <a href="/provider/login.php">Login as Provider</a> | <a href="/admin/login.php">Admin Login</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
