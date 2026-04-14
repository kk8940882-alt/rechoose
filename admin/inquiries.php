<?php
$pageTitle = 'Inquiries';
require_once __DIR__ . '/includes/header.php';

// Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $pdo->prepare("DELETE FROM inquiries WHERE id=?")->execute([(int)$_POST['delete_id']]);
    redirect('inquiries.php');
}

// Mark read
if (isset($_GET['view'])) {
    $viewId = (int)$_GET['view'];
    $pdo->prepare("UPDATE inquiries SET is_read=1 WHERE id=?")->execute([$viewId]);
}

$page = max(1,(int)($_GET['page']??1)); $perPage=20; $offset=($page-1)*$perPage;
$total = $pdo->query("SELECT COUNT(*) FROM inquiries")->fetchColumn();
$totalPages = ceil($total/$perPage);

$inquiries = $pdo->query("SELECT i.*, p.name AS product_name FROM inquiries i LEFT JOIN products p ON p.id=i.product_id ORDER BY i.created_at DESC LIMIT $perPage OFFSET $offset")->fetchAll();

$viewInquiry = null;
if (isset($_GET['view'])) {
    $s = $pdo->prepare("SELECT i.*, p.name AS product_name FROM inquiries i LEFT JOIN products p ON p.id=i.product_id WHERE i.id=?");
    $s->execute([(int)$_GET['view']]); $viewInquiry = $s->fetch();
}
?>

<?php if ($viewInquiry): ?>
<!-- View Modal inline -->
<div class="form-card mb-4">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h6 class="fw-bold mb-1"><?= sanitize($viewInquiry['name']) ?></h6>
            <span class="text-muted small"><?= sanitize($viewInquiry['email']) ?>
            <?php if($viewInquiry['phone']): ?> &bull; <?= sanitize($viewInquiry['phone']) ?><?php endif; ?>
            &bull; <?= date('d M Y, H:i', strtotime($viewInquiry['created_at'])) ?></span>
        </div>
        <a href="inquiries.php" class="btn-edit-sm">← Back</a>
    </div>
    <?php if($viewInquiry['product_name']): ?>
    <div class="mb-2"><span style="background:#f0f2f8;padding:4px 12px;border-radius:50px;font-size:0.8rem;"><i class="fas fa-tag me-1"></i>Product: <?= sanitize($viewInquiry['product_name']) ?></span></div>
    <?php endif; ?>
    <?php if($viewInquiry['subject']): ?>
    <div class="mb-2"><strong style="font-size:0.87rem;">Subject:</strong> <?= sanitize($viewInquiry['subject']) ?></div>
    <?php endif; ?>
    <div class="mt-3 p-3" style="background:#f8f9fc;border-radius:8px;font-size:0.9rem;line-height:1.8;"><?= nl2br(sanitize($viewInquiry['message'])) ?></div>
    <div class="d-flex gap-2 mt-3">
        <a href="mailto:<?= sanitize($viewInquiry['email']) ?>?subject=Re: <?= urlencode($viewInquiry['subject'] ?: 'Your Inquiry') ?>" class="btn btn-primary-admin btn-sm"><i class="fas fa-reply me-1"></i>Reply by Email</a>
        <?php if($viewInquiry['phone']): ?>
        <a href="https://wa.me/91<?= preg_replace('/\D/','',$viewInquiry['phone']) ?>" target="_blank" class="btn btn-sm" style="background:#25D366;color:#fff;border-radius:8px;font-size:0.87rem;"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<div class="admin-table">
    <div class="px-4 py-3 border-bottom d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">All Inquiries (<?= $total ?>)</h6>
        <?php $unread=$pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_read=0")->fetchColumn(); ?>
        <?php if($unread): ?><span class="badge bg-danger"><?= $unread ?> unread</span><?php endif; ?>
    </div>
    <?php if(empty($inquiries)): ?>
    <div class="text-center py-5 text-muted"><i class="fas fa-inbox fa-3x mb-3 opacity-25"></i><p>No inquiries yet.</p></div>
    <?php else: ?>
    <table class="table">
        <thead><tr><th>Name</th><th>Contact</th><th>Subject/Product</th><th>Date</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php foreach($inquiries as $inq): ?>
            <tr <?= !$inq['is_read'] ? 'style="background:#fffdf5;"' : '' ?>>
                <td>
                    <div style="font-weight:<?= $inq['is_read']?'400':'700' ?>;font-size:0.87rem;"><?= sanitize($inq['name']) ?></div>
                </td>
                <td>
                    <div style="font-size:0.8rem;"><?= sanitize($inq['email']) ?></div>
                    <?php if($inq['phone']): ?><div style="font-size:0.75rem;color:#888;"><?= sanitize($inq['phone']) ?></div><?php endif; ?>
                </td>
                <td>
                    <div style="font-size:0.8rem;max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= sanitize($inq['subject'] ?: 'General Inquiry') ?></div>
                    <?php if($inq['product_name']): ?><div style="font-size:0.73rem;color:var(--primary);"><?= sanitize($inq['product_name']) ?></div><?php endif; ?>
                </td>
                <td style="font-size:0.78rem;color:#888;"><?= date('d M Y', strtotime($inq['created_at'])) ?></td>
                <td><span class="status-badge <?= $inq['is_read']?'status-inactive':'status-active' ?>"><?= $inq['is_read']?'Read':'New' ?></span></td>
                <td>
                    <div class="d-flex gap-1">
                        <a href="inquiries.php?view=<?= $inq['id'] ?>" class="btn-edit-sm"><i class="fas fa-eye me-1"></i>View</a>
                        <form method="post" onsubmit="return confirmDelete(this,'Delete this inquiry?')">
                            <input type="hidden" name="delete_id" value="<?= $inq['id'] ?>">
                            <button class="btn-danger-sm"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if($totalPages>1): ?>
    <div class="px-4 py-3 border-top">
        <nav><ul class="pagination pagination-sm mb-0">
            <?php if($page>1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page-1 ?>">Prev</a></li><?php endif; ?>
            <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
            <li class="page-item <?= $i==$page?'active':'' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <?php if($page<$totalPages): ?><li class="page-item"><a class="page-link" href="?page=<?= $page+1 ?>">Next</a></li><?php endif; ?>
        </ul></nav>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
