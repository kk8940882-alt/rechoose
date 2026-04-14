<?php
// =============================================
// DATABASE CONFIGURATION
// =============================================
// Update these values with your Hostinger DB details

define('DB_HOST', 'localhost');
define('DB_NAME', 'rabeauty_db');         // Your database name
define('DB_USER', 'root');     // Your database username
define('DB_PASS', '');     // Your database password
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_URL', '');   // Your domain
define('SITE_NAME', 'RA Beauty & Aesthetic Products');
define('ADMIN_EMAIL', 'admin@rabeauty.com');

// File Upload Settings
define('UPLOAD_PATH', __DIR__ . '/../uploads/products/');
define('UPLOAD_URL', SITE_URL . '/uploads/products/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp', 'gif']);

// Session
define('SESSION_NAME', 'rabeauty_session');
define('SESSION_LIFETIME', 7200); // 2 hours

// =============================================
// DATABASE CONNECTION
// =============================================
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            // $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $dsn = "mysql:host=" . DB_HOST . ";port=3307;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die('<div style="padding:20px;background:#fee;color:#c00;font-family:sans-serif;">
                <h3>Database Connection Error</h3>
                <p>Could not connect to the database. Please check your configuration in <code>includes/config.php</code></p>
                <small>' . htmlspecialchars($e->getMessage()) . '</small>
            </div>');
        }
    }
    return $pdo;
}

// =============================================
// HELPER FUNCTIONS
// =============================================

function getSetting($key, $default = '') {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $row = $stmt->fetch();
    return $row ? $row['setting_value'] : $default;
}

function slug($str) {
    $str = strtolower(trim($str));
    $str = preg_replace('/[^a-z0-9-]/', '-', $str);
    $str = preg_replace('/-+/', '-', $str);
    return trim($str, '-');
}

function sanitize($str) {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        redirect('../admin/login.php');
    }
}

function uploadImage($file, $prefix = 'img') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) return null;
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) return null;
    if ($file['size'] > MAX_FILE_SIZE) return null;
    
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = UPLOAD_PATH . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return $filename;
    }
    return null;
}

function deleteImage($filename) {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        unlink(UPLOAD_PATH . $filename);
    }
}

function getProductImage($filename, $size = 'medium') {
    if ($filename && file_exists(UPLOAD_PATH . $filename)) {
        return UPLOAD_URL . $filename;
    }
    return SITE_URL . '/assets/images/no-product-image.jpg';
}

// Start session
session_name(SESSION_NAME);
session_set_cookie_params(SESSION_LIFETIME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
