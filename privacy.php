<?php
$page_title = "Privacy Policy";
require_once 'includes/header.php';

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM cms_pages WHERE page_slug = 'privacy'");
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
                <h1>Privacy Policy</h1>
                <p>At ProFix Masters, we take your privacy seriously. This policy describes how we collect, use, and protect your personal information.</p>
                <h3>Information Collection</h3>
                <p>We collect information necessary to provide our services including name, email, phone number, and address.</p>
                <h3>Data Protection</h3>
                <p>We implement security measures to protect your personal information.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
