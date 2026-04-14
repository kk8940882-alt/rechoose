<?php
$pageTitle = 'Change Password';
require_once __DIR__ . '/includes/header.php';

$success = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'] ?? '';
    $new      = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';
    $adminId  = $_SESSION['admin_id'];

    $user = $pdo->prepare("SELECT * FROM admin_users WHERE id=?");
    $user->execute([$adminId]); $user = $user->fetch();

    if (!password_verify($current, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($new) < 8) {
        $error = 'New password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'New passwords do not match.';
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE admin_users SET password=? WHERE id=?")->execute([$hashed,$adminId]);
        $success = 'Password changed successfully!';
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="form-card">
            <h6 class="fw-bold mb-4" style="border-bottom:1px solid #f0f2f8;padding-bottom:12px;"><i class="fas fa-key me-2 text-danger"></i>Change Password</h6>
            <?php if ($success): ?><div class="alert alert-success alert-auto mb-3"><i class="fas fa-check-circle me-2"></i><?= $success ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-danger mb-3"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div><?php endif; ?>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Current Password</label>
                    <input type="password" class="form-control" name="current_password" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" minlength="8" required>
                    <small class="text-muted">Minimum 8 characters.</small>
                </div>
                <div class="mb-4">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" name="confirm_password" required>
                </div>
                <button type="submit" class="btn btn-primary-admin w-100"><i class="fas fa-save me-2"></i>Update Password</button>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
