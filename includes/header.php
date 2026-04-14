<?php
require_once __DIR__ . '/config.php';
$pdo = getDB();

// Get categories for nav
$cats = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();
$siteName = getSetting('site_name', SITE_NAME);
$siteTagline = getSetting('site_tagline', 'Premium Beauty Equipment');
$contactPhone = getSetting('contact_phone', '8079021482');
$contactEmail = getSetting('contact_email', '');
$whatsapp = getSetting('contact_whatsapp', '918079021482');

// Page meta defaults
if (!isset($pageTitle)) $pageTitle = $siteName;
if (!isset($pageDesc)) $pageDesc = getSetting('meta_description', '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= SITE_URL . '/' . basename($_SERVER['PHP_SELF']) ?>">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <!-- Swiper CSS -->
    <link href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" rel="stylesheet">
    
    <!-- AOS Animation -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>/assets/css/style.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= SITE_URL ?>/assets/images/favicon.ico">
</head>
<body>

<!-- Top Bar -->
<div class="top-bar py-2 d-none d-md-block">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="me-4"><i class="fas fa-phone-alt me-1"></i> <?= sanitize($contactPhone) ?></span>
                <span><i class="fas fa-envelope me-1"></i> <?= sanitize($contactEmail) ?></span>
            </div>
            <div class="col-md-4 text-end">
                <?php $fb = getSetting('facebook_url'); $insta = getSetting('instagram_url'); ?>
                <?php if($fb): ?><a href="<?= $fb ?>" target="_blank" class="social-icon"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                <?php if($insta): ?><a href="<?= $insta ?>" target="_blank" class="social-icon"><i class="fab fa-instagram"></i></a><?php endif; ?>
                <a href="https://wa.me/<?= $whatsapp ?>" target="_blank" class="social-icon"><i class="fab fa-whatsapp"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top" id="mainNav">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="<?= SITE_URL ?>/">
            <div class="logo-icon me-2">
                <img src="<?= SITE_URL ?>/assets/images/logo.png" alt="<?= $siteName ?>" height="55" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'">
                <div class="logo-fallback" style="display:none; width:55px; height:55px; background:linear-gradient(135deg,#e31837,#003087); border-radius:50%; align-items:center; justify-content:center; color:white; font-weight:800; font-size:18px;">RA</div>
            </div>
            <div>
                <div class="brand-name"><?= $siteName ?></div>
                <div class="brand-tagline"><?= $siteTagline ?></div>
            </div>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/about.php">About Us</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="<?= SITE_URL ?>/products.php" id="productsMenu" data-bs-toggle="dropdown">Products</a>
                    <ul class="dropdown-menu mega-menu" aria-labelledby="productsMenu">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php"><i class="fas fa-th me-2"></i>All Products</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <?php foreach($cats as $cat): ?>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>">
                            <i class="fas fa-chevron-right me-2"></i><?= sanitize($cat['name']) ?></a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/contact.php">Contact</a></li>
                <li class="nav-item ms-lg-3">
                    <a class="btn btn-wa" href="https://wa.me/<?= $whatsapp ?>?text=Hello!%20I%20am%20interested%20in%20your%20products." target="_blank">
                        <i class="fab fa-whatsapp me-1"></i> WhatsApp
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
