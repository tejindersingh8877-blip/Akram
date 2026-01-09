<?php
$page_title = "Provider Bookings";
require_once '../includes/header.php';
requireProviderLogin();

$provider_id = $_SESSION['provider_id'];
$conn = getDBConnection();

// Handle booking status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action'];
    
    // Verify booking belongs to this provider
    $stmt = $conn->prepare("SELECT booking_id FROM bookings WHERE booking_id = ? AND provider_id = ?");
    $stmt->bind_param("ii", $booking_id, $provider_id);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        if ($action === 'accept') {
            $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'confirmed', updated_at = NOW() WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            setFlashMessage("Booking accepted successfully", "success");
        } elseif ($action === 'reject') {
            $reason = sanitize_input($_POST['reason'] ?? 'Provider rejected the booking');
            $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled', cancelled_at = NOW(), cancellation_reason = ? WHERE booking_id = ?");
            $stmt->bind_param("si", $reason, $booking_id);
            $stmt->execute();
            setFlashMessage("Booking rejected", "info");
        } elseif ($action === 'complete') {
            $stmt = $conn->prepare("UPDATE bookings SET booking_status = 'completed', completed_at = NOW() WHERE booking_id = ?");
            $stmt->bind_param("i", $booking_id);
            $stmt->execute();
            setFlashMessage("Booking marked as completed", "success");
        }
    }
    
    redirect('/provider/bookings.php');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = "b.provider_id = $provider_id";
if ($filter === 'pending') $where .= " AND b.booking_status = 'pending'";
elseif ($filter === 'confirmed') $where .= " AND b.booking_status = 'confirmed'";
elseif ($filter === 'completed') $where .= " AND b.booking_status = 'completed'";

$bookings = $conn->query("
    SELECT b.*, s.service_title, u.full_name as user_name, u.phone as user_phone, u.email as user_email
    FROM bookings b
    JOIN services s ON b.service_id = s.service_id
    JOIN users u ON b.user_id = u.user_id
    WHERE $where
    ORDER BY b.created_at DESC
");
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
            <a href="/provider/add-service.php" class="dashboard-nav-item">
                <i class="fas fa-plus"></i>
                <span>Add Service</span>
            </a>
            <a href="/provider/bookings.php" class="dashboard-nav-item active">
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
            <h1>Booking Requests</h1>
            <p>Manage your service bookings</p>
        </div>
        
        <!-- Filter -->
        <div class="filter-bar">
            <div class="d-flex gap-2">
                <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                    All Bookings
                </a>
                <a href="?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-warning' : 'btn-outline'; ?>">
                    Pending
                </a>
                <a href="?filter=confirmed" class="btn <?php echo $filter === 'confirmed' ? 'btn-info' : 'btn-outline'; ?>">
                    Confirmed
                </a>
                <a href="?filter=completed" class="btn <?php echo $filter === 'completed' ? 'btn-success' : 'btn-outline'; ?>">
                    Completed
                </a>
            </div>
        </div>
        
        <!-- Bookings Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Bookings (<?php echo $bookings->num_rows; ?>)</h2>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Service</th>
                            <th>Customer</th>
                            <th>Contact</th>
                            <th>Date & Time</th>
                            <th>Address</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($booking = $bookings->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $booking['booking_id']; ?></td>
                                <td><?php echo e($booking['service_title']); ?></td>
                                <td><?php echo e($booking['user_name']); ?></td>
                                <td>
                                    <?php echo e($booking['user_phone']); ?><br>
                                    <small><?php echo e($booking['user_email']); ?></small>
                                </td>
                                <td>
                                    <?php echo format_date($booking['booking_date']); ?><br>
                                    <small><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></small>
                                </td>
                                <td><?php echo e(substr($booking['service_address'], 0, 30)) . '...'; ?></td>
                                <td><?php echo format_currency($booking['total_amount']); ?></td>
                                <td><?php echo status_badge($booking['booking_status']); ?></td>
                                <td>
                                    <div class="actions">
                                        <?php if ($booking['booking_status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <button type="submit" name="action" value="accept" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Accept
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <input type="hidden" name="reason" value="Provider rejected">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm"
                                                        onclick="return confirm('Reject this booking?')">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php elseif ($booking['booking_status'] === 'confirmed'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                                <button type="submit" name="action" value="complete" class="btn btn-success btn-sm"
                                                        onclick="return confirm('Mark this booking as completed?')">
                                                    <i class="fas fa-check-circle"></i> Mark Complete
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
