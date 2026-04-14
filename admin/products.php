<?php
$pageTitle = 'Products';
require_once __DIR__ . '/includes/header.php';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $prod = $pdo->prepare("SELECT image, image_gallery FROM products WHERE id=?");
    $prod->execute([$delId]);
    $row = $prod->fetch();
    if ($row) {
        deleteImage($row['image']);
        if ($row['image_gallery']) {
            foreach (array_filter(explode(',', $row['image_gallery'])) as $g) deleteImage($g);
        }
        $pdo->prepare("DELETE FROM products WHERE id=?")->execute([$delId]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Product deleted successfully.'];
    }
    redirect('products.php');
}

// Filters
$catFilter = trim($_GET['cat'] ?? '');
$search    = trim($_GET['q'] ?? '');
$page      = max(1,(int)($_GET['page']??1));
$perPage   = 20;
$offset    = ($page-1)*$perPage;

$where = "1=1"; $params = [];
if ($catFilter) { $where.=" AND c.slug=?"; $params[]=$catFilter; }
if ($search)    { $where.=" AND p.name LIKE ?"; $params[]="%$search%"; }

$total = $pdo->prepare("SELECT COUNT(*) FROM products p JOIN categories c ON p.category_id=c.id WHERE $where");
$total->execute($params); $totalCount=$total->fetchColumn();
$totalPages = ceil($totalCount/$perPage);

$stmt = $pdo->prepare("SELECT p.*,c.name AS cat_name,c.slug AS cat_slug FROM products p JOIN categories c ON p.category_id=c.id WHERE $where ORDER BY c.sort_order,p.sort_order,p.id LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$allCats = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

// Flash message
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-auto mb-3"><i class="fas fa-check-circle me-2"></i><?= $flash['msg'] ?></div>
<?php endif; ?>

<!-- Toolbar -->
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
    <div class="d-flex gap-2 flex-wrap">
        <form method="get" class="d-flex gap-2">
            <input type="text" class="form-control form-control-sm" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search products..." style="width:200px;">
            <select class="form-select form-select-sm" name="cat" style="width:180px;">
                <option value="">All Categories</option>
                <?php foreach($allCats as $c): ?>
                <option value="<?= $c['slug'] ?>" <?= $catFilter===$c['slug']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-secondary-admin btn-sm">Filter</button>
            <?php if($search||$catFilter): ?><a href="products.php" class="btn btn-sm" style="background:#f0f2f8;color:#555;border-radius:8px;">Clear</a><?php endif; ?>
        </form>
    </div>
    <a href="product-add.php" class="btn btn-primary-admin"><i class="fas fa-plus me-1"></i>Add Product</a>
</div>

<!-- Table -->
<div class="admin-table">
    <div class="px-4 py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold">All Products <span class="text-muted fw-normal" style="font-size:0.82rem;">(<?= $totalCount ?>)</span></h6>
    </div>
    <?php if(empty($products)): ?>
    <div class="text-center py-5 text-muted">
        <i class="fas fa-boxes fa-3x mb-3 opacity-25"></i>
        <p>No products found. <a href="product-add.php">Add your first product</a></p>
    </div>
    <?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th style="width:50px;">#</th>
                <th>Product</th>
                <th>Category</th>
                <th>Featured</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($products as $i=>$p): ?>
            <tr>
                <td class="text-muted" style="font-size:0.8rem;"><?= $offset+$i+1 ?></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($p['image'] && file_exists(UPLOAD_PATH . $p['image'])): ?>
                        <img src="<?= UPLOAD_URL . $p['image'] ?>" class="prod-thumb" alt="">
                        <?php else: ?>
                        <div class="prod-thumb d-flex align-items-center justify-content-center text-muted" style="font-size:18px;"><i class="fas fa-spa"></i></div>
                        <?php endif; ?>
                        <div>
                            <div style="font-size:0.87rem;font-weight:600;"><?= sanitize($p['name']) ?></div>
                            <div style="font-size:0.75rem;color:#aaa;"><?= sanitize(substr($p['short_description'],0,60)) ?>...</div>
                        </div>
                    </div>
                </td>
                <td><span style="font-size:0.8rem;background:#f0f2f8;padding:3px 10px;border-radius:50px;"><?= sanitize($p['cat_name']) ?></span></td>
                <td>
                    <?php if($p['is_featured']): ?>
                    <span style="color:#c8963e;font-size:0.8rem;"><i class="fas fa-star"></i> Yes</span>
                    <?php else: ?>
                    <span style="color:#ccc;font-size:0.8rem;"><i class="far fa-star"></i> No</span>
                    <?php endif; ?>
                </td>
                <td><span class="status-badge <?= $p['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $p['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="product-edit.php?id=<?= $p['id'] ?>" class="btn-edit-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                        <a href="<?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?>" target="_blank" class="btn-edit-sm"><i class="fas fa-eye"></i></a>
                        <form method="post" onsubmit="return confirmDelete(this,'Delete product &quot;<?= addslashes($p['name']) ?>&quot;?')">
                            <input type="hidden" name="delete_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn-danger-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php if($totalPages > 1): ?>
    <div class="px-4 py-3 border-top">
        <nav><ul class="pagination pagination-sm mb-0">
            <?php if($page>1): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>">Prev</a></li><?php endif; ?>
            <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$i])) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if($page<$totalPages): ?><li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>">Next</a></li><?php endif; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
