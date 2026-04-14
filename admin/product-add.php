<?php
$pageTitle = 'Add Product';
require_once __DIR__ . '/includes/header.php';

$errors = []; $success = '';
$allCats = $pdo->query("SELECT * FROM categories WHERE is_active=1 ORDER BY sort_order")->fetchAll();

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
        $slugBase = slug($name); $slugFinal = $slugBase; $n = 1;
        while ($pdo->prepare("SELECT id FROM products WHERE slug=?")->execute([$slugFinal]) && $pdo->prepare("SELECT id FROM products WHERE slug=?")->execute([$slugFinal]) && $pdo->query("SELECT id FROM products WHERE slug='$slugFinal'")->fetchColumn()) {
            $slugFinal = $slugBase . '-' . $n++;
        }

        // Main image
        $image = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $image = uploadImage($_FILES['image'], 'prod');
            if (!$image) $errors[] = 'Invalid image file. Allowed: JPG, PNG, WEBP (max 5MB).';
        }

        // Gallery
        $galleryFiles = [];
        if (!empty($_FILES['gallery']['tmp_name'][0])) {
            foreach ($_FILES['gallery']['tmp_name'] as $k => $tmp) {
                if ($tmp) {
                    $gFile = ['name'=>$_FILES['gallery']['name'][$k],'tmp_name'=>$tmp,'size'=>$_FILES['gallery']['size'][$k],'error'=>$_FILES['gallery']['error'][$k],'type'=>$_FILES['gallery']['type'][$k]];
                    $gImg = uploadImage($gFile, 'gal');
                    if ($gImg) $galleryFiles[] = $gImg;
                }
            }
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO products (category_id,name,slug,short_description,description,features,specifications,image,image_gallery,is_featured,is_active,sort_order) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute([$catId,$name,$slugFinal,$shortDesc,$desc,$features,$specs,$image,implode(',',$galleryFiles),$featured,$active,$sortOrder]);
            $_SESSION['flash'] = ['type'=>'success','msg'=>'Product "'.htmlspecialchars($name).'" added successfully!'];
            redirect('products.php');
        }
    }
}
?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger mb-3"><?php foreach($errors as $e): ?><div><i class="fas fa-exclamation-circle me-2"></i><?= $e ?></div><?php endforeach; ?></div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="products.php" class="btn-edit-sm"><i class="fas fa-arrow-left me-1"></i>Back to Products</a>
</div>

<form method="post" enctype="multipart/form-data">
<div class="row g-4">
    <!-- Left -->
    <div class="col-lg-8">
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-4" style="color:#1a1a2e;border-bottom:1px solid #f0f2f8;padding-bottom:12px;">Product Information</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Product Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($_POST['name']??'') ?>" placeholder="e.g. Alice Bubble Max HydraFacial" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Category <span class="text-danger">*</span></label>
                    <select class="form-select" name="category_id" required>
                        <option value="">Select Category</option>
                        <?php foreach($allCats as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_POST['category_id']??'')==$c['id']?'selected':'' ?>><?= sanitize($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="sort_order" value="<?= (int)($_POST['sort_order']??0) ?>" min="0">
                </div>
                <div class="col-12">
                    <label class="form-label">Short Description</label>
                    <textarea class="form-control" name="short_description" rows="2" placeholder="Brief one-line description shown in product cards..."><?= htmlspecialchars($_POST['short_description']??'') ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Full Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Detailed product description..."><?= htmlspecialchars($_POST['description']??'') ?></textarea>
                </div>
            </div>
        </div>

        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3" style="color:#1a1a2e;">Key Features</h6>
            <p class="text-muted small mb-2">Enter each feature on a new line. They will be displayed as a bullet list.</p>
            <textarea class="form-control" name="features" rows="8" placeholder="Treatment heads: Oxygeno, Ultrasound head, High pressure spray
Voltage: 100-240V, 50Hz/60Hz
Power: 100W
Display: 10.4 inch touch screen
..."><?= htmlspecialchars($_POST['features']??'') ?></textarea>
            <small class="text-muted">Separate features with new lines. They will be joined with || internally.</small>
        </div>

        <div class="form-card">
            <h6 class="fw-bold mb-3" style="color:#1a1a2e;">Technical Specifications</h6>
            <p class="text-muted small mb-2">Enter as <strong>Key: Value</strong> pairs, one per line.</p>
            <textarea class="form-control" name="specifications" rows="6" placeholder="Voltage: 100-240V
Power: 100W
Wavelength: 532nm, 755nm, 1064nm
Cooling: Air + Water
..."><?= htmlspecialchars($_POST['specifications']??'') ?></textarea>
            <small class="text-muted">Format: <code>Label: Value</code> — one per line. Joined with || internally.</small>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Publish -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3" style="color:#1a1a2e;">Publish</h6>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1" <?= !isset($_POST['is_active']) || $_POST['is_active'] ? 'checked' : '' ?>>
                <label class="form-check-label small" for="isActive">Active (visible on website)</label>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_featured" id="isFeatured" value="1" <?= isset($_POST['is_featured']) ? 'checked' : '' ?>>
                <label class="form-check-label small" for="isFeatured">Featured on homepage</label>
            </div>
            <button type="submit" class="btn btn-primary-admin w-100"><i class="fas fa-save me-2"></i>Save Product</button>
        </div>

        <!-- Main Image -->
        <div class="form-card mb-4">
            <h6 class="fw-bold mb-3" style="color:#1a1a2e;">Main Product Image</h6>
            <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this,'mainPreview')">
            <div class="mt-2" id="mainPreview"></div>
            <small class="text-muted">JPG, PNG, WEBP — max 5MB</small>
        </div>

        <!-- Gallery -->
        <div class="form-card">
            <h6 class="fw-bold mb-3" style="color:#1a1a2e;">Image Gallery <span class="text-muted fw-normal" style="font-size:0.75rem;">(optional)</span></h6>
            <input type="file" class="form-control" name="gallery[]" accept="image/*" multiple>
            <small class="text-muted">Select multiple images for the product gallery</small>
        </div>
    </div>
</div>
</form>

<script>
// Convert features textarea newlines to || on submit
document.querySelector('form').addEventListener('submit', function() {
    ['features', 'specifications'].forEach(name => {
        const el = this.querySelector('[name="' + name + '"]');
        if (el) el.value = el.value.split('\n').map(s=>s.trim()).filter(Boolean).join('||');
    });
});

// Re-convert || to newlines on load (for edit mode)
['features', 'specifications'].forEach(name => {
    const el = document.querySelector('[name="' + name + '"]');
    if (el && el.value.includes('||')) {
        el.value = el.value.split('||').join('\n');
    }
});

function previewImage(input, targetId) {
    const target = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            target.innerHTML = '<img src="' + e.target.result + '" style="max-width:100%;border-radius:8px;margin-top:8px;">';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
