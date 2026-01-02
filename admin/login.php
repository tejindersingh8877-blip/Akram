<?php
$page_title = "Admin Login";
require_once '../includes/header.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('/admin/index.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize_input($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT admin_id, username, email, password FROM admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            if (verify_password($password, $admin['password'])) {
                setAdminSession($admin['admin_id'], $admin['username'], $admin['email']);
                setFlashMessage("Welcome back, " . $admin['username'] . "!", "success");
                redirect('/admin/index.php');
            } else {
                $errors[] = "Invalid username or password";
            }
        } else {
            $errors[] = "Invalid username or password";
        }
    }
}
?>

<div class="auth-container">
    <div class="auth-card">
        <h2>Admin Login</h2>
        <p class="text-center mb-3" style="color: var(--text-light);">
            <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-color);"></i>
        </p>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <?php foreach ($errors as $error): ?>
                    <div><?php echo e($error); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" onsubmit="return validateForm(this)">
            <div class="form-group">
                <label class="form-label">Username *</label>
                <input type="text" name="username" class="form-control" required 
                       value="<?php echo e($_POST['username'] ?? ''); ?>" data-name="Username">
            </div>
            
            <div class="form-group">
                <label class="form-label">Password *</label>
                <input type="password" name="password" class="form-control" required data-name="Password">
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>
        
        <div class="auth-footer">
            <a href="/user/login.php">User Login</a> | <a href="/provider/login.php">Provider Login</a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
