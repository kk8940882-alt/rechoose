<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

$slug = trim($_GET['slug'] ?? '');
if (!$slug) { redirect('products.php'); }

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug 
    FROM products p JOIN categories c ON p.category_id=c.id 
    WHERE p.slug=? AND p.is_active=1");
$stmt->execute([$slug]);
$product = $stmt->fetch();
if (!$product) { header("HTTP/1.0 404 Not Found"); die("Product not found."); }

// Parse features & specs
$features = $product['features'] ? explode('||', $product['features']) : [];
$specs    = [];
if ($product['specifications']) {
    foreach (explode('||', $product['specifications']) as $s) {
        $parts = explode(':', $s, 2);
        if (count($parts) === 2) $specs[trim($parts[0])] = trim($parts[1]);
    }
}

// Gallery
$gallery = $product['image_gallery'] ? array_filter(explode(',', $product['image_gallery'])) : [];

// Related products
$related = $pdo->prepare("SELECT p.*, c.name AS cat_name FROM products p 
    JOIN categories c ON p.category_id=c.id
    WHERE p.category_id=? AND p.id!=? AND p.is_active=1 LIMIT 4");
$related->execute([$product['category_id'], $product['id']]);
$relatedProducts = $related->fetchAll();

$pageTitle = sanitize($product['name']) . ' - ' . getSetting('site_name');
$pageDesc  = sanitize($product['short_description']);

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= sanitize($product['name']) ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item"><a href="products.php">Products</a></li>
                <li class="breadcrumb-item"><a href="products.php?category=<?= $product['cat_slug'] ?>"><?= sanitize($product['cat_name']) ?></a></li>
                <li class="breadcrumb-item active"><?= sanitize($product['name']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row g-5">
            <!-- Images -->
            <div class="col-lg-5">
                <div class="product-detail-img mb-3" data-aos="fade-right">
                    <?php $mainImg = $product['image'] && file_exists(UPLOAD_PATH . $product['image']) ? UPLOAD_URL . $product['image'] : null; ?>
                    <?php if ($mainImg): ?>
                    <img id="mainProductImg" src="<?= $mainImg ?>" alt="<?= sanitize($product['name']) ?>" class="w-100">
                    <?php else: ?>
                    <div class="no-img" style="min-height:340px;"><i class="fas fa-spa fa-4x"></i></div>
                    <?php endif; ?>
                </div>
                <!-- Gallery thumbs -->
                <?php if (!empty($gallery)): ?>
                <div class="d-flex gap-2 flex-wrap mt-2">
                    <?php if ($mainImg): ?>
                    <img src="<?= $mainImg ?>" class="thumb-img active" style="width:72px;height:72px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid #e31837;" alt="">
                    <?php endif; ?>
                    <?php foreach ($gallery as $g): ?>
                    <?php if (file_exists(UPLOAD_PATH . $g)): ?>
                    <img src="<?= UPLOAD_URL . $g ?>" class="thumb-img" style="width:72px;height:72px;object-fit:cover;border-radius:8px;cursor:pointer;border:2px solid #e5e7ef;" alt="">
                    <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Details -->
            <div class="col-lg-7" data-aos="fade-left">
                <span class="section-badge mb-2"><?= sanitize($product['cat_name']) ?></span>
                <h1 style="font-family:'Playfair Display',serif; font-size:2rem; font-weight:700; color:#1a1a2e;"><?= sanitize($product['name']) ?></h1>
                <p class="text-muted mt-2 mb-4"><?= sanitize($product['short_description']) ?></p>

                <!-- Description -->
                <?php if ($product['description']): ?>
                <p style="font-size:0.92rem; color:#555; line-height:1.8;"><?= nl2br(sanitize($product['description'])) ?></p>
                <?php endif; ?>

                <!-- Features -->
                <?php if (!empty($features)): ?>
                <div class="mt-4">
                    <h5 class="fw-bold mb-3" style="color:#1a1a2e;"><i class="fas fa-check-circle text-danger me-2"></i>Key Features</h5>
                    <ul class="features-list">
                        <?php foreach ($features as $f): if(trim($f)): ?>
                        <li><?= sanitize(trim($f)) ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="d-flex gap-3 mt-4 flex-wrap">
                    <a href="contact.php?product=<?= $product['id'] ?>" class="btn btn-submit px-4 py-2">
                        <i class="fas fa-envelope me-2"></i>Send Enquiry
                    </a>
                    <a href="https://wa.me/<?= getSetting('contact_whatsapp') ?>?text=Hello!%20I%20am%20interested%20in%20<?= urlencode($product['name']) ?>" 
                       class="btn btn-wa px-4 py-2" target="_blank">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp
                    </a>
                    <a href="tel:<?= preg_replace('/[^0-9]/', '', getSetting('contact_phone')) ?>" class="btn btn-outline-secondary px-4 py-2">
                        <i class="fas fa-phone me-2"></i>Call Us
                    </a>
                </div>
            </div>
        </div>

        <!-- Specifications -->
        <?php if (!empty($specs)): ?>
        <div class="mt-5" data-aos="fade-up">
            <h4 class="fw-bold mb-4" style="color:#1a1a2e; font-family:'Playfair Display',serif;">Technical Specifications</h4>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <table class="specs-table">
                                <?php foreach ($specs as $k => $v): ?>
                                <tr>
                                    <th><?= sanitize($k) ?></th>
                                    <td><?= sanitize($v) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Enquiry box -->
                <div class="col-lg-4">
                    <div class="enquiry-box">
                        <h5 class="fw-bold mb-3">Quick Enquiry</h5>
                        <p class="text-muted small mb-3">Interested in this product? Send us your details and we'll get back to you shortly.</p>
                        <a href="contact.php?product=<?= $product['id'] ?>" class="btn btn-submit w-100 mb-2">
                            <i class="fas fa-paper-plane me-2"></i>Send Enquiry
                        </a>
                        <a href="https://wa.me/<?= getSetting('contact_whatsapp') ?>?text=Hi!%20I%20want%20details%20about%20<?= urlencode($product['name']) ?>" 
                           class="btn btn-wa w-100" target="_blank">
                            <i class="fab fa-whatsapp me-2"></i>Chat on WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="mt-5 pt-3 border-top" data-aos="fade-up">
            <h4 class="fw-bold mb-4" style="font-family:'Playfair Display',serif; color:#1a1a2e;">Related Products</h4>
            <div class="row g-4">
                <?php foreach ($relatedProducts as $r): ?>
                <div class="col-sm-6 col-md-4 col-lg-3">
                    <div class="product-card">
                        <div class="product-img-wrap" style="aspect-ratio:1/1;">
                            <?php if ($r['image'] && file_exists(UPLOAD_PATH . $r['image'])): ?>
                            <img src="<?= UPLOAD_URL . $r['image'] ?>" alt="<?= sanitize($r['name']) ?>" loading="lazy">
                            <?php else: ?>
                            <div class="no-img w-100" style="min-height:140px;"><i class="fas fa-spa"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="product-body">
                            <div class="product-name" style="font-size:0.9rem;"><?= sanitize($r['name']) ?></div>
                            <div class="product-footer mt-auto">
                                <a href="product.php?slug=<?= $r['slug'] ?>" class="btn-view w-100"><i class="fas fa-eye"></i> View</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
