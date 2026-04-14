<?php
$pageTitle = 'Categories';
require_once __DIR__ . '/includes/header.php';

$errors = []; $success = '';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $count = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id=?");
    $count->execute([$delId]); $cnt = $count->fetchColumn();
    if ($cnt > 0) {
        $errors[] = "Cannot delete: This category has $cnt product(s). Remove products first.";
    } else {
        $cat = $pdo->prepare("SELECT image FROM categories WHERE id=?");
        $cat->execute([$delId]); $row = $cat->fetch();
        if ($row) deleteImage($row['image']);
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$delId]);
        $success = 'Category deleted.';
    }
}

// Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cat'])) {
    $catId   = (int)($_POST['cat_id'] ?? 0);
    $name    = trim($_POST['cat_name'] ?? '');
    $desc    = trim($_POST['cat_desc'] ?? '');
    $sort    = (int)($_POST['cat_sort'] ?? 0);
    $active  = isset($_POST['cat_active']) ? 1 : 0;

    if (!$name) $errors[] = 'Category name is required.';
    if (empty($errors)) {
        $slugBase = slug($name); $slugFinal = $slugBase; $n = 1;
        while ($pdo->query("SELECT id FROM categories WHERE slug='$slugFinal'" . ($catId ? " AND id!=$catId" : ""))->fetchColumn()) {
            $slugFinal = $slugBase . '-' . $n++;
        }
        $image = null;
        if (!empty($_FILES['cat_image']['tmp_name'])) {
            $image = uploadImage($_FILES['cat_image'], 'cat');
        }
        if ($catId) {
            $existImg = $pdo->prepare("SELECT image FROM categories WHERE id=?")->execute([$catId]) ? $pdo->query("SELECT image FROM categories WHERE id=$catId")->fetchColumn() : null;
            $imgToSave = $image ?? $existImg;
            if ($image && $existImg) deleteImage($existImg);
            $pdo->prepare("UPDATE categories SET name=?,slug=?,description=?,image=?,sort_order=?,is_active=? WHERE id=?")
                ->execute([$name,$slugFinal,$desc,$imgToSave,$sort,$active,$catId]);
            $success = 'Category updated.';
        } else {
            $pdo->prepare("INSERT INTO categories (name,slug,description,image,sort_order,is_active) VALUES (?,?,?,?,?,?)")
                ->execute([$name,$slugFinal,$desc,$image,$sort,$active]);
            $success = 'Category added.';
        }
    }
}

$categories = $pdo->query("SELECT c.*, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON p.category_id=c.id GROUP BY c.id ORDER BY c.sort_order")->fetchAll();
$editCat = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM categories WHERE id=?"); $s->execute([(int)$_GET['edit']]); $editCat = $s->fetch();
}
?>

<?php if ($success): ?><div class="alert alert-success alert-auto mb-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div><?php endif; ?>
<?php if (!empty($errors)): ?><div class="alert alert-danger mb-3"><?php foreach($errors as $e): ?><div><?= $e ?></div><?php endforeach; ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Add / Edit Form -->
    <div class="col-lg-4">
        <div class="form-card">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;">
                <?= $editCat ? 'Edit Category' : 'Add New Category' ?>
            </h6>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="save_cat" value="1">
                <?php if ($editCat): ?><input type="hidden" name="cat_id" value="<?= $editCat['id'] ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="cat_name" value="<?= htmlspecialchars($editCat['name'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="cat_desc" rows="3"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category Image</label>
                    <?php if ($editCat && $editCat['image'] && file_exists(UPLOAD_PATH.$editCat['image'])): ?>
                    <img src="<?= UPLOAD_URL.$editCat['image'] ?>" style="width:100%;border-radius:8px;margin-bottom:8px;" alt="">
                    <?php endif; ?>
                    <input type="file" class="form-control" name="cat_image" accept="image/*">
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="cat_sort" value="<?= $editCat['sort_order'] ?? 0 ?>" min="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="cat_active" value="1" id="catActive" <?= (!$editCat || $editCat['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="catActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-admin flex-1 w-100">
                        <i class="fas fa-save me-1"></i><?= $editCat ? 'Update' : 'Add Category' ?>
                    </button>
                    <?php if ($editCat): ?><a href="categories.php" class="btn btn-sm" style="background:#f0f2f8;color:#555;border-radius:8px;white-space:nowrap;">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="col-lg-8">
        <div class="admin-table">
            <div class="px-4 py-3 border-bottom">
                <h6 class="mb-0 fw-bold">All Categories (<?= count($categories) ?>)</h6>
            </div>
            <table class="table">
                <thead><tr><th>#</th><th>Category</th><th>Products</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($categories as $i=>$cat): ?>
                    <tr>
                        <td class="text-muted small"><?= $i+1 ?></td>
                        <td>
                            <div class="fw-600" style="font-size:0.87rem;"><?= sanitize($cat['name']) ?></div>
                            <div class="text-muted" style="font-size:0.75rem;"><?= sanitize(substr($cat['description'],0,50)) ?></div>
                        </td>
                        <td><span style="font-size:0.8rem;background:#f0f2f8;padding:3px 10px;border-radius:50px;"><?= $cat['product_count'] ?></span></td>
                        <td><span class="status-badge <?= $cat['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="categories.php?edit=<?= $cat['id'] ?>" class="btn-edit-sm"><i class="fas fa-edit me-1"></i>Edit</a>
                                <?php if($cat['product_count'] == 0): ?>
                                <form method="post" onsubmit="return confirmDelete(this,'Delete category &quot;<?= addslashes($cat['name']) ?>&quot;?')">
                                    <input type="hidden" name="delete_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn-danger-sm"><i class="fas fa-trash"></i></button>
                                </form>
                                <?php else: ?>
                                <span class="btn-danger-sm" title="Has products — cannot delete" style="opacity:0.4;cursor:not-allowed;"><i class="fas fa-trash"></i></span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
