<?php
/**
 * Helper Functions
 * ProFix Masters - Service Marketplace Platform
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Sanitize input to prevent XSS
 * @param string $data
 * @return string
 */
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email format
 * @param string $email
 * @return bool
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (basic validation)
 * @param string $phone
 * @return bool
 */
function validate_phone($phone) {
    return preg_match('/^[0-9]{10,15}$/', $phone);
}

/**
 * Validate password strength
 * Minimum 8 characters, at least one uppercase, one lowercase, one number
 * @param string $password
 * @return bool
 */
function validate_password($password) {
    return strlen($password) >= 8 && 
           preg_match('/[A-Z]/', $password) && 
           preg_match('/[a-z]/', $password) && 
           preg_match('/[0-9]/', $password);
}

/**
 * Hash password
 * @param string $password
 * @return string
 */
function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Verify password
 * @param string $password
 * @param string $hash
 * @return bool
 */
function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Redirect to URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Calculate commission
 * @param float $amount
 * @param float $percentage
 * @return array ['commission' => float, 'provider_earnings' => float]
 */
function calculate_commission($amount, $percentage = 15.0) {
    $commission = ($amount * $percentage) / 100;
    $provider_earnings = $amount - $commission;
    
    return [
        'commission' => round($commission, 2),
        'provider_earnings' => round($provider_earnings, 2)
    ];
}

/**
 * Upload file with validation
 * @param array $file $_FILES array element
 * @param string $target_dir Target directory
 * @param array $allowed_types Allowed MIME types
 * @param int $max_size Max file size in bytes
 * @return array ['success' => bool, 'file_path' => string, 'error' => string]
 */
function upload_file($file, $target_dir, $allowed_types = [], $max_size = 5242880) {
    $result = ['success' => false, 'file_path' => '', 'error' => ''];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        $result['error'] = 'No file uploaded';
        return $result;
    }
    
    // Check file size
    if ($file['size'] > $max_size) {
        $result['error'] = 'File size exceeds maximum allowed size';
        return $result;
    }
    
    // Check file type
    $file_type = mime_content_type($file['tmp_name']);
    if (!empty($allowed_types) && !in_array($file_type, $allowed_types)) {
        $result['error'] = 'Invalid file type';
        return $result;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $target_path = $target_dir . '/' . $filename;
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        $result['success'] = true;
        $result['file_path'] = $filename;
    } else {
        $result['error'] = 'Failed to move uploaded file';
    }
    
    return $result;
}

/**
 * Format date for display
 * @param string $date
 * @param string $format
 * @return string
 */
function format_date($date, $format = 'M d, Y') {
    if (empty($date)) return '';
    return date($format, strtotime($date));
}

/**
 * Format currency
 * @param float $amount
 * @return string
 */
function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Get user details by ID
 * @param int $user_id
 * @return array|null
 */
function get_user_details($user_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT user_id, full_name, email, phone, address, city, status FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get provider details by ID
 * @param int $provider_id
 * @return array|null
 */
function get_provider_details($provider_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT * FROM providers WHERE provider_id = ?");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get service details by ID
 * @param int $service_id
 * @return array|null
 */
function get_service_details($service_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("
        SELECT s.*, p.business_name, p.full_name as provider_name, c.category_name,
               (SELECT image_path FROM service_images WHERE service_id = s.service_id AND is_primary = 1 LIMIT 1) as primary_image,
               (SELECT AVG(rating) FROM reviews WHERE service_id = s.service_id) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE service_id = s.service_id) as review_count
        FROM services s
        JOIN providers p ON s.provider_id = p.provider_id
        JOIN service_categories c ON s.category_id = c.category_id
        WHERE s.service_id = ?
    ");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

/**
 * Get average rating for service
 * @param int $service_id
 * @return float
 */
function get_service_rating($service_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['avg_rating'] ?? 0, 1);
}

/**
 * Get average rating for provider
 * @param int $provider_id
 * @return float
 */
function get_provider_rating($provider_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE provider_id = ?");
    $stmt->bind_param("i", $provider_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return round($row['avg_rating'] ?? 0, 1);
}

/**
 * Get total review count
 * @param int $service_id
 * @return int
 */
function get_review_count($service_id) {
    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE service_id = ?");
    $stmt->bind_param("i", $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'] ?? 0;
}

/**
 * Check if user can review booking
 * @param int $booking_id
 * @param int $user_id
 * @return bool
 */
function can_review_booking($booking_id, $user_id) {
    $conn = getDBConnection();
    
    // Check if booking is completed and belongs to user
    $stmt = $conn->prepare("
        SELECT booking_id FROM bookings 
        WHERE booking_id = ? AND user_id = ? AND booking_status = 'completed'
    ");
    $stmt->bind_param("ii", $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    // Check if already reviewed
    $stmt = $conn->prepare("SELECT review_id FROM reviews WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows === 0;
}

/**
 * Generate status badge HTML
 * @param string $status
 * @return string
 */
function status_badge($status) {
    $colors = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'active' => 'success',
        'inactive' => 'secondary',
        'blocked' => 'danger',
        'approved' => 'success',
        'rejected' => 'danger'
    ];
    
    $color = $colors[$status] ?? 'secondary';
    return '<span class="badge badge-' . $color . '">' . ucfirst($status) . '</span>';
}

/**
 * Pagination helper
 * @param int $total_records
 * @param int $per_page
 * @param int $current_page
 * @return array ['total_pages' => int, 'offset' => int, 'start' => int, 'end' => int]
 */
function paginate($total_records, $per_page = 10, $current_page = 1) {
    $total_pages = ceil($total_records / $per_page);
    $current_page = max(1, min($current_page, $total_pages));
    $offset = ($current_page - 1) * $per_page;
    $start = $offset + 1;
    $end = min($offset + $per_page, $total_records);
    
    return [
        'total_pages' => $total_pages,
        'current_page' => $current_page,
        'offset' => $offset,
        'start' => $start,
        'end' => $end,
        'per_page' => $per_page
    ];
}

/**
 * Escape output for safe display
 * @param string $string
 * @return string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
