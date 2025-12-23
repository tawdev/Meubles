<?php
// Configuration SEO pour la page Types de catégories
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$pageTitle = "Nos Types de catégories";
$pageMetaDescription = "Découvrez tous nos types de catégories de produits (canapés, lits, tables, bureaux, etc.) pour trouver rapidement le style de meuble qui vous convient.";
$pageKeywords = "types catégories, types de meubles, canapés, lits, tables, meubles salon, meubles chambre, frachdark";
$pageImage = $siteUrl . '/images/logo.jpg';

require_once 'includes/header.php';

// Récupérer la liste des catégories pour le filtre
$categoriesList = [];
try {
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categoriesList = $catStmt->fetchAll();
} catch (PDOException $e) {
    $categoriesList = [];
}

// Filtre catégorie depuis l'URL
$filterCategory = $_GET['category'] ?? '';

// Récupérer les types de catégories avec leur catégorie et le nombre de produits (avec filtre éventuel)
try {
    $sql = "
        SELECT tc.*,
               c.name  AS category_name,
               c.image AS category_image,
               t.name  AS type_name,
               t.id    AS type_id,
               (SELECT COUNT(*) FROM products p WHERE p.type_category_id = tc.id) AS product_count
        FROM types_categories tc
        LEFT JOIN categories c ON tc.category_id = c.id
        LEFT JOIN types t ON tc.types_id = t.id
        WHERE 1=1
    ";

    $params = [];

    if (!empty($filterCategory)) {
        // On filtre par ID de catégorie
        $sql .= " AND tc.category_id = ?";
        $params[] = (int)$filterCategory;
    }

    $sql .= " ORDER BY c.name, tc.name";

    if (!empty($params)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($sql);
    }

    $typesCategories = $stmt->fetchAll();
} catch (PDOException $e) {
    $typesCategories = [];
}

// Calculer le nombre de types et déterminer le nombre de colonnes pour la grille
$typesCount = count($typesCategories);
$cols = 4; // valeur par défaut

if ($typesCount === 1) {
    $cols = 1;
} elseif ($typesCount === 2) {
    $cols = 2;
} elseif (in_array($typesCount, [3, 6, 7, 9], true)) {
    $cols = 3;
} elseif (in_array($typesCount, [4, 8], true)) {
    $cols = 4;
} elseif (in_array($typesCount, [5, 10], true)) {
    $cols = 5;
} else {
    // Fallback pour d'autres nombres : garder une grille raisonnable
    if ($typesCount <= 3 && $typesCount > 0) {
        $cols = $typesCount;
    } elseif ($typesCount >= 11) {
        $cols = 5;
    } else {
        $cols = 4;
    }
}
?>

<div class="container">
    
    <!-- Filtres catégories -->
    <section style="padding: 2rem 0 1rem 0;">
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05);">
            <form method="GET" action="types.php" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="flex: 1; min-width: 220px;">
                    <label for="category" style="display: block; margin-bottom: 0.35rem; font-size: 0.9rem; color: var(--text-light);">
                        Filtrer par catégorie
                    </label>
                    <select id="category" name="category" onchange="this.form.submit()"
                            style="width: 100%; padding: 0.75rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($filterCategory !== '' && (int)$filterCategory === (int)$cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem; align-items: flex-end;">
                    <a href="types.php" class="btn" style="background: var(--text-light); text-decoration: none; padding: 0.75rem 1.5rem;">Réinitialiser</a>
                </div>
            </form>
        </div>
    </section>

    <!-- Liste des types de catégories -->
    <section id="types-categories-list">
        <h2 class="section-title" style="margin-top: 1rem;">Tous les types</h2>

        <div class="categories types-grid cols-<?php echo $cols; ?>">
            <?php if (empty($typesCategories)): ?>
                <p style="text-align: center; padding: 2rem; color: var(--text-light);">
                    Aucun type de catégorie trouvé pour le moment.
                </p>
            <?php else: ?>
                <?php foreach ($typesCategories as $type): ?>
                    <?php
                        // Image à afficher : priorité à l'image du type, sinon image de la catégorie
                        $cardImage = '';
                        if (!empty($type['image'])) {
                            $cardImage = $type['image'];
                        } elseif (!empty($type['category_image'])) {
                            $cardImage = $type['category_image'];
                        }

                        $categoryName = $type['category_name'] ?? 'Catégorie inconnue';
                        $typeName = $type['name'] ?? '';
                        $productCount = isset($type['product_count']) ? (int)$type['product_count'] : 0;
                    ?>
                    <a href="products.php?category=<?php echo urlencode($categoryName); ?>&type_category=<?php echo urlencode($type['id']); ?>" 
                       class="category-card"
                       style="text-decoration: none; color: inherit; display: block;">
                        <div style="width: 100%; height: 200px; margin-bottom: 1rem; border-radius: 8px; overflow: hidden; background: var(--bg-light); display: flex; align-items: center; justify-content: center;">
                            <?php if (!empty($cardImage)): ?>
                                <img src="<?php echo htmlspecialchars($cardImage); ?>" 
                                     alt="<?php echo htmlspecialchars($typeName ?: $categoryName); ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="this.src='images/placeholder.jpg';">
                            <?php else: ?>
                                <div style="font-size: 3.5rem; color: var(--text-light);">📦</div>
                            <?php endif; ?>
                        </div>
                        <h3 style="text-align: center; margin-bottom: 0.25rem;">
                            <?php echo htmlspecialchars($typeName ?: 'Type sans nom'); ?>
                        </h3>
                        <p style="text-align: center; color: var(--text-light); font-size: 0.9rem; margin-bottom: 0.5rem;">
                            <?php echo htmlspecialchars($categoryName); ?>
                            <?php if (!empty($type['type_name'])): ?>
                                • <?php echo htmlspecialchars($type['type_name']); ?>
                            <?php endif; ?>
                        </p>
                        <p style="text-align: center; color: var(--secondary-color); font-weight: 600; margin-top: 0.5rem;">
                            <?php echo $productCount; ?> produit(s)
                        </p>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php require_once 'includes/footer.php'; ?>


