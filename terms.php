<?php
$page_title = "Terms & Conditions";
require_once 'includes/header.php';

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM cms_pages WHERE page_slug = 'terms'");
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
?>

<div class="container" style="padding: 3rem 20px;">
    <div class="card" style="max-width: 900px; margin: 0 auto;">
        <div class="card-body">
            <?php if ($page): ?>
                <h1><?php echo e($page['page_title']); ?></h1>
                <?php echo $page['page_content']; ?>
            <?php else: ?>
                <h1>Terms & Conditions</h1>
                <p>Welcome to ProFix Masters. By accessing and using our platform, you agree to these terms and conditions.</p>
                <h3>User Responsibilities</h3>
                <p>Users must provide accurate information and maintain account security.</p>
                <h3>Provider Responsibilities</h3>
                <p>Service providers must deliver services as described and maintain professional standards.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
