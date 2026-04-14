<?php
$pageTitle = 'Edit Product';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { redirect('products.php'); }

$product = $pdo->prepare("SELECT * FROM products WHERE id=?");
$product->execute([$id]);
$product = $product->fetch();
if (!$product) { redirect('products.php'); }

$errors = [];
$allCats = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

// Convert stored || back to newlines for display
$featuresDisplay = str_replace('||', "\n", $product['features'] ?? '');
$specsDisplay    = str_replace('||', "\n", $product['specifications'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name      = trim($_POST['name'] ?? '');
    $catId     = (int)($_POST['category_id'] ?? 0);
    $shortDesc = trim($_POST['short_description'] ?? '');
    $desc      = trim($_POST['description'] ?? '');
    $features  = trim($_POST['features'] ?? '');
    $specs     = trim($_POST['specifications'] ?? '');
    $featured  = isset($_POST['is_featured']) ? 1 : 0;
    $active    = isset($_POST['is_active']) ? 1 : 0;
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    if (!$name)  $errors[] = 'Product name is required.';
    if (!$catId) $errors[] = 'Please select a category.';

    if (empty($errors)) {
        // New slug if name changed
        $slugFinal = $product['slug'];
        if ($name !== $product['name']) {
            $slugBase = slug($name); $slugFinal = $slugBase; $n = 1;
            while ($pdo->query("SELECT id FROM products WHERE slug='$slugFinal' AND id!=$id")->fetchColumn()) {
                $slugFinal = $slugBase . '-' . $n++;
            }
        }

        // New main image
        $image = $product['image'];
        if (!empty($_FILES['image']['tmp_name'])) {
            $newImg = uploadImage($_FILES['image'], 'prod');
            if ($newImg) {
                deleteImage($image);
                $image = $newImg;
            } else {
                $errors[] = 'Invalid image file.';
            }
        }

        // Delete existing image if checked
        if (isset($_POST['delete_image'])) {
            deleteImage($image);
            $image = null;
        }

        // Gallery additions
        $gallery = array_filter(explode(',', $product['image_gallery'] ?? ''));
        // Remove individual gallery items
        if (!empty($_POST['delete_gallery'])) {
            foreach ($_POST['delete_gallery'] as $delG) {
                deleteImage($delG);
                $gallery = array_diff($gallery, [$delG]);
            }
        }
        if (!empty($_FILES['gallery']['tmp_name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $k => $tmp) {
                if ($tmp) {
                    $gFile = ['name'=>$_FILES['gallery']['name'][$k],'tmp_name'=>$tmp,'size'=>$_FILES['gallery']['size'][$k],'error'=>$_FILES['gallery']['error'][$k],'type'=>$_FILES['gallery']['type'][$k]];
                    $gImg = uploadImage($gFile, 'gal');
                    if ($gImg) $gallery[] = $gImg;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("UPDATE products SET category_id=?,name=?,slug=?,short_description=?,description=?,features=?,specifications=?,image=?,image_gallery=?,is_featured=?,is_active=?,sort_order=?,updated_at=NOW() WHERE id=?");
            $stmt->execute([$catId,$name,$slugFinal,$shortDesc,$desc,$features,$specs,$image,implode(',',array_filter($gallery)),$featured,$active,$sortOrder,$id]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Product updated successfully!'];
            redirect('products.php');
        }
    }

    // Repopulate display
    $featuresDisplay = str_replace('||', "\n", $features);
    $specsDisplay    = str_replace('||', "\n", $specs);
}

$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
$gallery = array_filter(explode(',', $product['image_gallery'] ?? ''));
?>

<?php if ($flash): ?>
<div class="alert alert-<?= $flash['type'] ?> alert-auto mb-3"><?= $flash['msg'] ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-3"><?php foreach($errors as $e): ?><div><i class="fas fa-exclamation-circle me-2"></i><?= $e ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="products.php" class="btn-edit-sm"><i class="fas fa-arrow-left me-1"></i>Back to Products</a>
    <a href="<?= SITE_URL ?>/product.php?slug=<?= $product['slug'] ?>" target="_blank" class="btn-edit-sm"><i class="fas fa-eye me-1"></i>View on Website</a>
</div>

<form method="post" enctype="multipart/form-data">
<div class="row g-4">
    <div class="col-lg-8">
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-4" style="color:#1a1a2e;border-bottom:1px solid #f0f2f8;padding-bottom:12px;">Product Information</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name'] ?? $product['name']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="category_id" required>
                        <?php foreach($allCats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_POST['category_id'] ?? $product['category_id'])==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="sort_order" value="<?= (int)($_POST['sort_order'] ?? $product['sort_order']) ?>" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Short Description</label>
                    <textarea class="form-control" name="short_description" rows="2"><?= htmlspecialchars($_POST['short_description'] ?? $product['short_description']) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Full Description</label>
                    <textarea class="form-control" name="description" rows="4"><?= htmlspecialchars($_POST['description'] ?? $product['description']) ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3">Key Features</h6>
            <p class="text-muted small mb-2">One feature per line.</p>
            <textarea class="form-control" name="features" rows="8"><?= htmlspecialchars($featuresDisplay) ?></textarea>
        </div>

        <div class="form-card">
            <h6 class="fw-bold mb-3">Technical Specifications</h6>
            <p class="text-muted small mb-2">Format: <code>Label: Value</code> one per line.</p>
            <textarea class="form-control" name="specifications" rows="6"><?= htmlspecialchars($specsDisplay) ?></textarea>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Publish -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3">Publish</h6>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= ($_POST['is_active'] ?? $product['is_active']) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="isActive">Active (visible on website)</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" <?= ($_POST['is_featured'] ?? $product['is_featured']) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="isFeatured">Featured on homepage</label>
            </div>
            <button type="submit" class="btn btn-primary-admin w-100 mb-2"><i class="fas fa-save me-2"></i>Update Product</button>
        </div>

        <!-- Current Image -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3">Main Product Image</h6>
            <?php if ($product['image'] && file_exists(UPLOAD_PATH . $product['image'])): ?>
            <img src="<?= UPLOAD_URL . $product['image'] ?>" style="width:100%;border-radius:8px;margin-bottom:10px;" alt="">
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="delete_image" id="delImg" value="1">
                <label class="form-check-label small text-danger" for="delImg">Remove current image</label>
            </div>
            <?php else: ?>
            <div style="background:#f4f6fb;border-radius:8px;height:120px;display:flex;align-items:center;justify-content:center;color:#ccc;margin-bottom:10px;"><i class="fas fa-image fa-2x"></i></div>
            <?php endif; ?>
            <label class="form-label small">Upload New Image</label>
            <input type="file" class="form-control" name="image" accept="image/*">
            <small class="text-muted">JPG, PNG, WEBP — max 5MB</small>
        </div>

        <!-- Gallery -->
        <div class="form-card">
            <h6 class="fw-bold mb-3">Image Gallery</h6>
            <?php if (!empty($gallery)): ?>
            <div class="d-flex flex-wrap gap-2 mb-3">
                <?php foreach($gallery as $g): ?>
                <?php if(file_exists(UPLOAD_PATH.$g)): ?>
                <div class="position-relative">
                    <img src="<?= UPLOAD_URL.$g ?>" style="width:70px;height:70px;object-fit:cover;border-radius:6px;" alt="">
                    <div class="form-check" style="margin-top:2px;">
                        <input class="form-check-input" type="checkbox" name="delete_gallery[]" value="<?= htmlspecialchars($g) ?>">
                        <label class="form-check-label" style="font-size:0.68rem;color:#e31837;">Remove</label>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <label class="form-label small">Add More Images</label>
            <input type="file" class="form-control" name="gallery[]" accept="image/*" multiple>
        </div>
    </div>
</div>
</form>

<script>
document.querySelector('form').addEventListener('submit', function() {
    ['features','specifications'].forEach(name => {
        const el = this.querySelector('[name="' + name + '"]');
        if (el) el.value = el.value.split('\n').map(s=>s.trim()).filter(Boolean).join('||');
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
