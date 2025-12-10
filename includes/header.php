<?php
require_once __DIR__ . '/../db.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>Meubles de Maison</title>
    <link rel="stylesheet" href="styles.css">
    <?php
    // Charger le CSS spécifique à chaque page
    $currentPage = basename($_SERVER['PHP_SELF']);
    $pageStyles = [
        'index.php' => 'css/pages/home.css',
        'products.php' => 'css/pages/products.css',
        'product.php' => 'css/pages/product-detail.css',
        'cart.php' => 'css/pages/cart.css',
        'about.php' => 'css/pages/about.css',
        'contact.php' => 'css/pages/contact.css',
        'categories.php' => 'css/pages/categories.css'
    ];
    
    if (isset($pageStyles[$currentPage])) {
        echo '<link rel="stylesheet" href="' . $pageStyles[$currentPage] . '">';
    }
    ?>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">Meubles de Maison</a>
            <nav>
                <ul>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product.php') ? 'active' : ''; ?>">Produits</a></li>
                    <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Catégories</a></li>
                    <li><a href="index.php#gallery" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php#gallery' ? 'active' : ''; ?>">Galerie</a></li>   
                    <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">À propos</a></li>
                    <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            <a href="cart.php" class="cart-icon">
                🛒
                <span class="cart-count" id="cart-count" style="display: none;">0</span>
            </a>
        </div>
    </header>

