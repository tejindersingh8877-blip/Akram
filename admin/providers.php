<?php
$page_title = "Manage Providers";
require_once '../includes/header.php';
requireAdminLogin();

$conn = getDBConnection();

// Handle provider approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $provider_id = intval($_POST['provider_id']);
    $action = $_POST['action'];
    $admin_id = $_SESSION['admin_id'];
    
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE providers SET verification_status = 'approved', approved_by = ?, approved_at = NOW() WHERE provider_id = ?");
        $stmt->bind_param("ii", $admin_id, $provider_id);
        $stmt->execute();
        setFlashMessage("Provider approved successfully", "success");
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE providers SET verification_status = 'rejected' WHERE provider_id = ?");
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        setFlashMessage("Provider rejected", "info");
    } elseif ($action === 'block') {
        $stmt = $conn->prepare("UPDATE providers SET status = 'blocked' WHERE provider_id = ?");
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        setFlashMessage("Provider blocked", "success");
    } elseif ($action === 'unblock') {
        $stmt = $conn->prepare("UPDATE providers SET status = 'active' WHERE provider_id = ?");
        $stmt->bind_param("i", $provider_id);
        $stmt->execute();
        setFlashMessage("Provider unblocked", "success");
    }
    
    redirect('/admin/providers.php');
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = "1=1";
if ($filter === 'pending') $where = "verification_status = 'pending'";
elseif ($filter === 'approved') $where = "verification_status = 'approved'";
elseif ($filter === 'rejected') $where = "verification_status = 'rejected'";
elseif ($filter === 'blocked') $where = "status = 'blocked'";

$providers = $conn->query("
    SELECT p.*, 
           (SELECT COUNT(*) FROM services WHERE provider_id = p.provider_id) as service_count,
           (SELECT COUNT(*) FROM bookings WHERE provider_id = p.provider_id) as booking_count
    FROM providers p
    WHERE $where
    ORDER BY p.created_at DESC
");
?>

<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    
    <div class="dashboard-content">
        <div class="dashboard-header">
            <h1>Manage Providers</h1>
            <p>Approve, reject, or manage service providers</p>
        </div>
        
        <!-- Filter -->
        <div class="filter-bar">
            <div class="d-flex gap-2">
                <a href="?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-outline'; ?>">
                    All Providers
                </a>
                <a href="?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-warning' : 'btn-outline'; ?>">
                    Pending Approval
                </a>
                <a href="?filter=approved" class="btn <?php echo $filter === 'approved' ? 'btn-success' : 'btn-outline'; ?>">
                    Approved
                </a>
                <a href="?filter=rejected" class="btn <?php echo $filter === 'rejected' ? 'btn-danger' : 'btn-outline'; ?>">
                    Rejected
                </a>
                <a href="?filter=blocked" class="btn <?php echo $filter === 'blocked' ? 'btn-secondary' : 'btn-outline'; ?>">
                    Blocked
                </a>
            </div>
        </div>
        
        <!-- Providers Table -->
        <div class="table-container">
            <div class="table-header">
                <h2>Providers (<?php echo $providers->num_rows; ?>)</h2>
                <div class="table-search">
                    <input type="text" placeholder="Search providers...">
                    <i class="fas fa-search"></i>
                </div>
            </div>
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Name</th>
                            <th>Owner</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Category</th>
                            <th>Services</th>
                            <th>Bookings</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($provider = $providers->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $provider['provider_id']; ?></td>
                                <td><?php echo e($provider['business_name']); ?></td>
                                <td><?php echo e($provider['full_name']); ?></td>
                                <td><?php echo e($provider['email']); ?></td>
                                <td><?php echo e($provider['phone']); ?></td>
                                <td><?php echo e($provider['service_category']); ?></td>
                                <td><?php echo $provider['service_count']; ?></td>
                                <td><?php echo $provider['booking_count']; ?></td>
                                <td>
                                    <?php echo status_badge($provider['verification_status']); ?>
                                    <?php if ($provider['status'] === 'blocked'): ?>
                                        <br><?php echo status_badge('blocked'); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <?php if ($provider['verification_status'] === 'pending'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                                <button type="submit" name="action" value="approve" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                                <button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($provider['status'] === 'active'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                                <button type="submit" name="action" value="block" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Block this provider?')">
                                                    <i class="fas fa-ban"></i> Block
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="provider_id" value="<?php echo $provider['provider_id']; ?>">
                                                <button type="submit" name="action" value="unblock" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check"></i> Unblock
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <a href="provider-detail.php?id=<?php echo $provider['provider_id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
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
