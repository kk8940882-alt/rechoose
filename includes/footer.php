<?php
$address = getSetting('contact_address', 'Kachiguda, Hyderabad - 500 027');
$phone = getSetting('contact_phone', '8079021482');
$email = getSetting('contact_email', 'jakharbhiyaram22@gmail.com');
$whatsapp = getSetting('contact_whatsapp', '918079021482');
$about = getSetting('site_about', '');
?>

<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row gy-4">
            <!-- Brand -->
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand mb-3">
                    <h4 class="text-white mb-1"><?= $siteName ?></h4>
                    <p class="text-muted small"><?= sanitize($siteTagline) ?></p>
                </div>
                <p class="footer-about"><?= sanitize($about) ?></p>
                <div class="footer-social mt-3">
                    <?php $fb=getSetting('facebook_url'); $insta=getSetting('instagram_url'); ?>
                    <?php if($fb): ?><a href="<?= $fb ?>" target="_blank"><i class="fab fa-facebook-f"></i></a><?php endif; ?>
                    <?php if($insta): ?><a href="<?= $insta ?>" target="_blank"><i class="fab fa-instagram"></i></a><?php endif; ?>
                    <a href="https://wa.me/<?= $whatsapp ?>" target="_blank"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>

            <!-- Categories -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Products</h5>
                <ul class="footer-links">
                    <?php foreach(array_slice($cats, 0, 6) as $cat): ?>
                    <li><a href="<?= SITE_URL ?>/products.php?category=<?= $cat['slug'] ?>"><?= sanitize($cat['name']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="<?= SITE_URL ?>/">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/about.php">About Us</a></li>
                    <li><a href="<?= SITE_URL ?>/products.php">All Products</a></li>
                    <li><a href="<?= SITE_URL ?>/contact.php">Contact Us</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <h5 class="footer-heading">Contact Us</h5>
                <ul class="footer-contact">
                    <li><i class="fas fa-map-marker-alt"></i><span><?= sanitize($address) ?></span></li>
                    <li><i class="fas fa-phone-alt"></i><a href="tel:<?= preg_replace('/[^0-9]/', '', $phone) ?>"><?= sanitize($phone) ?></a></li>
                    <li><i class="fas fa-envelope"></i><a href="mailto:<?= sanitize($email) ?>"><?= sanitize($email) ?></a></li>
                    <li><i class="fab fa-whatsapp"></i><a href="https://wa.me/<?= $whatsapp ?>" target="_blank">Chat on WhatsApp</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= $siteName ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0 small text-muted">Designed for beauty professionals</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- WhatsApp Float Button -->
<a href="https://wa.me/<?= $whatsapp ?>?text=Hello!%20I'm%20interested%20in%20your%20beauty%20products." 
   class="whatsapp-float" target="_blank" title="Chat on WhatsApp">
    <i class="fab fa-whatsapp"></i>
</a>

<!-- Back to Top -->
<button class="back-to-top" id="backToTop" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
