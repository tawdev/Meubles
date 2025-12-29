<?php
require_once __DIR__ . '/../db.php';

// Configuration SEO de base
$siteName = "Frachdark - Meubles de Maison";
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$currentUrl = $siteUrl . $_SERVER['REQUEST_URI'];
$currentPage = basename($_SERVER['PHP_SELF']);

// Définir les meta descriptions par défaut pour chaque page
$defaultMetaDescriptions = [
    'index.php' => 'Découvrez Frachdark, votre destination pour des meubles modernes et élégants. Salon, chambre, salle à manger, bureau et décoration. Qualité premium, livraison rapide.',
    'products.php' => 'Parcourez notre catalogue complet de meubles de qualité. Trouvez le meuble parfait pour chaque pièce de votre maison. Large sélection, prix compétitifs.',
    'product.php' => 'Détails du produit - Meubles de qualité supérieure pour votre intérieur. Découvrez les caractéristiques, prix et disponibilité.',
    'categories.php' => 'Explorez nos catégories de meubles : Salon, Chambre, Salle à manger, Bureau et Décoration. Trouvez l\'inspiration pour aménager votre intérieur.',
    'about.php' => 'Découvrez l\'histoire de Frachdark, votre partenaire de confiance pour transformer votre intérieur avec des meubles de qualité premium.',
    'contact.php' => 'Contactez Frachdark pour toute question sur nos meubles. Notre équipe est à votre disposition pour vous conseiller et vous accompagner.',
    'cart.php' => 'Votre panier d\'achat - Finalisez votre commande de meubles en toute sécurité avec Frachdark.'
];

$defaultKeywords = [
    'index.php' => 'meubles, meubles de maison, mobilier, salon, chambre, salle à manger, bureau, décoration, frachdark, meubles modernes, meubles élégants',
    'products.php' => 'meubles, catalogue meubles, mobilier intérieur, meubles salon, meubles chambre, meubles salle à manger, meubles bureau',
    'product.php' => 'meuble, mobilier, achat meuble, prix meuble, description produit',
    'categories.php' => 'catégories meubles, meubles salon, meubles chambre, meubles salle à manger, meubles bureau, décoration intérieure',
    'about.php' => 'à propos, histoire, frachdark, meubles qualité, mobilier premium',
    'contact.php' => 'contact, service client, support, questions meubles, assistance',
    'cart.php' => 'panier, commande, achat, checkout'
];

// Récupérer les meta personnalisées si définies
$pageMetaDescription = isset($pageMetaDescription) ? $pageMetaDescription : ($defaultMetaDescriptions[$currentPage] ?? 'Frachdark - Meubles de qualité pour votre maison. Découvrez notre collection exclusive de meubles modernes et élégants.');
$pageKeywords = isset($pageKeywords) ? $pageKeywords : ($defaultKeywords[$currentPage] ?? 'meubles, mobilier, frachdark, meubles de maison');
$pageTitle = isset($pageTitle) ? $pageTitle : 'Accueil';
$pageImage = isset($pageImage) ? $pageImage : $siteUrl . '/images/logo.jpg';

// Construire le titre SEO optimisé
$seoTitle = ($currentPage === 'index.php') ? $siteName . ' - Meubles Modernes et Élégants pour Votre Intérieur' : $pageTitle . ' - ' . $siteName;

// Canonical URL
$canonicalUrl = $currentUrl;
if (strpos($canonicalUrl, '?') !== false) {
    $canonicalUrl = strtok($canonicalUrl, '?');
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Primary Meta Tags -->
    <title><?php echo htmlspecialchars($seoTitle); ?></title>
    <meta name="title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta name="description" content="<?php echo htmlspecialchars($pageMetaDescription); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($pageKeywords); ?>">
    <meta name="author" content="Frachdark">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="French">
    <meta name="revisit-after" content="7 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?php echo htmlspecialchars($canonicalUrl); ?>">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo ($currentPage === 'product.php') ? 'product' : 'website'; ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($pageMetaDescription); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($pageImage); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($siteName); ?>">
    <meta property="og:locale" content="fr_FR">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($seoTitle); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($pageMetaDescription); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($pageImage); ?>">
    <meta name="twitter:image:alt" content="<?php echo htmlspecialchars($seoTitle); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    
    <!-- Preconnect pour améliorer les performances -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="dns-prefetch" href="https://fonts.googleapis.com">
    
    <!-- Stylesheets avec preload pour améliorer les performances -->
    <link rel="preload" href="styles.css" as="style">
    <link rel="stylesheet" href="styles.css">
    <?php
    // Charger le CSS spécifique à chaque page
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
        echo '<link rel="preload" href="' . $pageStyles[$currentPage] . '" as="style">';
        echo '<link rel="stylesheet" href="' . $pageStyles[$currentPage] . '">';
    }
    ?>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">frachdark</a>
            <button class="menu-toggle" aria-label="Ouvrir le menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <nav class="main-nav">
                <ul>
                <form action="products.php" method="GET" class="header-search-form">
                <input type="text" 
                       name="search" 
                       id="header-search-input"
                       placeholder="Rechercher un produit..." 
                       value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                       class="header-search-input">
                    <button type="submit" class="header-search-button" aria-label="Rechercher">
                    🔍
                    </button>
            </form>
                    <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Accueil</a></li>
                    <li><a href="products.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'products.php' || basename($_SERVER['PHP_SELF']) == 'product.php') ? 'active' : ''; ?>">Produits</a></li>
                    <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Catégories</a></li>
                    <!-- <li><a href="visual_search.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'visual_search.php' ? 'active' : ''; ?>" title="Recherche Visuelle">🔍 Recherche Visuelle</a></li> -->
                    <li><a href="index.php#gallery" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php#gallery' ? 'active' : ''; ?>">Galerie</a></li>   
                    <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">À propos</a></li>
                    <li><a href="contact.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                </ul>
            </nav>
            <!-- Barre de recherche -->
            <a href="cart.php" class="cart-icon">
                🛒
                <span class="cart-count" id="cart-count" style="display: none;">0</span>
            </a>    
        </div>
    </header>

