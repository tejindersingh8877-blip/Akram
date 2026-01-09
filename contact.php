<?php
$page_title = "Contact Us";
require_once 'includes/header.php';

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT * FROM cms_pages WHERE page_slug = 'contact'");
$stmt->execute();
$page = $stmt->get_result()->fetch_assoc();
?>

<div class="container" style="padding: 3rem 20px;">
    <h1 class="section-title"><?php echo e($page['page_title'] ?? 'Contact Us'); ?></h1>
    
    <div class="grid grid-cols-2" style="max-width: 1000px; margin: 0 auto; gap: 3rem;">
        <div class="card">
            <div class="card-body">
                <?php if ($page): ?>
                    <?php echo $page['page_content']; ?>
                <?php else: ?>
                    <h2>Get In Touch</h2>
                    <p><strong>Email:</strong> support@profixmasters.com</p>
                    <p><strong>Phone:</strong> +1 (555) 123-4567</p>
                    <p><strong>Address:</strong> 123 Business Street, Suite 100, City, State 12345</p>
                    <p><strong>Business Hours:</strong><br>Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 10:00 AM - 4:00 PM</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <h3>Send Us a Message</h3>
                <form method="POST" action="" onsubmit="return validateForm(this)">
                    <div class="form-group">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" required data-name="Name">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" required data-name="Email">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Subject *</label>
                        <input type="text" name="subject" class="form-control" required data-name="Subject">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Message *</label>
                        <textarea name="message" class="form-control" required rows="5" data-name="Message"></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
