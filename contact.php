<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

$success = $error = '';
$productId = (int)($_GET['product'] ?? 0);
$preProduct = null;
if ($productId) {
    $s = $pdo->prepare("SELECT name FROM products WHERE id=? AND is_active=1");
    $s->execute([$productId]);
    $preProduct = $s->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $pid     = (int)($_POST['product_id'] ?? 0);

    if (!$name || !$email || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, subject, message, product_id) VALUES (?,?,?,?,?,?)");
        $stmt->execute([$name, $email, $phone, $subject, $message, $pid ?: null]);
        $success = 'Thank you! Your enquiry has been received. We will contact you shortly.';
        // Reset
        $name = $email = $phone = $subject = $message = '';
    }
}

$pageTitle = 'Contact Us - ' . getSetting('site_name');
include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>Contact Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">Contact</li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <!-- Contact Info Cards -->
        <div class="row g-4 mb-5">
            <div class="col-md-4" data-aos="fade-up">
                <div class="contact-card text-center">
                    <div class="contact-icon mx-auto"><i class="fas fa-map-marker-alt"></i></div>
                    <h6 class="fw-bold">Our Location</h6>
                    <p class="text-muted small"><?= sanitize(getSetting('contact_address')) ?></p>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                <div class="contact-card text-center">
                    <div class="contact-icon mx-auto"><i class="fas fa-phone-alt"></i></div>
                    <h6 class="fw-bold">Call Us</h6>
                    <?php foreach(explode(',', getSetting('contact_phone')) as $ph): ?>
                    <p class="mb-1"><a href="tel:<?= preg_replace('/\s/','',$ph) ?>" class="text-muted small text-decoration-none"><?= sanitize(trim($ph)) ?></a></p>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                <div class="contact-card text-center">
                    <div class="contact-icon mx-auto"><i class="fas fa-envelope"></i></div>
                    <h6 class="fw-bold">Email Us</h6>
                    <p class="text-muted small"><a href="mailto:<?= getSetting('contact_email') ?>" class="text-muted text-decoration-none"><?= sanitize(getSetting('contact_email')) ?></a></p>
                </div>
            </div>
        </div>

        <div class="row g-5">
            <!-- Form -->
            <div class="col-lg-7" data-aos="fade-right">
                <div class="card border-0 shadow-sm p-4" style="border-radius:14px;">
                    <h4 class="fw-bold mb-1" style="font-family:'Playfair Display',serif;">Send Us a Message</h4>
                    <p class="text-muted small mb-4">Fill in the form and our team will respond within 24 hours.</p>

                    <?php if ($success): ?>
                    <div class="alert alert-success alert-auto"><i class="fas fa-check-circle me-2"></i><?= $success ?></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert alert-danger"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
                    <?php endif; ?>

                    <form method="post" action="contact.php">
                        <?php if ($productId): ?><input type="hidden" name="product_id" value="<?= $productId ?>"><?php endif; ?>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label fw-500 small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" value="<?= sanitize($name ?? '') ?>" placeholder="Your name" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-500 small">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" name="email" value="<?= sanitize($email ?? '') ?>" placeholder="your@email.com" required>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-500 small">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" value="<?= sanitize($phone ?? '') ?>" placeholder="+91 00000 00000">
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label fw-500 small">Subject</label>
                                <input type="text" class="form-control" name="subject" value="<?= $preProduct ? 'Enquiry about ' . sanitize($preProduct) : sanitize($subject ?? '') ?>" placeholder="Product enquiry / Pricing...">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-500 small">Message <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="message" rows="5" placeholder="Tell us what you're looking for..." required><?= sanitize($message ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-submit px-5 py-2 w-100">
                                    <i class="fas fa-paper-plane me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Map + WhatsApp -->
            <div class="col-lg-5" data-aos="fade-left">
                <div class="card border-0 shadow-sm overflow-hidden mb-3" style="border-radius:14px;">
                    <iframe 
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3807.283399765858!2d78.49200!3d17.38500!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bcb99daeaebd2c7%3A0xae93b78392bafbc2!2sKachiguda%2C%20Hyderabad%2C%20Telangana!5e0!3m2!1sen!2sin!4v1234567890"
                        width="100%" height="240" style="border:0;" allowfullscreen loading="lazy">
                    </iframe>
                </div>
                <div class="card border-0 p-4 text-center" style="background:linear-gradient(135deg,#25D366,#1ebe5a); border-radius:14px;">
                    <i class="fab fa-whatsapp fa-3x text-white mb-3"></i>
                    <h5 class="text-white fw-bold">Chat on WhatsApp</h5>
                    <p class="text-white small opacity-75 mb-3">Get instant support or enquire about any product directly on WhatsApp.</p>
                    <a href="https://wa.me/<?= getSetting('contact_whatsapp') ?>?text=Hello!%20I%20want%20to%20enquire%20about%20your%20beauty%20products." 
                       target="_blank" class="btn btn-light fw-bold text-success rounded-pill px-4">
                        Start Chat
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
