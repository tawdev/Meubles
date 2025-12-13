<?php
$pageTitle = "Nos Catégories";
require_once 'includes/header.php';

// Récupérer les catégories
try {
    $categoriesStmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category = c.name) as product_count FROM categories c ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
    
    // Mettre à jour l'icône de Bureau si nécessaire
    foreach ($categoriesList as &$cat) {
        if ($cat['name'] === 'Bureau' && ($cat['icon'] === '💼' || empty($cat['icon']))) {
            $cat['icon'] = '🖥️';
        }
    }
    unset($cat);
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['name' => 'Salon', 'icon' => '🛋️', 'description' => 'Meubles pour le salon', 'product_count' => 0],
        ['name' => 'Chambre', 'icon' => '🛏️', 'description' => 'Meubles pour la chambre', 'product_count' => 0],
        ['name' => 'Salle à manger', 'icon' => '🍽️', 'description' => 'Meubles pour la salle à manger', 'product_count' => 0],
        ['name' => 'Bureau', 'icon' => '🖥️', 'description' => 'Meubles de bureau', 'product_count' => 0],
        ['name' => 'Décoration', 'icon' => '🖼️', 'description' => 'Éléments de décoration', 'product_count' => 0]
    ];
}

?>

<div class="container">
    <!-- Hero Section pour la page catégories -->
    <section class="hero" style="padding: 3rem 2rem; margin-bottom: 3rem; position: relative;">
        <a href="index.php" style="position: absolute; top: 2rem; left: 2rem; display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; padding: 0.75rem 1.25rem; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 8px; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 500; z-index: 10; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateX(-3px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(0)';">
            ← Retour à l'accueil
        </a>
        <div class="hero-content">
            <h1>Nos Catégories</h1>
            <p>Explorez notre collection organisée par catégories pour trouver exactement ce que vous cherchez</p>
        </div>
    </section>

    <!-- Liste des catégories -->
    <section id="categories-list">
        <h2 class="section-title">Toutes Nos Catégories</h2>
        
        <div class="categories" style="margin-bottom: 4rem;">
            <?php foreach ($categoriesList as $category): ?>
                <a href="products.php?category=<?php echo urlencode($category['name']); ?>" 
                   class="category-card" 
                   style="text-decoration: none; color: inherit; display: block;">
                    <div style="font-size: 5rem; margin-bottom: 1rem; text-align: center;">
                        <?php echo htmlspecialchars($category['icon'] ?? '📦'); ?>
                    </div>
                    <h3 style="text-align: center; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($category['name']); ?>
                    </h3>
                    <?php if (!empty($category['description'])): ?>
                        <p style="text-align: center; color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($category['description']); ?>
                        </p>
                    <?php endif; ?>
                    <p style="text-align: center; color: var(--secondary-color); font-weight: 600; margin-top: 1rem;">
                        <?php echo $category['product_count'] ?? 0; ?> produit(s)
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>

