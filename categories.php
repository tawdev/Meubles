<?php
// Configuration SEO pour la page Catégories
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$pageTitle = "Nos Catégories";
$pageMetaDescription = "Explorez nos catégories de meubles : Salon, Chambre, Salle à manger, Bureau et Décoration. Trouvez l'inspiration pour aménager votre intérieur avec des meubles de qualité.";
$pageKeywords = "catégories meubles, meubles salon, meubles chambre, meubles salle à manger, meubles bureau, décoration intérieure, frachdark";
$pageImage = $siteUrl . '/images/logo.jpg';

require_once 'includes/header.php';

// Récupérer les catégories avec le nombre de types de catégories
try {
    $categoriesStmt = $pdo->query("
        SELECT c.*, 
               (SELECT COUNT(*) FROM types_categories tc WHERE tc.category_id = c.id) AS type_count
        FROM categories c
        ORDER BY c.name
    ");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories (ou types_categories) n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['name' => 'Salon', 'image' => '', 'description' => 'Meubles pour le salon', 'type_count' => 0],
        ['name' => 'Chambre', 'image' => '', 'description' => 'Meubles pour la chambre', 'type_count' => 0],
        ['name' => 'Salle à manger', 'image' => '', 'description' => 'Meubles pour la salle à manger', 'type_count' => 0],
        ['name' => 'Bureau', 'image' => '', 'description' => 'Meubles de bureau', 'type_count' => 0],
        ['name' => 'Décoration', 'image' => '', 'description' => 'Éléments de décoration', 'type_count' => 0]
    ];
}

?>

<div class="container">
    <!-- Bouton de retour -->
    <div style="margin-top: 1.5rem; margin-bottom: 1rem;">
        <a href="<?php echo isset($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'index.php'; ?>" 
           class="btn" 
           style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration: none;">
            ← Retour
        </a>
    </div>

    <!-- Liste des catégories -->
    <section id="categories-list">
        <h2 class="section-title">Toutes Nos Catégories</h2>
        
        <div class="categories" style="margin-bottom: 4rem;">
            <?php foreach ($categoriesList as $category): ?>
                <a href="types.php?category=<?php echo isset($category['id']) ? (int)$category['id'] : urlencode($category['name']); ?>" 
                   class="category-card" 
                   style="text-decoration: none; color: inherit; display: block;">
                    <div style="width: 100%; height: 200px; margin-bottom: 1rem; border-radius: 8px; overflow: hidden; background: var(--bg-light); display: flex; align-items: center; justify-content: center;">
                        <?php if (!empty($category['image'])): ?>
                            <img src="<?php echo htmlspecialchars($category['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                 style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="font-size: 4rem; color: var(--text-light);">📦</div>
                        <?php endif; ?>
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
                        <?php echo $category['type_count'] ?? 0; ?> type(s)
                    </p>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

</div>

<?php require_once 'includes/footer.php'; ?>

