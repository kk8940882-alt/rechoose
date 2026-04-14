<?php
$pageTitle = 'Banners';
require_once __DIR__ . '/includes/header.php';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $delId = (int)$_POST['delete_id'];
    $row = $pdo->query("SELECT image FROM banners WHERE id=$delId")->fetch();
    if ($row) deleteImage($row['image']);
    $pdo->prepare("DELETE FROM banners WHERE id=?")->execute([$delId]);
    $_SESSION['flash'] = ['type'=>'success','msg'=>'Banner deleted.'];
    redirect('banners.php');
}

// Save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_banner'])) {
    $bid      = (int)($_POST['banner_id'] ?? 0);
    $title    = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $btnText  = trim($_POST['button_text'] ?? '');
    $btnLink  = trim($_POST['button_link'] ?? '');
    $sort     = (int)($_POST['sort_order'] ?? 0);
    $active   = isset($_POST['is_active']) ? 1 : 0;

    $image = null;
    if (!empty($_FILES['banner_image']['tmp_name'])) {
        $image = uploadImage($_FILES['banner_image'], 'banner');
    }

    if ($bid) {
        $existImg = $pdo->query("SELECT image FROM banners WHERE id=$bid")->fetchColumn();
        $imgSave = $image ?? $existImg;
        if ($image && $existImg) deleteImage($existImg);
        $pdo->prepare("UPDATE banners SET title=?,subtitle=?,button_text=?,button_link=?,image=?,sort_order=?,is_active=? WHERE id=?")
            ->execute([$title,$subtitle,$btnText,$btnLink,$imgSave,$sort,$active,$bid]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Banner updated.'];
    } else {
        $pdo->prepare("INSERT INTO banners (title,subtitle,button_text,button_link,image,sort_order,is_active) VALUES (?,?,?,?,?,?,?)")
            ->execute([$title,$subtitle,$btnText,$btnLink,$image,$sort,$active]);
        $_SESSION['flash'] = ['type'=>'success','msg'=>'Banner added.'];
    }
    redirect('banners.php');
}

$banners = $pdo->query("SELECT * FROM banners ORDER BY sort_order")->fetchAll();
$editBanner = null;
if (isset($_GET['edit'])) {
    $s = $pdo->prepare("SELECT * FROM banners WHERE id=?"); $s->execute([(int)$_GET['edit']]); $editBanner = $s->fetch();
}
$flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']);
?>

<?php if ($flash): ?><div class="alert alert-<?= $flash['type'] ?> alert-auto mb-3"><?= $flash['msg'] ?></div><?php endif; ?>

<div class="row g-4">
    <!-- Form -->
    <div class="col-lg-4">
        <div class="form-card">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;"><?= $editBanner ? 'Edit Banner' : 'Add Banner' ?></h6>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="save_banner" value="1">
                <?php if ($editBanner): ?><input type="hidden" name="banner_id" value="<?= $editBanner['id'] ?>"><?php endif; ?>
                <div class="mb-3">
                    <label class="form-label">Title</label>
                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($editBanner['title'] ?? '') ?>">
                </div>
                <div class="mb-3">
                    <label class="form-label">Subtitle</label>
                    <textarea class="form-control" name="subtitle" rows="2"><?= htmlspecialchars($editBanner['subtitle'] ?? '') ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Button Text</label>
                    <input type="text" class="form-control" name="button_text" value="<?= htmlspecialchars($editBanner['button_text'] ?? '') ?>" placeholder="e.g. Explore Products">
                </div>
                <div class="mb-3">
                    <label class="form-label">Button Link</label>
                    <input type="text" class="form-control" name="button_link" value="<?= htmlspecialchars($editBanner['button_link'] ?? '') ?>" placeholder="e.g. products.php">
                </div>
                <div class="mb-3">
                    <label class="form-label">Banner Image</label>
                    <?php if ($editBanner && $editBanner['image'] && file_exists(UPLOAD_PATH.$editBanner['image'])): ?>
                    <img src="<?= UPLOAD_URL.$editBanner['image'] ?>" style="width:100%;border-radius:8px;margin-bottom:8px;" alt="">
                    <?php endif; ?>
                    <input type="file" class="form-control" name="banner_image" accept="image/*">
                    <small class="text-muted">Recommended: 1200×600px</small>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <label class="form-label">Sort Order</label>
                        <input type="number" class="form-control" name="sort_order" value="<?= $editBanner['sort_order'] ?? 0 ?>" min="0">
                    </div>
                    <div class="col-6 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="bannerActive" <?= (!$editBanner || $editBanner['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="bannerActive">Active</label>
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-admin w-100"><i class="fas fa-save me-1"></i><?= $editBanner ? 'Update' : 'Add Banner' ?></button>
                    <?php if ($editBanner): ?><a href="banners.php" class="btn btn-sm" style="background:#f0f2f8;color:#555;border-radius:8px;">Cancel</a><?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- List -->
    <div class="col-lg-8">
        <div class="admin-table">
            <div class="px-4 py-3 border-bottom"><h6 class="mb-0 fw-bold">All Banners (<?= count($banners) ?>)</h6></div>
            <?php if(empty($banners)): ?>
            <div class="text-center py-5 text-muted"><i class="fas fa-images fa-3x mb-3 opacity-25"></i><p>No banners yet.</p></div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Preview</th><th>Title</th><th>Button</th><th>Sort</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach($banners as $b): ?>
                    <tr>
                        <td>
                            <?php if ($b['image'] && file_exists(UPLOAD_PATH.$b['image'])): ?>
                            <img src="<?= UPLOAD_URL.$b['image'] ?>" style="width:80px;height:44px;object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                            <div style="width:80px;height:44px;background:#f0f2f8;border-radius:6px;display:flex;align-items:center;justify-content:center;color:#ccc;font-size:18px;"><i class="fas fa-image"></i></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-size:0.87rem;font-weight:600;"><?= sanitize($b['title']) ?></div>
                            <div class="text-muted" style="font-size:0.75rem;"><?= sanitize(substr($b['subtitle'],0,40)) ?></div>
                        </td>
                        <td style="font-size:0.8rem;"><?= sanitize($b['button_text']) ?></td>
                        <td style="font-size:0.8rem;"><?= $b['sort_order'] ?></td>
                        <td><span class="status-badge <?= $b['is_active']?'status-active':'status-inactive' ?>"><?= $b['is_active']?'Active':'Hidden' ?></span></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="banners.php?edit=<?= $b['id'] ?>" class="btn-edit-sm"><i class="fas fa-edit"></i></a>
                                <form method="post" onsubmit="return confirmDelete(this,'Delete this banner?')">
                                    <input type="hidden" name="delete_id" value="<?= $b['id'] ?>">
                                    <button class="btn-danger-sm"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
