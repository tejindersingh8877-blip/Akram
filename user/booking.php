<?php
$page_title = "Book Service";
require_once '../includes/header.php';
requireUserLogin();

$service_id = intval($_GET['service_id'] ?? 0);

if ($service_id === 0) {
    redirect('/user/search.php');
}

$service = get_service_details($service_id);

if (!$service) {
    setFlashMessage("Service not found", "error");
    redirect('/user/search.php');
}

$user_id = $_SESSION['user_id'];
$user = get_user_details($user_id);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = sanitize_input($_POST['booking_date'] ?? '');
    $booking_time = sanitize_input($_POST['booking_time'] ?? '');
    $service_address = sanitize_input($_POST['service_address'] ?? '');
    $special_notes = sanitize_input($_POST['special_notes'] ?? '');
    
    // Validation
    if (empty($booking_date)) $errors[] = "Booking date is required";
    if (empty($booking_time)) $errors[] = "Booking time is required";
    if (empty($service_address)) $errors[] = "Service address is required";
    
    // Check if date is in future
    if (!empty($booking_date) && strtotime($booking_date) < strtotime('today')) {
        $errors[] = "Booking date must be in the future";
    }
    
    // Create booking
    if (empty($errors)) {
        $conn = getDBConnection();
        $total_amount = $service['price'];
        $provider_id = $service['provider_id'];
        
        $stmt = $conn->prepare("
            INSERT INTO bookings (user_id, service_id, provider_id, booking_date, booking_time, 
                                service_address, special_notes, total_amount, booking_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iiissssd", $user_id, $service_id, $provider_id, $booking_date, 
                         $booking_time, $service_address, $special_notes, $total_amount);
        
        if ($stmt->execute()) {
            $booking_id = $conn->insert_id;
            
            // Create payment record
            $stmt = $conn->prepare("
                INSERT INTO payments (booking_id, user_id, provider_id, amount, payment_status)
                VALUES (?, ?, ?, ?, 'pending')
            ");
            $stmt->bind_param("iiid", $booking_id, $user_id, $provider_id, $total_amount);
            $stmt->execute();
            
            // Calculate and store commission
            $commission_percent = 15.0; // Default commission
            $commission_calc = calculate_commission($total_amount, $commission_percent);
            
            $stmt = $conn->prepare("
                INSERT INTO commissions (booking_id, provider_id, booking_amount, commission_percentage, 
                                       commission_amount, provider_earnings)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("iidddd", $booking_id, $provider_id, $total_amount, $commission_percent,
                             $commission_calc['commission'], $commission_calc['provider_earnings']);
            $stmt->execute();
            
            setFlashMessage("Booking created successfully! Wait for provider confirmation.", "success");
            redirect('/user/my-bookings.php');
        } else {
            $errors[] = "Failed to create booking. Please try again.";
        }
    }
}
?>

<div class="container" style="padding: 2rem 20px;">
    <div class="auth-container" style="max-width: 700px;">
        <div class="auth-card">
            <h2>Book Service</h2>
            
            <!-- Service Info -->
            <div class="card mb-3">
                <div class="card-body">
                    <h3><?php echo e($service['service_title']); ?></h3>
                    <p><strong>Provider:</strong> <?php echo e($service['business_name']); ?></p>
                    <p><strong>Price:</strong> <span class="price"><?php echo format_currency($service['price']); ?></span></p>
                    <p><strong>Duration:</strong> <?php echo $service['duration']; ?> minutes</p>
                </div>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <div><?php echo e($error); ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" onsubmit="return validateForm(this)">
                <div class="form-group">
                    <label class="form-label">Booking Date *</label>
                    <input type="date" name="booking_date" class="form-control" required 
                           min="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo e($_POST['booking_date'] ?? ''); ?>" data-name="Booking Date">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Booking Time *</label>
                    <input type="time" name="booking_time" class="form-control" required 
                           value="<?php echo e($_POST['booking_time'] ?? ''); ?>" data-name="Booking Time">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Service Address *</label>
                    <textarea name="service_address" class="form-control" required rows="3" 
                              data-name="Service Address"><?php echo e($_POST['service_address'] ?? $user['address']); ?></textarea>
                    <small class="form-text">Address where service should be provided</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Special Notes (Optional)</label>
                    <textarea name="special_notes" class="form-control" rows="3" 
                              placeholder="Any special requirements or instructions..."><?php echo e($_POST['special_notes'] ?? ''); ?></textarea>
                </div>
                
                <div class="card mb-3" style="background: var(--light-color);">
                    <div class="card-body">
                        <h4>Booking Summary</h4>
                        <div class="d-flex justify-between mb-2">
                            <span>Service Charge:</span>
                            <strong><?php echo format_currency($service['price']); ?></strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-between">
                            <span style="font-size: 1.25rem; font-weight: 600;">Total Amount:</span>
                            <span style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">
                                <?php echo format_currency($service['price']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Confirm Booking
                </button>
                
                <a href="/user/service-detail.php?id=<?php echo $service_id; ?>" class="btn btn-outline btn-block mt-2">
                    Cancel
                </a>
            </form>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
