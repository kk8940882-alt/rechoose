<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

$pageTitle = getSetting('site_name') . ' - ' . getSetting('site_tagline');
$pageDesc  = getSetting('meta_description');

// Featured products
$featured = $pdo->query("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug 
    FROM products p JOIN categories c ON p.category_id=c.id 
    WHERE p.is_featured=1 AND p.is_active=1 
    ORDER BY p.sort_order, p.id LIMIT 6")->fetchAll();

// All categories with product count
$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count 
    FROM categories c LEFT JOIN products p ON p.category_id=c.id AND p.is_active=1 
    WHERE c.is_active=1 GROUP BY c.id ORDER BY c.sort_order")->fetchAll();

// Banners
$banners = $pdo->query("SELECT * FROM banners WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$catIcons = [
    'hydrafacial-machines' => 'fa-water',
    'laser-hair-removal'   => 'fa-bolt',
    'q-switch-laser'       => 'fa-radiation',
    'skin-care-devices'    => 'fa-spa',
    'hair-care-devices'    => 'fa-cut',
    'body-slimming'        => 'fa-dumbbell',
    'beauty-products'      => 'fa-flask',
];

include __DIR__ . '/includes/header.php';
?>

<!-- HERO SLIDER -->
<div class="hero-swiper swiper">
    <div class="swiper-wrapper">
        <?php if ($banners): foreach ($banners as $i => $b): ?>
        <div class="swiper-slide">
            <div class="hero-bg slide-<?= $i+1 ?>">
                <div class="hero-overlay"></div>
                <div class="container">
                    <div class="row align-items-center py-5">
                        <div class="col-lg-6 hero-content" data-aos="fade-right">
                            <span class="hero-badge"><i class="fas fa-star me-1"></i> Premium Aesthetic Equipment</span>
                            <h1 class="hero-title"><?= sanitize($b['title']) ?></h1>
                            <p class="hero-subtitle"><?= sanitize($b['subtitle']) ?></p>
                            <a href="<?= sanitize($b['button_link']) ?>" class="btn btn-cta-white me-2">
                                <?= sanitize($b['button_text']) ?> <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                            <a href="contact.php" class="btn btn-cta-outline">Get Quote</a>
                        </div>
                        <div class="col-lg-6 text-center" data-aos="fade-left">
                            <?php if ($b['image'] && file_exists(UPLOAD_PATH . $b['image'])): ?>
                            <img src="<?= UPLOAD_URL . $b['image'] ?>" alt="<?= sanitize($b['title']) ?>" class="hero-img">
                            <?php else: ?>
                            <div style="font-size:120px; opacity:0.2; color:#fff;">
                                <i class="fas fa-spa"></i>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="swiper-slide">
            <div class="hero-bg">
                <div class="hero-overlay"></div>
                <div class="container">
                    <div class="row align-items-center py-5">
                        <div class="col-lg-7 hero-content">
                            <span class="hero-badge"><i class="fas fa-star me-1"></i> Professional Grade Equipment</span>
                            <h1 class="hero-title">Premium <span>Beauty & Aesthetic</span><br>Equipment</h1>
                            <p class="hero-subtitle">Advanced solutions for skin care, hair removal, slimming and beauty treatments. Trusted by professionals across India.</p>
                            <a href="products.php" class="btn btn-cta-white me-2">Explore Products <i class="fas fa-arrow-right ms-1"></i></a>
                            <a href="contact.php" class="btn btn-cta-outline">Get Quote</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="swiper-pagination"></div>
</div>

<!-- FEATURES STRIP -->
<div class="features-strip">
    <div class="container">
        <div class="row gy-4">
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-award"></i></div>
                    <div class="feature-title">Certified Products</div>
                    <div class="feature-text">CE & FDA approved medical grade devices</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-tools"></i></div>
                    <div class="feature-title">Full Support</div>
                    <div class="feature-text">Installation, training and after-sales service</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-shipping-fast"></i></div>
                    <div class="feature-title">Pan India Delivery</div>
                    <div class="feature-text">Fast and safe delivery to your doorstep</div>
                </div>
            </div>
            <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
                <div class="feature-item">
                    <div class="feature-icon"><i class="fas fa-headset"></i></div>
                    <div class="feature-title">Expert Guidance</div>
                    <div class="feature-text">Free consultation with product specialists</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CATEGORIES -->
<section class="categories-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Browse by Category</span>
            <h2 class="section-title">Our Product Categories</h2>
            <p class="section-subtitle">Professional aesthetic and beauty equipment for every treatment need</p>
            <div class="section-line"></div>
        </div>
        <div class="row g-3">
            <?php foreach ($categories as $i => $cat): ?>
            <div class="col-6 col-md-4 col-lg-3" data-aos="fade-up" data-aos-delay="<?= ($i % 4) * 60 ?>">
                <a href="products.php?category=<?= $cat['slug'] ?>" class="cat-card">
                    <div class="cat-icon">
                        <i class="fas <?= $catIcons[$cat['slug']] ?? 'fa-cube' ?>"></i>
                    </div>
                    <div class="cat-name"><?= sanitize($cat['name']) ?></div>
                    <div class="cat-count"><?= $cat['product_count'] ?> product<?= $cat['product_count'] != 1 ? 's' : '' ?></div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- FEATURED PRODUCTS -->
<?php if ($featured): ?>
<section class="products-section">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Top Picks</span>
            <h2 class="section-title">Featured Products</h2>
            <p class="section-subtitle">Our most popular professional beauty and aesthetic machines</p>
            <div class="section-line"></div>
        </div>
        <div class="row g-4">
            <?php foreach ($featured as $i => $p): ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 80 ?>">
                <div class="product-card">
                    <div class="product-img-wrap">
                        <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                        <img src="<?= UPLOAD_URL . $p['image'] ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
                        <?php else: ?>
                        <div class="no-img w-100"><i class="fas fa-spa"></i></div>
                        <?php endif; ?>
                        <span class="product-badge">Featured</span>
                    </div>
                    <div class="product-body">
                        <div class="product-cat"><?= sanitize($p['cat_name']) ?></div>
                        <div class="product-name"><?= sanitize($p['name']) ?></div>
                        <div class="product-desc"><?= sanitize($p['short_description']) ?></div>
                        <div class="product-footer">
                            <a href="product.php?slug=<?= $p['slug'] ?>" class="btn-view"><i class="fas fa-eye"></i> View</a>
                            <a href="contact.php?product=<?= $p['id'] ?>" class="btn-enquire"><i class="fas fa-envelope"></i> Enquire</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center mt-5" data-aos="fade-up">
            <a href="products.php" class="btn btn-submit px-5 py-3">View All Products <i class="fas fa-arrow-right ms-2"></i></a>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ABOUT STRIP -->
<section class="about-strip bg-white">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="about-img-wrap">
                    <img src="assets/images/about-beauty.jpg" alt="About RA Beauty" onerror="this.parentNode.innerHTML='<div class=\'no-img\' style=\'height:380px\'><i class=\'fas fa-spa fa-4x text-muted\'></i></div>'">
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <span class="about-badge">About Us</span>
                <h2 class="about-title">Ramdev Beauty & Aesthetic Products</h2>
                <p class="text-muted mb-4"><?= getSetting('site_about') ?></p>
                <div class="row g-3 mb-4">
                    <div class="col-4"><div class="stat-box"><div class="stat-num">500+</div><div class="stat-label">Products Sold</div></div></div>
                    <div class="col-4"><div class="stat-box"><div class="stat-num">200+</div><div class="stat-label">Happy Clients</div></div></div>
                    <div class="col-4"><div class="stat-box"><div class="stat-num">5+</div><div class="stat-label">Years Experience</div></div></div>
                </div>
                <a href="about.php" class="btn btn-submit px-4 py-2">Learn More <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section" data-aos="zoom-in">
    <div class="container">
        <h2>Ready to Upgrade Your Practice?</h2>
        <p>Contact our specialists today for personalized product recommendations and pricing.</p>
        <a href="contact.php" class="btn btn-cta-white">
            <i class="fas fa-envelope me-2"></i>Send Enquiry
        </a>
        <a href="tel:<?= preg_replace('/[^0-9]/', '', getSetting('contact_phone')) ?>" class="btn btn-cta-outline">
            <i class="fas fa-phone me-2"></i>Call Now
        </a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
