<?php
$page_title = "About Us";
require_once 'includes/header.php';

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM cms_pages WHERE page_slug = 'about'");
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
?>

<div class="container" style="padding: 3rem 20px;">
    <h1 class="section-title"><?php echo e($page['page_title'] ?? 'About Us'); ?></h1>
    
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-body">
            <?php if ($page): ?>
                <?php echo $page['page_content']; ?>
            <?php else: ?>
                <h2>About ProFix Masters</h2>
                <p>ProFix Masters is your trusted platform for finding and booking reliable local service providers.</p>
                <p>We connect customers with verified professionals across various service categories.</p>
            <?php endif; ?>
            
            <hr style="margin: 2rem 0;">
            
            <h3>Why Choose Us?</h3>
            <div class="grid grid-cols-3" style="margin-top: 2rem;">
                <div class="text-center">
                    <i class="fas fa-shield-alt" style="font-size: 3rem; color: var(--primary-color); margin-bottom: 1rem;"></i>
                    <h4>Verified Providers</h4>
                    <p>All service providers are verified by our admin team</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-star" style="font-size: 3rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                    <h4>Quality Service</h4>
                    <p>Read reviews and ratings from real customers</p>
                </div>
                <div class="text-center">
                    <i class="fas fa-clock" style="font-size: 3rem; color: var(--secondary-color); margin-bottom: 1rem;"></i>
                    <h4>Easy Booking</h4>
                    <p>Book services in just a few clicks</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
