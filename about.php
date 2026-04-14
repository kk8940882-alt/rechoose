<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

$pageTitle = 'About Us - ' . getSetting('site_name');
include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1>About Us</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item active">About Us</li>
            </ol>
        </nav>
    </div>
</div>

<!-- About Section -->
<section class="py-5">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5" data-aos="fade-right">
                <div class="about-img-wrap">
                    <img src="assets/images/about-beauty.jpg" alt="About RA Beauty"
                         style="width:100%;height:420px;object-fit:cover;"
                         onerror="this.parentNode.innerHTML='<div style=\'height:420px;background:linear-gradient(135deg,#003087,#e31837);display:flex;align-items:center;justify-content:center;\'><i class=\'fas fa-spa fa-5x text-white opacity-50\'></i></div>'">
                </div>
            </div>
            <div class="col-lg-7" data-aos="fade-left">
                <span class="about-badge">Who We Are</span>
                <h2 class="about-title">Ramdev Beauty &<br>Aesthetic Products</h2>
                <p class="text-muted mb-3">
                    We are a leading importer and marketer of professional beauty and aesthetic equipment, based in Kachiguda, Hyderabad. 
                    With years of experience in the industry, we supply high-quality machines to clinics, salons, spas and hospitals across India.
                </p>
                <p class="text-muted mb-4">
                    Our product range covers the full spectrum of aesthetic treatments including HydraFacial machines, diode laser hair removal, 
                    Q-switch laser systems, skin care devices, slimming equipment and professional beauty care products.
                </p>
                <div class="row g-3 mb-4">
                    <div class="col-4"><div class="stat-box"><div class="stat-num">500+</div><div class="stat-label">Products Sold</div></div></div>
                    <div class="col-4"><div class="stat-box"><div class="stat-num">200+</div><div class="stat-label">Happy Clients</div></div></div>
                    <div class="col-4"><div class="stat-box"><div class="stat-num">5+</div><div class="stat-label">Years Exp.</div></div></div>
                </div>
                <a href="contact.php" class="btn btn-submit px-4 py-2">Get In Touch <i class="fas fa-arrow-right ms-2"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose Us -->
<section class="py-5" style="background:var(--light-bg);">
    <div class="container">
        <div class="section-header" data-aos="fade-up">
            <span class="section-badge">Why Choose Us</span>
            <h2 class="section-title">Our Commitment to You</h2>
            <div class="section-line"></div>
        </div>
        <div class="row g-4">
            <?php
            $whys = [
                ['fas fa-certificate','Certified Equipment','All our products are CE & ISO certified, ensuring the highest standards of safety and quality.'],
                ['fas fa-tools','Installation & Training','We provide full installation support and hands-on training for all equipment we supply.'],
                ['fas fa-shield-alt','Warranty & Support','Comprehensive warranty coverage and dedicated after-sales technical support.'],
                ['fas fa-shipping-fast','Pan India Delivery','Fast, insured delivery to any city in India with careful packaging.'],
                ['fas fa-headset','Expert Consultation','Our specialists help you choose the right equipment for your clinic or salon needs.'],
                ['fas fa-rupee-sign','Competitive Pricing','Best prices in the market with flexible payment options for your business.'],
            ];
            foreach($whys as $i => $w):
            ?>
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?= ($i%3)*80 ?>">
                <div class="card border-0 shadow-sm h-100 p-4" style="border-radius:14px;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform=''">
                    <div style="width:52px;height:52px;background:linear-gradient(135deg,rgba(227,24,55,0.1),rgba(0,48,135,0.1));border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:16px;">
                        <i class="fas <?= $w[0] ?>" style="color:var(--primary);font-size:20px;"></i>
                    </div>
                    <h5 class="fw-bold mb-2" style="font-size:1rem;"><?= $w[1] ?></h5>
                    <p class="text-muted small mb-0"><?= $w[2] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="cta-section">
    <div class="container">
        <h2>Ready to Work With Us?</h2>
        <p>Contact us today for product demos, pricing and installation support.</p>
        <a href="contact.php" class="btn btn-cta-white me-2"><i class="fas fa-envelope me-2"></i>Contact Us</a>
        <a href="products.php" class="btn btn-cta-outline"><i class="fas fa-boxes me-2"></i>View Products</a>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
