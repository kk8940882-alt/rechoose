<?php
$pageTitle = 'Site Settings';
require_once __DIR__ . '/includes/header.php';

$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = ['site_name','site_tagline','contact_email','contact_phone','contact_address','contact_whatsapp','site_about','meta_description','facebook_url','instagram_url','whatsapp_url'];
    foreach ($fields as $f) {
        $val = trim($_POST[$f] ?? '');
        $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?")->execute([$f,$val,$val]);
    }
    $success = 'Settings saved successfully!';
}

$s = [];
$rows = $pdo->query("SELECT setting_key, setting_value FROM settings")->fetchAll();
foreach ($rows as $r) $s[$r['setting_key']] = $r['setting_value'];
?>

<?php if ($success): ?><div class="alert alert-success alert-auto mb-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div><?php endif; ?>

<form method="post">
<div class="row g-4">
    <div class="col-lg-8">
        <!-- General -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;"><i class="fas fa-globe me-2 text-danger"></i>General Settings</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Site Name</label>
                    <input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($s['site_name']??'') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Site Tagline</label>
                    <input type="text" class="form-control" name="site_tagline" value="<?= htmlspecialchars($s['site_tagline']??'') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label">About Us Text</label>
                    <textarea class="form-control" name="site_about" rows="3"><?= htmlspecialchars($s['site_about']??'') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Meta Description (SEO)</label>
                    <textarea class="form-control" name="meta_description" rows="2"><?= htmlspecialchars($s['meta_description']??'') ?></textarea>
                    <small class="text-muted">Shown in Google search results. Keep under 160 characters.</small>
                </div>
            </div>
        </div>

        <!-- Contact -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;"><i class="fas fa-phone-alt me-2 text-danger"></i>Contact Information</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" name="contact_email" value="<?= htmlspecialchars($s['contact_email']??'') ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number(s)</label>
                    <input type="text" class="form-control" name="contact_phone" value="<?= htmlspecialchars($s['contact_phone']??'') ?>" placeholder="8079021482, 7726935291">
                </div>
                <div class="col-12">
                    <label class="form-label">Address</label>
                    <textarea class="form-control" name="contact_address" rows="2"><?= htmlspecialchars($s['contact_address']??'') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">WhatsApp Number</label>
                    <input type="text" class="form-control" name="contact_whatsapp" value="<?= htmlspecialchars($s['contact_whatsapp']??'') ?>" placeholder="918079021482 (with country code)">
                    <small class="text-muted">Include country code, no + sign. e.g. 918079021482</small>
                </div>
            </div>
        </div>

        <!-- Social -->
        <div class="form-card">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;"><i class="fas fa-share-alt me-2 text-danger"></i>Social Media</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label"><i class="fab fa-facebook-f me-2"></i>Facebook URL</label>
                    <input type="url" class="form-control" name="facebook_url" value="<?= htmlspecialchars($s['facebook_url']??'') ?>" placeholder="https://facebook.com/...">
                </div>
                <div class="col-md-6">
                    <label class="form-label"><i class="fab fa-instagram me-2"></i>Instagram URL</label>
                    <input type="url" class="form-control" name="instagram_url" value="<?= htmlspecialchars($s['instagram_url']??'') ?>" placeholder="https://instagram.com/...">
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3">Save Changes</h6>
            <p class="text-muted small">Changes take effect immediately on the website after saving.</p>
            <button type="submit" class="btn btn-primary-admin w-100"><i class="fas fa-save me-2"></i>Save All Settings</button>
        </div>
        <div class="form-card">
            <h6 class="fw-bold mb-3">Quick Links</h6>
            <div class="d-flex flex-column gap-2">
                <a href="<?= SITE_URL ?>" target="_blank" class="btn-edit-sm"><i class="fas fa-home me-2"></i>View Homepage</a>
                <a href="<?= SITE_URL ?>/products.php" target="_blank" class="btn-edit-sm"><i class="fas fa-boxes me-2"></i>View Products</a>
                <a href="<?= SITE_URL ?>/contact.php" target="_blank" class="btn-edit-sm"><i class="fas fa-envelope me-2"></i>View Contact</a>
            </div>
        </div>
    </div>
</div>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
