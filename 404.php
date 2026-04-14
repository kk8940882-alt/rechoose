<?php
require_once __DIR__ . '/includes/config.php';
http_response_code(404);
$pageTitle = 'Page Not Found - ' . getSetting('site_name');
include __DIR__ . '/includes/header.php';
?>
<div class="page-header">
    <div class="container"><h1>404 - Page Not Found</h1></div>
</div>
<div class="text-center py-5 my-5">
    <div style="font-size:5rem;color:#e31837;opacity:0.3;line-height:1;">404</div>
    <h3 class="mt-3 mb-2">Oops! Page not found</h3>
    <p class="text-muted mb-4">The page you're looking for doesn't exist or has been moved.</p>
    <a href="/" class="btn btn-submit px-5 py-2 me-2"><i class="fas fa-home me-2"></i>Go Home</a>
    <a href="/products.php" class="btn btn-outline-secondary px-5 py-2">View Products</a>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
