<?php
require_once __DIR__ . '/../../includes/config.php';
requireLogin();
$pdo = getDB();
$adminName = $_SESSION['admin_name'] ?? 'Admin';
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Stats for sidebar badges
$productCount  = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1")->fetchColumn();
$inquiryUnread = $pdo->query("SELECT COUNT(*) FROM inquiries WHERE is_read=0")->fetchColumn();
$catCount      = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active=1")->fetchColumn();

if (!isset($pageTitle)) $pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Admin | <?= SITE_NAME ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 260px;
            --primary: #e31837;
            --secondary: #003087;
            --dark: #1a1a2e;
        }
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        body { background: #f4f6fb; margin: 0; }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-w); position: fixed; top: 0; left: 0; height: 100vh;
            background: var(--dark); overflow-y: auto; z-index: 1000;
            display: flex; flex-direction: column;
            transition: transform 0.3s ease;
        }
        .sidebar-brand { padding: 20px 22px; border-bottom: 1px solid rgba(255,255,255,0.07); }
        .sidebar-brand .logo { width: 42px; height: 42px; background: linear-gradient(135deg,#e31837,#003087); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 800; font-size: 16px; flex-shrink: 0; }
        .sidebar-brand .brand-text { color: #fff; font-size: 0.88rem; font-weight: 700; line-height: 1.2; }
        .sidebar-brand .brand-sub { color: rgba(255,255,255,0.4); font-size: 0.7rem; }
        .sidebar-nav { padding: 16px 0; flex: 1; }
        .nav-label { color: rgba(255,255,255,0.3); font-size: 0.65rem; font-weight: 600; letter-spacing: 1.5px; text-transform: uppercase; padding: 10px 22px 4px; }
        .sidebar-link {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 22px; color: rgba(255,255,255,0.65); text-decoration: none;
            font-size: 0.87rem; font-weight: 500; transition: all 0.2s; border-radius: 0;
            position: relative;
        }
        .sidebar-link:hover { color: #fff; background: rgba(255,255,255,0.07); }
        .sidebar-link.active { color: #fff; background: rgba(227,24,55,0.2); border-left: 3px solid var(--primary); }
        .sidebar-link .icon { width: 18px; text-align: center; font-size: 14px; }
        .sidebar-link .badge-dot { margin-left: auto; background: var(--primary); color: #fff; font-size: 0.68rem; padding: 2px 7px; border-radius: 50px; font-weight: 600; }
        .sidebar-footer { padding: 16px 22px; border-top: 1px solid rgba(255,255,255,0.07); }

        /* Main content */
        .main-content { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        /* Top bar */
        .admin-topbar { background: #fff; padding: 14px 28px; box-shadow: 0 1px 8px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
        .topbar-title { font-weight: 700; font-size: 1.1rem; color: var(--dark); }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .topbar-btn { background: none; border: none; cursor: pointer; position: relative; padding: 6px; color: #666; font-size: 16px; border-radius: 8px; transition: all 0.2s; }
        .topbar-btn:hover { background: #f0f0f0; color: var(--primary); }
        .notif-badge { position: absolute; top: 0; right: 0; width: 18px; height: 18px; background: var(--primary); color: #fff; border-radius: 50%; font-size: 0.62rem; display: flex; align-items: center; justify-content: center; font-weight: 700; }
        .admin-avatar { width: 34px; height: 34px; background: linear-gradient(135deg,var(--primary),var(--secondary)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 13px; cursor: pointer; }

        /* Page body */
        .page-body { padding: 28px; flex: 1; }

        /* Cards */
        .stat-card { background: #fff; border-radius: 14px; padding: 22px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); border: 1px solid #eef0f6; transition: all 0.3s; }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .stat-num { font-size: 1.9rem; font-weight: 800; line-height: 1; color: var(--dark); }
        .stat-label { font-size: 0.82rem; color: #888; font-weight: 500; }

        /* Table */
        .admin-table { background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 16px rgba(0,0,0,0.06); }
        .admin-table .table { margin: 0; }
        .admin-table .table th { background: #f8f9fc; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #666; border-bottom: 1px solid #eef0f6; padding: 12px 16px; }
        .admin-table .table td { padding: 12px 16px; font-size: 0.87rem; vertical-align: middle; border-bottom: 1px solid #f0f2f8; }
        .admin-table .table tr:last-child td { border-bottom: none; }
        .admin-table .table tr:hover td { background: #fafbff; }

        /* Form card */
        .form-card { background: #fff; border-radius: 14px; padding: 28px; box-shadow: 0 2px 16px rgba(0,0,0,0.06); }
        .form-label { font-size: 0.85rem; font-weight: 600; color: #444; margin-bottom: 6px; }
        .form-control, .form-select { border-radius: 8px !important; border: 1.5px solid #e5e7ef !important; padding: 9px 13px !important; font-size: 0.88rem !important; }
        .form-control:focus, .form-select:focus { border-color: var(--primary) !important; box-shadow: 0 0 0 3px rgba(227,24,55,0.1) !important; }

        /* Buttons */
        .btn-primary-admin { background: var(--primary); color: #fff; border: none; border-radius: 8px; padding: 9px 20px; font-size: 0.87rem; font-weight: 600; transition: all 0.2s; }
        .btn-primary-admin:hover { background: #b5112b; color: #fff; }
        .btn-secondary-admin { background: var(--secondary); color: #fff; border: none; border-radius: 8px; padding: 9px 20px; font-size: 0.87rem; font-weight: 600; transition: all 0.2s; }
        .btn-secondary-admin:hover { background: #001f5c; color: #fff; }
        .btn-danger-sm { background: rgba(227,24,55,0.1); color: var(--primary); border: none; border-radius: 6px; padding: 5px 10px; font-size: 0.78rem; transition: all 0.2s; cursor: pointer; }
        .btn-danger-sm:hover { background: var(--primary); color: #fff; }
        .btn-edit-sm { background: rgba(0,48,135,0.08); color: var(--secondary); border: none; border-radius: 6px; padding: 5px 10px; font-size: 0.78rem; transition: all 0.2s; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-edit-sm:hover { background: var(--secondary); color: #fff; }

        /* Alerts */
        .alert-success { background: rgba(25,195,125,0.1); border: 1px solid rgba(25,195,125,0.3); color: #0e7c5a; border-radius: 8px; }
        .alert-danger { background: rgba(227,24,55,0.08); border: 1px solid rgba(227,24,55,0.2); color: var(--primary); border-radius: 8px; }

        /* Responsive */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; }
        .sidebar-overlay.show { display: block; }

        /* Badge */
        .status-badge { padding: 3px 10px; border-radius: 50px; font-size: 0.72rem; font-weight: 600; }
        .status-active { background: rgba(25,195,125,0.12); color: #0e7c5a; }
        .status-inactive { background: rgba(100,100,100,0.1); color: #666; }

        /* Img thumb */
        .prod-thumb { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; background: #f4f6fb; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-2">
        <div class="logo">RA</div>
        <div>
            <div class="brand-text">RA Beauty</div>
            <div class="brand-sub">Admin Panel</div>
        </div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Main</div>
        <a href="dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard' ? 'active' : '' ?>">
            <i class="fas fa-tachometer-alt icon"></i> Dashboard
        </a>
        <div class="nav-label">Catalogue</div>
        <a href="products.php" class="sidebar-link <?= in_array($currentPage, ['products','product-add','product-edit']) ? 'active' : '' ?>">
            <i class="fas fa-boxes icon"></i> Products
            <?php if ($productCount): ?><span class="badge-dot"><?= $productCount ?></span><?php endif; ?>
        </a>
        <a href="categories.php" class="sidebar-link <?= in_array($currentPage, ['categories','category-add','category-edit']) ? 'active' : '' ?>">
            <i class="fas fa-tags icon"></i> Categories
        </a>
        <div class="nav-label">Website</div>
        <a href="banners.php" class="sidebar-link <?= in_array($currentPage, ['banners','banner-add','banner-edit']) ? 'active' : '' ?>">
            <i class="fas fa-images icon"></i> Banners
        </a>
        <a href="inquiries.php" class="sidebar-link <?= $currentPage === 'inquiries' ? 'active' : '' ?>">
            <i class="fas fa-envelope icon"></i> Inquiries
            <?php if ($inquiryUnread): ?><span class="badge-dot"><?= $inquiryUnread ?></span><?php endif; ?>
        </a>
        <a href="settings.php" class="sidebar-link <?= $currentPage === 'settings' ? 'active' : '' ?>">
            <i class="fas fa-cog icon"></i> Settings
        </a>
        <div class="nav-label">Account</div>
        <a href="change-password.php" class="sidebar-link <?= $currentPage === 'change-password' ? 'active' : '' ?>">
            <i class="fas fa-key icon"></i> Change Password
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-link" style="padding:8px 0;">
            <i class="fas fa-sign-out-alt icon"></i> Logout
        </a>
    </div>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Main -->
<div class="main-content">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="topbar-btn d-lg-none" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <div class="topbar-title"><?= $pageTitle ?></div>
        </div>
        <div class="topbar-right">
            <a href="inquiries.php" class="topbar-btn position-relative" title="Inquiries">
                <i class="fas fa-bell"></i>
                <?php if ($inquiryUnread): ?><span class="notif-badge"><?= $inquiryUnread ?></span><?php endif; ?>
            </a>
            <a href="<?= SITE_URL ?>" target="_blank" class="topbar-btn" title="View Website"><i class="fas fa-external-link-alt"></i></a>
            <div class="dropdown">
                <div class="admin-avatar dropdown-toggle" data-bs-toggle="dropdown" title="<?= htmlspecialchars($adminName) ?>">
                    <?= strtoupper(substr($adminName, 0, 2)) ?>
                </div>
                <ul class="dropdown-menu dropdown-menu-end" style="font-size:0.85rem;">
                    <li><span class="dropdown-item-text text-muted small"><?= htmlspecialchars($adminName) ?></span></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                    <li><a class="dropdown-item" href="change-password.php"><i class="fas fa-key me-2"></i>Change Password</a></li>
                    <li><hr class="dropdown-divider my-1"></li>
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="page-body">
