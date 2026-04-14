<?php
require_once __DIR__ . '/includes/config.php';
header('Content-Type: application/xml; charset=utf-8');
$pdo = getDB();
$products   = $pdo->query("SELECT slug, updated_at FROM products WHERE is_active=1")->fetchAll();
$categories = $pdo->query("SELECT slug FROM categories WHERE is_active=1")->fetchAll();
?>
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <url><loc><?= SITE_URL ?>/</loc><changefreq>weekly</changefreq><priority>1.0</priority></url>
  <url><loc><?= SITE_URL ?>/products.php</loc><changefreq>weekly</changefreq><priority>0.9</priority></url>
  <url><loc><?= SITE_URL ?>/about.php</loc><changefreq>monthly</changefreq><priority>0.7</priority></url>
  <url><loc><?= SITE_URL ?>/contact.php</loc><changefreq>monthly</changefreq><priority>0.7</priority></url>
  <?php foreach($categories as $c): ?>
  <url><loc><?= SITE_URL ?>/products.php?category=<?= $c['slug'] ?></loc><changefreq>weekly</changefreq><priority>0.8</priority></url>
  <?php endforeach; ?>
  <?php foreach($products as $p): ?>
  <url>
    <loc><?= SITE_URL ?>/product.php?slug=<?= $p['slug'] ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($p['updated_at'])) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endforeach; ?>
</urlset>
