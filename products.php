<?php
require_once __DIR__ . '/includes/config.php';
$pdo = getDB();

// Get filter params
$catSlug  = isset($_GET['category']) ? trim($_GET['category']) : '';
$search   = isset($_GET['q']) ? trim($_GET['q']) : '';
$page     = max(1, (int)($_GET['page'] ?? 1));
$perPage  = 12;
$offset   = ($page - 1) * $perPage;

// Current category
$currentCat = null;
if ($catSlug) {
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE slug=? AND is_active=1");
    $stmt->execute([$catSlug]);
    $currentCat = $stmt->fetch();
}

// Build query
$where = "p.is_active=1";
$params = [];
if ($currentCat) { $where .= " AND p.category_id=?"; $params[] = $currentCat['id']; }
if ($search)     { $where .= " AND (p.name LIKE ? OR p.short_description LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$total = $pdo->prepare("SELECT COUNT(*) FROM products p WHERE $where");
$total->execute($params);
$totalCount = $total->fetchColumn();
$totalPages = ceil($totalCount / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.name AS cat_name, c.slug AS cat_slug 
    FROM products p JOIN categories c ON p.category_id=c.id 
    WHERE $where ORDER BY p.sort_order, p.id LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$allCats = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

$pageTitle = ($currentCat ? $currentCat['name'] . ' - ' : '') . 'Products - ' . getSetting('site_name');
$pageDesc  = $currentCat ? $currentCat['description'] : getSetting('meta_description');

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
    <div class="container">
        <h1><?= $currentCat ? sanitize($currentCat['name']) : ($search ? 'Search: ' . sanitize($search) : 'All Products') ?></h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Home</a></li>
                <li class="breadcrumb-item <?= !$currentCat && !$search ? 'active' : '' ?>">
                    <?php if ($currentCat || $search): ?><a href="products.php">Products</a><?php else: ?>Products<?php endif; ?>
                </li>
                <?php if ($currentCat): ?><li class="breadcrumb-item active"><?= sanitize($currentCat['name']) ?></li><?php endif; ?>
                <?php if ($search): ?><li class="breadcrumb-item active">Search results</li><?php endif; ?>
            </ol>
        </nav>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Sidebar -->
            <div class="col-lg-3">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-0">
                        <div class="p-3 border-bottom">
                            <h6 class="fw-bold mb-0"><i class="fas fa-filter me-2 text-danger"></i>Filter Products</h6>
                        </div>
                        <!-- Search -->
                        <div class="p-3 border-bottom">
                            <form method="get" action="products.php">
                                <?php if ($catSlug): ?><input type="hidden" name="category" value="<?= sanitize($catSlug) ?>"><?php endif; ?>
                                <div class="input-group input-group-sm">
                                    <input type="text" class="form-control" name="q" value="<?= sanitize($search) ?>" placeholder="Search products...">
                                    <button class="btn btn-danger" type="submit"><i class="fas fa-search"></i></button>
                                </div>
                            </form>
                        </div>
                        <!-- Categories -->
                        <div class="p-3">
                            <p class="text-muted small fw-bold mb-2">CATEGORIES</p>
                            <ul class="list-unstyled mb-0">
                                <li class="mb-1">
                                    <a href="products.php" class="d-flex justify-content-between align-items-center text-decoration-none py-1 px-2 rounded <?= !$catSlug ? 'bg-danger text-white' : 'text-dark hover-red' ?>" style="font-size:0.87rem;">
                                        <span>All Products</span>
                                        <span class="badge <?= !$catSlug ? 'bg-white text-danger' : 'bg-light text-muted' ?>">
                                            <?= $pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn() ?>
                                        </span>
                                    </a>
                                </li>
                                <?php foreach ($allCats as $cat):
                                    $cnt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=? AND is_active=1");
                                    $cnt->execute([$cat['id']]); $c = $cnt->fetchColumn();
                                ?>
                                <li class="mb-1">
                                    <a href="products.php?category=<?= $cat['slug'] ?>" class="d-flex justify-content-between align-items-center text-decoration-none py-1 px-2 rounded <?= $catSlug === $cat['slug'] ? 'bg-danger text-white' : 'text-dark' ?>" style="font-size:0.87rem; transition:all 0.2s;">
                                        <span><?= sanitize($cat['name']) ?></span>
                                        <span class="badge <?= $catSlug === $cat['slug'] ? 'bg-white text-danger' : 'bg-light text-muted' ?>"><?= $c ?></span>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- CTA Box -->
                <div class="card border-0" style="background:linear-gradient(135deg,#e31837,#003087); border-radius:14px;">
                    <div class="card-body text-white text-center p-4">
                        <i class="fas fa-headset fa-2x mb-3 opacity-75"></i>
                        <h6 class="fw-bold">Need Help Choosing?</h6>
                        <p style="font-size:0.82rem; opacity:0.85;">Our experts are ready to assist you find the perfect equipment.</p>
                        <a href="contact.php" class="btn btn-sm btn-light fw-bold text-danger rounded-pill px-3">Contact Us</a>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results bar -->
                <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                    <p class="mb-0 text-muted small">
                        Showing <strong><?= count($products) ?></strong> of <strong><?= $totalCount ?></strong> products
                        <?php if ($search): ?> for "<strong><?= sanitize($search) ?></strong>"<?php endif; ?>
                    </p>
                    <?php if ($search || $catSlug): ?>
                    <a href="products.php" class="btn btn-sm btn-outline-secondary rounded-pill"><i class="fas fa-times me-1"></i>Clear Filter</a>
                    <?php endif; ?>
                </div>

                <?php if (empty($products)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No products found</h5>
                    <p class="text-muted">Try a different search term or browse all categories.</p>
                    <a href="products.php" class="btn btn-danger rounded-pill px-4 mt-2">View All Products</a>
                </div>
                <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($products as $i => $p): ?>
                    <div class="col-sm-6 col-xl-4" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 60 ?>">
                        <div class="product-card">
                            <div class="product-img-wrap">
                                <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                                <img src="<?= UPLOAD_URL . $p['image'] ?>" alt="<?= sanitize($p['name']) ?>" loading="lazy">
                                <?php else: ?>
                                <div class="no-img w-100" style="min-height:180px;"><i class="fas fa-spa"></i></div>
                                <?php endif; ?>
                                <?php if ($p['is_featured']): ?><span class="product-badge">Featured</span><?php endif; ?>
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

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        <?php for ($i = max(1, $page-2); $i <= min($totalPages, $page+2); $i++): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
