<?php
/**
 * Sitemap dynamique pour SEO
 * Génère un sitemap.xml avec toutes les pages et produits du site
 */

header('Content-Type: application/xml; charset=utf-8');

require_once 'db.php';

$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$currentDate = date('Y-m-d');

// Pages statiques avec priorité et fréquence
$staticPages = [
    ['url' => '/index.php', 'priority' => '1.0', 'changefreq' => 'daily'],
    ['url' => '/products.php', 'priority' => '0.9', 'changefreq' => 'daily'],
    ['url' => '/categories.php', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ['url' => '/about.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
    ['url' => '/contact.php', 'priority' => '0.7', 'changefreq' => 'monthly'],
];

// Récupérer toutes les catégories
$categories = [];
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    $categories = [];
}

// Récupérer tous les produits
$products = [];
try {
    $stmt = $pdo->query("SELECT id, updated_at FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
echo '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
echo '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
echo '        http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

// Pages statiques
foreach ($staticPages as $page) {
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($siteUrl . $page['url']) . "</loc>\n";
    echo "    <lastmod>" . $currentDate . "</lastmod>\n";
    echo "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
    echo "    <priority>" . $page['priority'] . "</priority>\n";
    echo "  </url>\n";
}

// Pages catégories
foreach ($categories as $category) {
    $categoryUrl = $siteUrl . '/products.php?category=' . urlencode($category['name']);
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($categoryUrl) . "</loc>\n";
    echo "    <lastmod>" . $currentDate . "</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.8</priority>\n";
    echo "  </url>\n";
}

// Pages produits
foreach ($products as $product) {
    $productUrl = $siteUrl . '/product.php?id=' . $product['id'];
    $lastmod = isset($product['updated_at']) ? date('Y-m-d', strtotime($product['updated_at'])) : $currentDate;
    echo "  <url>\n";
    echo "    <loc>" . htmlspecialchars($productUrl) . "</loc>\n";
    echo "    <lastmod>" . $lastmod . "</lastmod>\n";
    echo "    <changefreq>weekly</changefreq>\n";
    echo "    <priority>0.7</priority>\n";
    echo "  </url>\n";
}

echo '</urlset>';
?>

