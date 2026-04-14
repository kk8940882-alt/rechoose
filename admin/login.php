<?php
require_once __DIR__ . '/../includes/config.php';
$pdo = getDB();

if (isLoggedIn()) { redirect('dashboard.php'); }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!$username || !$password) {
        $error = 'Please enter username and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (username=? OR email=?) AND is_active=1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_name'] = $user['full_name'];
            $_SESSION['admin_user'] = $user['username'];
            $pdo->prepare("UPDATE admin_users SET last_login=NOW() WHERE id=?")->execute([$user['id']]);
            redirect('dashboard.php');
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background: linear-gradient(135deg, #1a1a2e 0%, #003087 50%, #e31837 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { background: #fff; border-radius: 20px; padding: 44px 40px; box-shadow: 0 24px 80px rgba(0,0,0,0.3); max-width: 420px; width: 100%; }
        .logo-circle { width: 72px; height: 72px; background: linear-gradient(135deg,#e31837,#003087); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 26px; font-weight: 800; margin: 0 auto 16px; }
        .form-control { border-radius: 10px !important; border: 1.5px solid #e5e7ef !important; padding: 11px 14px !important; font-size: 0.9rem !important; }
        .form-control:focus { border-color: #e31837 !important; box-shadow: 0 0 0 3px rgba(227,24,55,0.1) !important; }
        .btn-login { background: linear-gradient(135deg,#e31837,#b5112b); color: #fff; border: none; border-radius: 50px; padding: 12px; font-weight: 700; font-size: 0.95rem; width: 100%; transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(227,24,55,0.4); }
        .input-icon { position: relative; }
        .input-icon i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .input-icon input { padding-left: 38px !important; }
    </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-center">
        <div class="login-card">
            <div class="text-center mb-4">
                <div class="logo-circle">RA</div>
                <h4 class="fw-bold mb-1">Admin Panel</h4>
                <p class="text-muted small">RA Beauty & Aesthetic Products</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><i class="fas fa-exclamation-circle me-2"></i><?= $error ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <div class="mb-3">
                    <label class="form-label small fw-600">Username or Email</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" placeholder="Enter username" required autofocus>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-600">Password</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>
                </div>
                <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt me-2"></i>Login to Dashboard</button>
            </form>
            <div class="text-center mt-4">
                <a href="../index.php" class="text-muted small text-decoration-none"><i class="fas fa-arrow-left me-1"></i>Back to Website</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
