<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Stats
$totalProducts  = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$activeProducts = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
$totalCats      = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active=1")->fetchColumn();
$totalInquiries = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$unreadInq      = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_read=0")->fetchColumn();
$featuredCount  = $pdo->query("SELECT COUNT(*) FROM products WHERE is_featured=1 AND is_active=1")->fetchColumn();

// Recent inquiries
$recentInq = $pdo->query("SELECT * FROM inquiries ORDER BY created_at DESC LIMIT 6")->fetchAll();

// Recent products
$recentProds = $pdo->query("SELECT p.*, c.name AS cat_name FROM products p JOIN categories c ON p.category_id=c.id ORDER BY p.created_at DESC LIMIT 5")->fetchAll();
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:rgba(227,24,55,0.1);">
                    <i class="fas fa-boxes" style="color:#e31837;"></i>
                </div>
                <span class="badge" style="background:rgba(227,24,55,0.08);color:#e31837;font-size:0.72rem;">Total</span>
            </div>
            <div class="stat-num"><?= $totalProducts ?></div>
            <div class="stat-label">Products</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:rgba(0,48,135,0.1);">
                    <i class="fas fa-tags" style="color:#003087;"></i>
                </div>
                <span class="badge" style="background:rgba(0,48,135,0.08);color:#003087;font-size:0.72rem;">Active</span>
            </div>
            <div class="stat-num"><?= $totalCats ?></div>
            <div class="stat-label">Categories</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content: between mb-3">
                <div class="stat-icon" style="background:rgba(25,195,125,0.1);">
                    <i class="fas fa-envelope" style="color:#19c37d;"></i>
                </div>
                <?php if($unreadInq): ?><span class="badge bg-danger" style="font-size:0.72rem;"><?= $unreadInq ?> new</span><?php endif; ?>
            </div>
            <div class="stat-num"><?= $totalInquiries ?></div>
            <div class="stat-label">Inquiries</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="stat-icon" style="background:rgba(200,150,62,0.12);">
                    <i class="fas fa-star" style="color:#c8963e;"></i>
                </div>
                <span class="badge" style="background:rgba(200,150,62,0.1);color:#c8963e;font-size:0.72rem;">Featured</span>
            </div>
            <div class="stat-num"><?= $featuredCount ?></div>
            <div class="stat-label">Featured Products</div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="form-card py-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="fw-bold" style="font-size:0.9rem;">Quick Actions:</span>
                <a href="product-add.php" class="btn btn-primary-admin btn-sm"><i class="fas fa-plus me-1"></i>Add Product</a>
                <a href="categories.php" class="btn btn-secondary-admin btn-sm"><i class="fas fa-folder-plus me-1"></i>Categories</a>
                <a href="banners.php" class="btn btn-secondary-admin btn-sm"><i class="fas fa-image me-1"></i>Banners</a>
                <a href="settings.php" class="btn btn-secondary-admin btn-sm"><i class="fas fa-cog me-1"></i>Settings</a>
                <a href="<?= SITE_URL ?>" target="_blank" class="btn btn-sm" style="background:#f0f2f8;color:#333;border-radius:8px;font-size:0.87rem;"><i class="fas fa-external-link-alt me-1"></i>View Website</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Products -->
    <div class="col-lg-6">
        <div class="admin-table">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold">Recent Products</h6>
                <a href="products.php" class="btn-edit-sm">View All</a>
            </div>
            <table class="table mb-0">
                <thead>
                    <tr><th>Product</th><th>Category</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProds as $p): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <?php if ($p['image'] && file_exists(__DIR__ . '/../../uploads/products/' . $p['image'])): ?>
                                <img src="<?= UPLOAD_URL . $p['image'] ?>" class="prod-thumb" alt="">
                                <?php else: ?>
                                <div class="prod-thumb d-flex align-items-center justify-content-center text-muted" style="font-size:16px;"><i class="fas fa-spa"></i></div>
                                <?php endif; ?>
                                <span style="font-size:0.84rem;font-weight:500;"><?= sanitize($p['name']) ?></span>
                            </div>
                        </td>
                        <td><span style="font-size:0.78rem;color:#888;"><?= sanitize($p['cat_name']) ?></span></td>
                        <td><span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                        <td><a href="product-edit.php?id=<?= $p['id'] ?>" class="btn-edit-sm"><i class="fas fa-edit"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Inquiries -->
    <div class="col-lg-6">
        <div class="admin-table">
            <div class="d-flex align-items-center justify-content-between px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold">Recent Inquiries <?php if($unreadInq): ?><span class="badge bg-danger ms-1"><?= $unreadInq ?></span><?php endif; ?></h6>
                <a href="inquiries.php" class="btn-edit-sm">View All</a>
            </div>
            <table class="table mb-0">
                <thead>
                    <tr><th>Name</th><th>Subject</th><th>Date</th><th></th></tr>
                </thead>
                <tbody>
                    <?php if (empty($recentInq)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4"><i class="fas fa-inbox me-2"></i>No inquiries yet</td></tr>
                    <?php endif; ?>
                    <?php foreach ($recentInq as $inq): ?>
                    <tr <?= !$inq['is_read'] ? 'style="background:#fffbf0;"' : '' ?>>
                        <td>
                            <div style="font-size:0.84rem;font-weight:<?= $inq['is_read'] ? '400' : '600' ?>;"><?= sanitize($inq['name']) ?></div>
                            <div style="font-size:0.75rem;color:#888;"><?= sanitize($inq['email']) ?></div>
                        </td>
                        <td style="font-size:0.8rem;color:#666;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= sanitize($inq['subject'] ?: 'General') ?></td>
                        <td style="font-size:0.75rem;color:#aaa;"><?= date('d M', strtotime($inq['created_at'])) ?></td>
                        <td><a href="inquiries.php?view=<?= $inq['id'] ?>" class="btn-edit-sm"><i class="fas fa-eye"></i></a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
