<?php
// Configuration SEO pour la page produits
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$currentUrl = $siteUrl . $_SERVER['REQUEST_URI'];

// SEO dynamique basé sur les filtres
$seoTitleBase = "Catalogue de Meubles";
$seoDescriptionBase = "Découvrez notre catalogue complet de meubles de qualité au Maroc. Large sélection de meubles pour salon, chambre, salle à manger, bureau et décoration. Prix compétitifs, livraison rapide partout au Maroc.";

// Personnaliser selon la catégorie sélectionnée
$selectedCategory = $_GET['category'] ?? '';
if (!empty($selectedCategory)) {
    $categoryName = htmlspecialchars($selectedCategory);
    $seoTitleBase = "Meubles " . $categoryName . " - Catalogue Complet";
    $seoDescriptionBase = "Découvrez notre collection de meubles " . $categoryName . " au Maroc. Qualité premium, design moderne, livraison rapide. Parcourez notre sélection exclusive de meubles " . $categoryName . " pour votre intérieur.";
}

$pageTitle = $seoTitleBase;
$pageMetaDescription = $seoDescriptionBase;
$pageKeywords = "meubles maroc, achat meubles maroc, mobilier intérieur maroc, meubles salon maroc, meubles chambre maroc, meubles salle à manger maroc, meubles bureau maroc, décoration intérieure maroc, frachdark, meubles modernes maroc, meubles élégants maroc, livraison meubles maroc";
$pageImage = $siteUrl . '/images/logo.jpg';

require_once 'includes/header.php';

// Récupérer la catégorie, le type de catégorie, le type et le terme de recherche depuis l'URL si présents
$selectedCategory = $_GET['category'] ?? '';
$selectedTypeCategory = $_GET['type_category'] ?? '';
$selectedType = $_GET['type'] ?? '';
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Si un type_category est fourni, récupérer sa catégorie et son type associés
$selectedCategoryIdFromType = null;
if (!empty($selectedTypeCategory)) {
    try {
        $tcStmt = $pdo->prepare("SELECT category_id, types_id FROM types_categories WHERE id = ?");
        $tcStmt->execute([(int) $selectedTypeCategory]);
        $tcData = $tcStmt->fetch();

        if ($tcData) {
            $selectedCategoryIdFromType = $tcData['category_id'] ?? null;
            // Si aucun type explicite dans l'URL, utiliser le type lié au type_category
            if (empty($selectedType) && !empty($tcData['types_id'])) {
                $selectedType = (string) $tcData['types_id'];
            }
        }
    } catch (PDOException $e) {
        // En cas d'erreur, on ignore et on continue avec les paramètres fournis
        $selectedCategoryIdFromType = null;
    }
}

// Récupérer les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['id' => 1, 'name' => 'Salon', 'image' => ''],
        ['id' => 2, 'name' => 'Chambre', 'image' => ''],
        ['id' => 3, 'name' => 'Salle à manger', 'image' => ''],
        ['id' => 4, 'name' => 'Bureau', 'image' => ''],
        ['id' => 5, 'name' => 'Décoration', 'image' => '']
    ];
}

// Trouver l'ID de la catégorie sélectionnée
$selectedCategoryId = null;
if ($selectedCategory) {
    foreach ($categoriesList as $cat) {
        if (isset($cat['name']) && $cat['name'] === $selectedCategory) {
            $selectedCategoryId = isset($cat['id']) ? $cat['id'] : null;
            break;
        }
    }
}

// Si la catégorie n'est pas trouvée par son nom mais qu'on l'a obtenue via type_category, l'utiliser
if (!$selectedCategoryId && $selectedCategoryIdFromType) {
    $selectedCategoryId = $selectedCategoryIdFromType;
}

// Récupérer les types de catégorie si une catégorie est sélectionnée
$typesList = [];
if ($selectedCategoryId) {
    try {
        $typesStmt = $pdo->prepare("SELECT * FROM types_categories WHERE category_id = ? ORDER BY name");
        $typesStmt->execute([$selectedCategoryId]);
        $typesList = $typesStmt->fetchAll();
    } catch (PDOException $e) {
        $typesList = [];
    }
}

// Récupérer tous les types (En stock, Sur mesure)
$allTypes = [];
try {
    $typesStmt = $pdo->query("SELECT * FROM types ORDER BY name");
    $allTypes = $typesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table types n'existe pas encore
    $allTypes = [];
}

// Récupérer tous les produits avec leurs types de catégorie et types (en tenant compte des filtres)
try {
    $sql = "
        SELECT p.*, 
               tc.name  AS type_category_name,
               tc.id    AS type_category_id,
               t.id     AS type_id,
               t.name   AS type_name,
               p.max_length,
               p.max_width
        FROM products p
        LEFT JOIN types_categories tc ON p.type_category_id = tc.id
        LEFT JOIN types t ON tc.types_id = t.id
        WHERE 1=1
    ";

    $params = [];

    // Filtre par catégorie (ID de catégorie si disponible)
    if (!empty($selectedCategoryId)) {
        $sql .= " AND p.category_id = ?";
        $params[] = (int) $selectedCategoryId;
    } elseif (!empty($selectedCategory)) {
        // Fallback par nom de catégorie si pas d'ID
        $sql .= " AND p.category = ?";
        $params[] = $selectedCategory;
    }

    // Filtre par type de catégorie (types_categories)
    if (!empty($selectedTypeCategory)) {
        $sql .= " AND p.type_category_id = ?";
        $params[] = (int) $selectedTypeCategory;
    }

    // Filtre par type (En stock / Sur mesure)
    if (!empty($selectedType)) {
        $sql .= " AND t.id = ?";
        $params[] = (int) $selectedType;
    }

    // Filtre par recherche (nom du produit ou description)
    // Recherche non sensible à la casse et partielle (LIKE)
    if (!empty($searchTerm)) {
        $sql .= " AND (LOWER(p.name) LIKE LOWER(?) OR LOWER(p.description) LIKE LOWER(?))";
        $searchPattern = '%' . $searchTerm . '%';
        $params[] = $searchPattern;
        $params[] = $searchPattern;
    }

    $sql .= " ORDER BY p.id DESC";

    if (!empty($params)) {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($sql);
    }

    $allProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la jointure échoue, récupérer sans les infos de type
    try {
        $fallbackSql = "
            SELECT p.*, 
                   tc.name AS type_category_name,
                   tc.id   AS type_category_id,
                   p.max_length,
                   p.max_width
            FROM products p
            LEFT JOIN types_categories tc ON p.type_category_id = tc.id
            WHERE 1=1
        ";

        $params = [];

        if (!empty($selectedCategoryId)) {
            $fallbackSql .= " AND p.category_id = ?";
            $params[] = (int) $selectedCategoryId;
        } elseif (!empty($selectedCategory)) {
            $fallbackSql .= " AND p.category = ?";
            $params[] = $selectedCategory;
        }

        if (!empty($selectedTypeCategory)) {
            $fallbackSql .= " AND p.type_category_id = ?";
            $params[] = (int) $selectedTypeCategory;
        }

        $fallbackSql .= " ORDER BY p.id DESC";

        if (!empty($params)) {
            $stmt = $pdo->prepare($fallbackSql);
            $stmt->execute($params);
        } else {
            $stmt = $pdo->query($fallbackSql);
        }

        $allProducts = $stmt->fetchAll();
    } catch (PDOException $e2) {
        // Fallback final : sans jointure ni filtre
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
        $allProducts = $stmt->fetchAll();
    }
}

// Calculer le nombre de produits et déterminer le nombre de colonnes pour la grille
$productsCount = count($allProducts);
$productCols = 4; // valeur par défaut

if ($productsCount === 1) {
    $productCols = 1;
} elseif ($productsCount === 2) {
    $productCols = 2;
} elseif (in_array($productsCount, [3, 6, 7, 9], true)) {
    $productCols = 3;
} elseif (in_array($productsCount, [4, 8], true)) {
    $productCols = 4;
} elseif (in_array($productsCount, [5, 10], true)) {
    $productCols = 5;
} else {
    // Fallback pour d'autres nombres : garder une grille raisonnable
    if ($productsCount <= 3 && $productsCount > 0) {
        $productCols = $productsCount;
    } elseif ($productsCount >= 11) {
        $productCols = 5;
    } else {
        $productCols = 4;
    }
}
?>

<!-- Structured Data pour CollectionPage / ItemList -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "CollectionPage",
    "name": "<?php echo htmlspecialchars($pageTitle); ?>",
    "description": "<?php echo htmlspecialchars($pageMetaDescription); ?>",
    "url": "<?php echo htmlspecialchars($currentUrl); ?>",
    "mainEntity": {
        "@type": "ItemList",
        "numberOfItems": <?php echo count($allProducts); ?>,
        "itemListElement": [
            <?php
            $itemIndex = 1;
            foreach (array_slice($allProducts, 0, 10) as $product):
                ?>
                        {
                            "@type": "ListItem",
                            "position": <?php echo $itemIndex++; ?>,
                            "item": {
                                "@type": "Product",
                                "name": "<?php echo htmlspecialchars($product['name']); ?>",
                                "description": "<?php echo htmlspecialchars(substr($product['description'] ?? '', 0, 200)); ?>",
                                "image": "<?php echo htmlspecialchars($product['image']); ?>",
                                "url": "<?php echo $siteUrl; ?>/product.php?id=<?php echo $product['id']; ?>",
                                "offers": {
                                    "@type": "Offer",
                                    "price": "<?php echo $product['price']; ?>",
                                    "priceCurrency": "MAD",
                                    "availability": "https://schema.org/InStock"
                                },
                                "category": "<?php echo htmlspecialchars($product['category']); ?>"
                            }
                        }<?php if ($itemIndex <= 10 && $itemIndex <= count($allProducts))
                            echo ','; ?>
            <?php endforeach; ?>
        ]
    },
    "breadcrumb": {
        "@type": "BreadcrumbList",
        "itemListElement": [
            {
                "@type": "ListItem",
                "position": 1,
                "name": "Accueil",
                "item": "<?php echo $siteUrl; ?>/index.php"
            },
            {
                "@type": "ListItem",
                "position": 2,
                "name": "<?php echo htmlspecialchars($pageTitle); ?>",
                "item": "<?php echo htmlspecialchars($currentUrl); ?>"
            }
        ]
    }
}
</script>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="Fil d'Ariane" style="margin-top: 1.5rem; margin-bottom: 1rem;">
        <ol
            style="display: flex; align-items: center; gap: 0.5rem; list-style: none; padding: 0; margin: 0; flex-wrap: wrap;">
            <li><a href="<?php echo $siteUrl; ?>/index.php"
                    style="color: var(--primary-color); text-decoration: none;">Accueil</a></li>
            <li style="color: var(--text-light);">/</li>
            <li style="color: var(--text-dark); font-weight: 600;">
                <?php echo !empty($selectedCategory) ? htmlspecialchars("Meubles " . $selectedCategory) : "Catalogue"; ?>
            </li>
        </ol>
    </nav>

    <!-- Bouton de retour -->
    <div style="margin-top: 0.5rem; margin-bottom: 1rem;">
        <a href="<?php echo $siteUrl; ?>/index.php" class="btn"
            style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; text-decoration: none;"
            aria-label="Retour à la page d'accueil">
            ← Retour
        </a>
    </div>


    <!-- Slider de catégories -->
    <?php if (!empty($categoriesList)): ?>
        <section class="categories-slider-section" aria-label="Navigation par catégories">
            <div class="categories-slider-header">
                <h2 class="section-subtitle">Parcourir par catégorie</h2>
            </div>
            <div class="categories-slider-wrapper">
                <button type="button" class="categories-slider-arrow prev" aria-label="Précédent">
                    ‹
                </button>
                <div class="categories-slider" id="categories-slider">
                    <?php foreach ($categoriesList as $category): ?>
                        <?php
                        $catId = isset($category['id']) ? (int) $category['id'] : null;
                        $catName = isset($category['name']) ? $category['name'] : '';
                        $catImg = !empty($category['image']) ? $category['image'] : 'images/categories/default-category.jpg';

                        // Construire l'URL vers products.php avec filtre catégorie
                        $categoryUrl = 'products.php';
                        if ($catId) {
                            $categoryUrl .= '?category=' . urlencode($catName);
                        } else {
                            $categoryUrl .= '?category=' . urlencode($catName);
                        }
                        ?>
                        <a href="<?php echo htmlspecialchars($categoryUrl); ?>" class="category-slide"
                            onclick="event.preventDefault(); filterProductsByCategoryFromSlide('<?php echo $catId ?: htmlspecialchars($catName, ENT_QUOTES); ?>');">
                            <div class="category-slide-image-wrapper">
                                <img src="<?php echo htmlspecialchars($catImg); ?>"
                                    alt="<?php echo htmlspecialchars($catName); ?>" loading="lazy">
                                <div class="category-slide-overlay"></div>
                            </div>
                            <div class="category-slide-content">
                                <span class="category-slide-name"><?php echo htmlspecialchars($catName); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="categories-slider-arrow next" aria-label="Suivant">
                    ›
                </button>
            </div>
        </section>
        <!-- Slider des types de catégories (rempli dynamiquement selon la catégorie) -->
        <section class="type-categories-slider-section" id="type-categories-slider-section" style="display: none;"
            aria-label="Types de produits disponibles">
            <div class="categories-slider-header">
                <h2 class="section-subtitle">Types disponibles</h2>
            </div>
            <div class="categories-slider-wrapper">
                <button type="button" class="categories-slider-arrow prev" aria-label="Précédent">
                    ‹
                </button>
                <div class="categories-slider" id="type-categories-slider">
                    <!-- Slides de types de catégories ajoutés en JS -->
                </div>
                <button type="button" class="categories-slider-arrow next" aria-label="Suivant">
                    ›
                </button>
            </div>
        </section>

        <!-- Slider des articles spécifiques (rempli dynamiquement selon le type de catégorie) -->
        <section class="items-slider-section" id="items-slider-section" style="display: none;"
            aria-label="Articles spécifiques disponibles">
            <div class="categories-slider-header">
                <h2 class="section-subtitle">Articles spécifiques</h2>
            </div>
            <div class="categories-slider-wrapper">
                <button type="button" class="categories-slider-arrow prev" aria-label="Précédent">
                    ‹
                </button>
                <div class="categories-slider" id="items-slider">
                    <!-- Slides d'articles ajoutés en JS -->
                </div>
                <button type="button" class="categories-slider-arrow next" aria-label="Suivant">
                    ›
                </button>
            </div>
        </section>
    <?php endif; ?>

    <!-- Section Produits -->
    <section id="products">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h1 class="section-title" style="margin-bottom: 0;">
                <?php
                if (!empty($selectedCategory)) {
                    echo htmlspecialchars("Meubles " . $selectedCategory);
                } else {
                    echo "Catalogue Complet de Meubles";
                }
                ?>
            </h1>
            <div id="results-count"
                style="background: var(--bg-light); padding: 0.75rem 1.5rem; border-radius: 25px; color: var(--primary-color); font-weight: 600; font-size: 1rem;">
                <span id="results-number"><?php echo count($allProducts); ?></span> produit(s) trouvé(s)
            </div>
        </div>

        <!-- Filtres améliorés (masqués à l'affichage, utilisés uniquement par le JS) -->
        <div
            style="display: none; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05);">
            <div
                style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px; order: 0;">
                    <select id="filter-category" onchange="onCategoryChange()"
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                        <option value="all">Toutes les catégories</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option
                                value="<?php echo isset($category['id']) ? $category['id'] : htmlspecialchars($category['name']); ?>"
                                data-name="<?php echo htmlspecialchars($category['name']); ?>" <?php echo ($selectedCategory === $category['name'] || (isset($category['id']) && $selectedCategoryId == $category['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="type-category-filter-container" class="hidden"
                    style="display: none; align-items: center; gap: 1rem; flex: 1; min-width: 200px; order: 1;">
                    <label for="filter-type-category" style="display: none;">Type de catégorie</label>
                    <select id="filter-type-category" onchange="filterProducts()"
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; width: 100%;">
                        <option value="all">Tous les types</option>
                        <?php foreach ($typesList as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo ($selectedTypeCategory && $selectedTypeCategory == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px; order: 1.5;">
                    <label for="filter-type" style="display: none;">Type</label>
                    <select id="filter-type" onchange="onTypeChange()"
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; width: 100%;">
                        <option value="all">Tous les types (stock/mesure)</option>
                        <?php foreach ($allTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>" <?php echo ($selectedType && $selectedType == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div
                    style="display: flex; align-items: center; gap: 0.5rem; flex: 2; min-width: 250px; position: relative; order: 2;">
                    <input type="text" id="search-products" placeholder="Rechercher un produit..."
                        oninput="filterProducts()"
                        style="flex: 1; padding: 0.875rem 1rem 0.875rem 2.75rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                    <span style="position: absolute; left: 1rem; color: var(--text-light); font-size: 1.1rem;">🔍</span>
                </div>

                <button onclick="resetFilters()"
                    style="padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-size: 0.95rem; background: var(--primary-color); color: white; cursor: pointer; font-weight: 600; transition: all 0.3s ease; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1); order: 3;"
                    onmouseover="this.style.background='#1a252f'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)';"
                    onmouseout="this.style.background='var(--primary-color)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 5px rgba(0,0,0,0.1)';">
                    Réinitialiser
                </button>
            </div>
        </div>

        <!-- Grille de produits améliorée -->
        <div class="products-grid products-grid-dynamic cols-<?php echo $productCols; ?>">
            <?php if (empty($allProducts)): ?>
                <div
                    style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; background: var(--bg-light); border-radius: 15px;">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;"><?php echo !empty($searchTerm) ? '🔍' : '📦'; ?>
                    </div>
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">
                        <?php echo !empty($searchTerm) ? 'Aucun produit trouvé' : 'Aucun produit disponible'; ?>
                    </h3>
                    <p style="color: var(--text-light); font-size: 1.1rem;">
                        <?php if (!empty($searchTerm)): ?>
                            Aucun produit ne correspond à votre recherche "<?php echo htmlspecialchars($searchTerm); ?>".<br>
                            Essayez avec d'autres mots-clés ou <a href="products.php"
                                style="color: var(--primary-color); text-decoration: underline;">parcourez tous nos
                                produits</a>.
                        <?php else: ?>
                            Revenez bientôt pour découvrir nos nouveaux produits !
                        <?php endif; ?>
                    </p>
                </div>
            <?php else: ?>
                <?php foreach ($allProducts as $product): ?>
                    <div class="product-card" data-id="<?php echo $product['id']; ?>"
                        data-category="<?php echo htmlspecialchars($product['category']); ?>"
                        data-category-id="<?php echo $product['category_id'] ?? ''; ?>"
                        data-type-category-id="<?php echo $product['type_category_id'] ?? ''; ?>"
                        data-type-category-name="<?php echo htmlspecialchars($product['type_category_name'] ?? ''); ?>"
                        data-type-id="<?php echo $product['type_id'] ?? ''; ?>"
                        data-type-name="<?php echo htmlspecialchars($product['type_name'] ?? ''); ?>"
                        data-type-item-id="<?php echo $product['types_categories_items_id'] ?? ''; ?>"
                        data-price="<?php echo $product['price']; ?>" style="overflow: hidden; position: relative;">
                        <!-- Badge catégorie -->
                        <div
                            style="position: absolute; top: 1rem; right: 1rem; background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; z-index: 10;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>

                        <!-- Image produit -->
                        <div style="position: relative; overflow: hidden; height: 280px; background: var(--bg-light);">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>"
                                alt="<?php echo htmlspecialchars($product['name'] . ' - Meubles ' . $product['category'] . ' - Frachdark Maroc'); ?>"
                                class="product-image" loading="lazy" width="300" height="280"
                                style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                onerror="this.src='https://via.placeholder.com/300x280?text=Produit'" fetchpriority="<?php
                                static $imgIndex = 0;
                                $imgIndex++;
                                echo ($imgIndex <= 3) ? 'high' : 'low';
                                ?>">
                            <div
                                style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.3), transparent); height: 50%;">
                            </div>
                        </div>

                        <div class="product-info">
                            <h3 class="product-name">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <div class="product-price">
                                <?php echo number_format($product['price'], 2, ',', ' '); ?> DH
                            </div>
                            <div class="product-actions">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="btn"
                                    style="flex: 1; text-align: center; padding: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                                    👁️ Voir détails
                                </a>
                                <button class="btn-add-cart" data-id="<?php echo $product['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($product['image']); ?>"
                                    data-type-name="<?php echo htmlspecialchars($product['type_name'] ?? ''); ?>"
                                    data-max-length="<?php echo isset($product['max_length']) && $product['max_length'] ? $product['max_length'] : ''; ?>"
                                    data-max-width="<?php echo isset($product['max_width']) && $product['max_width'] ? $product['max_width'] : ''; ?>"
                                    style="flex: 1; text-align: center; padding: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                    title="Ajouter au panier">
                                    🛒 Ajouter
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
    /* Amélioration des cartes produits */
    .product-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .product-card:hover .product-image {
        transform: scale(1.05);
    }

    /* Amélioration des filtres */
    #filter-category:focus,
    #filter-type-category:focus,
    #filter-type:focus,
    #search-products:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15), 0 4px 10px rgba(0, 0, 0, 0.1);
        transform: translateY(-1px);
    }

    #filter-category:hover,
    #filter-type-category:hover,
    #filter-type:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    #search-products:hover {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
    }

    /* Style pour le conteneur des types de catégories */
    #type-category-filter-container {
        transition: opacity 0.3s ease, visibility 0.3s ease;
        align-items: center;
        gap: 1rem;
        flex: 1;
        min-width: 200px;
    }

    #type-category-filter-container:not(.hidden) {
        display: flex !important;
        opacity: 1 !important;
        visibility: visible !important;
        width: auto !important;
        max-width: none !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        position: relative !important;
        z-index: 10 !important;
        background: transparent !important;
        border: none !important;
        margin: 0 !important;
        padding: 0 !important;
        order: 1 !important;
    }

    /* Forcer l'affichage même si la classe hidden est présente mais override par JS */
    #type-category-filter-container[style*="display: flex"] {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
    }

    #type-category-filter-container.hidden {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        max-height: 0 !important;
        overflow: hidden !important;
        min-width: 0 !important;
        width: 0 !important;
    }

    /* Style pour le slider d'articles */
    .items-slider-section {
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .item-slide {
        flex: 0 0 auto;
        width: 150px;
        cursor: pointer;
        text-align: center;
        border: none;
        background: none;
        padding: 0;
        transition: all 0.3s ease;
    }

    .item-slide.active .category-slide-image-wrapper {
        border: 3px solid var(--primary-color);
        transform: scale(1.05);
    }

    .item-slide.active .category-slide-name {
        color: var(--primary-color);
        font-weight: 700;
    }

    #filter-type-category {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        width: 100% !important;
        min-width: 200px !important;
    }

    #filter-type-category:disabled {
        opacity: 0.6 !important;
        cursor: wait !important;
    }

    /* Animation pour le compteur */
    #results-count {
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive amélioré */
    @media (max-width: 768px) {
        .products-grid {
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        /* Fix pour products-grid-dynamic sur mobile */
        .products-grid-dynamic {
            grid-template-columns: 1fr !important;
            gap: 1.5rem !important;
        }

        .products-grid-dynamic.cols-1,
        .products-grid-dynamic.cols-2,
        .products-grid-dynamic.cols-3,
        .products-grid-dynamic.cols-4,
        .products-grid-dynamic.cols-5 {
            grid-template-columns: 1fr !important;
        }

        /* Ajuster les cartes produits */
        .product-card {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
        }

        .product-image {
            height: 220px !important;
        }

        .product-info {
            padding: 1rem !important;
        }

        .product-name {
            font-size: 1.1rem !important;
        }

        .product-price {
            font-size: 1.3rem !important;
        }

        .product-actions {
            flex-direction: column;
            gap: 0.75rem;
        }

        .product-actions .btn,
        .product-actions .btn-add-cart {
            width: 100%;
        }

        .filters {
            padding: 1.5rem;
        }

        .filters>div {
            grid-template-columns: 1fr;
        }

        .section-title {
            font-size: 2rem !important;
        }

        #results-count {
            font-size: 0.9rem !important;
            padding: 0.6rem 1.2rem !important;
        }
    }

    @media (max-width: 480px) {
        .products-grid-dynamic {
            grid-template-columns: 1fr !important;
            gap: 1rem !important;
        }

        .product-card {
            width: 100% !important;
            margin: 0 !important;
        }

        .product-image {
            height: 200px !important;
        }

        .section-title {
            font-size: 1.8rem !important;
        }
    }
</style>

<script>
    // Filtre déclenché depuis le slider de catégories
    function filterProductsByCategoryFromSlide(categoryValue) {
        const select = document.getElementById('filter-category');
        if (!select) return;

        // Trouver le nom de la catégorie depuis le select
        let categoryName = '';
        let categoryId = '';

        for (let i = 0; i < select.options.length; i++) {
            const opt = select.options[i];
            if (opt.value == categoryValue || (opt.dataset.name && opt.dataset.name == categoryValue)) {
                categoryName = opt.dataset.name || opt.textContent.trim();
                categoryId = opt.value;
                break;
            }
        }

        // Si "all" ou pas trouvé, rediriger vers products.php sans filtre
        if (!categoryName || categoryValue === 'all' || categoryValue === '') {
            window.location.href = 'products.php';
            return;
        }

        // Rediriger vers products.php avec le nouveau filtre catégorie
        const newUrl = 'products.php?category=' + encodeURIComponent(categoryName);
        window.location.href = newUrl;
    }

    // Filtre déclenché depuis le slider des types de catégories
    function filterProductsByTypeCategoryFromSlide(typeCategoryId) {
        if (!typeCategoryId || typeCategoryId === 'all' || typeCategoryId === '') {
            return;
        }

        // Récupérer la catégorie actuelle depuis l'URL ou le select
        const urlParams = new URLSearchParams(window.location.search);
        const currentCategory = urlParams.get('category') || '';

        const categorySelect = document.getElementById('filter-category');
        let categoryName = currentCategory;

        // Si pas de catégorie dans l'URL, essayer de la récupérer depuis le select
        if (!categoryName && categorySelect && categorySelect.value && categorySelect.value !== 'all') {
            const selectedOption = categorySelect.options[categorySelect.selectedIndex];
            categoryName = selectedOption ? (selectedOption.dataset.name || selectedOption.textContent.trim()) : '';
        }

        // Régler le select
        const typeCatSelect = document.getElementById('filter-type-category');
        if (typeCatSelect) {
            typeCatSelect.value = typeCategoryId;
        }

        // Charger les articles pour ce type
        loadItemsByType(typeCategoryId);

        // Appliquer le filtre localement au lieu de recharger la page
        filterProducts();
    }

    // Filtre déclenché depuis le slider des articles
    function filterProductsByItemFromSlide(itemId) {
        if (!itemId || itemId === 'all' || itemId === '') {
            return;
        }

        // Activer le slide
        document.querySelectorAll('.item-slide').forEach(el => el.classList.remove('active'));
        const activeSlide = document.querySelector(`.item-slide[data-id="${itemId}"]`);
        if (activeSlide) activeSlide.classList.add('active');

        // Appliquer le filtre
        filterProducts();
    }

    function filterProductsByCategory(category) {
        if (category === 'all') {
            document.getElementById('filter-category').value = 'all';
        } else {
            document.getElementById('filter-category').value = category;
        }
        filterProducts();
    }

    function resetFilters() {
        document.getElementById('filter-category').value = 'all';
        document.getElementById('search-products').value = '';
        const typeFilter = document.getElementById('filter-type-category');
        if (typeFilter) {
            typeFilter.value = 'all';
        }
        const typeFilterType = document.getElementById('filter-type');
        if (typeFilterType) {
            typeFilterType.value = 'all';
        }
        const typeContainer = document.getElementById('type-category-filter-container');
        if (typeContainer) {
            typeContainer.classList.add('hidden');
            typeContainer.style.display = 'none';
        }
        // Cacher le slider des types de catégories
        const typeSliderSection = document.getElementById('type-categories-slider-section');
        const typeSlider = document.getElementById('type-categories-slider');
        if (typeSliderSection && typeSlider) {
            typeSliderSection.style.display = 'none';
            typeSlider.innerHTML = '';
        }
        // Cacher le slider des articles
        const itemSliderSection = document.getElementById('items-slider-section');
        const itemSlider = document.getElementById('items-slider');
        if (itemSliderSection && itemSlider) {
            itemSliderSection.style.display = 'none';
            itemSlider.innerHTML = '';
        }
        filterProducts();
    }

    // ID de type de catégorie initial provenant de l'URL (si présent)
    let initialTypeCategoryId = "<?php echo $selectedTypeCategory ? (int) $selectedTypeCategory : ''; ?>";

    // Charger les types de catégorie
    function loadTypesByCategory(categoryId) {
        const typeSelect = document.getElementById('filter-type-category');
        const typeContainer = document.getElementById('type-category-filter-container');
        const typeFilter = document.getElementById('filter-type');
        const typeSliderSection = document.getElementById('type-categories-slider-section');
        const typeSlider = document.getElementById('type-categories-slider');

        if (!typeSelect || !typeContainer) {
            return;
        }

        // Réinitialiser
        typeSelect.innerHTML = '<option value="all">Tous les types</option>';
        typeSelect.value = 'all';

        if (!categoryId || categoryId === 'all' || categoryId === '' || categoryId === '0') {
            typeContainer.classList.add('hidden');
            typeContainer.style.display = 'none';
            if (typeSliderSection && typeSlider) {
                typeSliderSection.style.display = 'none';
                typeSlider.innerHTML = '';
            }
            return;
        }

        // Récupérer le type sélectionné (En stock / Sur mesure)
        const selectedTypeId = typeFilter ? typeFilter.value : 'all';

        // Afficher le conteneur immédiatement
        typeContainer.classList.remove('hidden');
        typeContainer.style.display = 'flex';
        typeContainer.style.visibility = 'visible';
        typeContainer.style.opacity = '1';
        typeContainer.style.width = 'auto';
        typeContainer.style.minWidth = '200px';
        typeContainer.style.maxHeight = 'none';
        typeContainer.style.overflow = 'visible';

        typeSelect.disabled = true;
        typeSelect.innerHTML = '<option value="all">Chargement...</option>';

        // Charger les types via AJAX
        // IMPORTANT :
        // - Si on arrive avec un type_category dans l'URL (initialTypeCategoryId),
        //   on NE filtre PAS par type_id côté AJAX, afin que ce type_category soit toujours présent.
        // - Sinon (interaction utilisateur), on peut filtrer par type_id.
        let apiUrl = `admin/get_types_by_category.php?category_id=${categoryId}`;
        if (!initialTypeCategoryId && selectedTypeId && selectedTypeId !== 'all' && selectedTypeId !== '') {
            apiUrl += `&type_id=${selectedTypeId}`;
        }

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Erreur réseau: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                typeSelect.disabled = false;
                typeSelect.innerHTML = '<option value="all">Tous les types</option>';

                if (data.success && data.types && data.types.length > 0) {
                    data.types.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        typeSelect.appendChild(option);
                    });
                    // Construire le slider des types sous forme de cartes avec image
                    if (typeSliderSection && typeSlider) {
                        typeSlider.innerHTML = '';
                        data.types.forEach(type => {
                            const slide = document.createElement('button');
                            slide.type = 'button';
                            slide.className = 'type-category-card';
                            slide.setAttribute('data-id', type.id);

                            const imgSrc = type.image && type.image !== ''
                                ? type.image
                                : 'images/types/default-type.jpg';

                            slide.innerHTML = `
                            <div class="type-category-card-image-wrapper">
                                <img src="${imgSrc}"
                                     alt="${type.name ? String(type.name).replace(/"/g, '&quot;') : ''}">
                                <div class="type-category-card-overlay"></div>
                            </div>
                            <div class="type-category-card-content">
                                <span class="type-category-card-name">${type.name || ''}</span>
                            </div>
                        `;

                            slide.addEventListener('click', function () {
                                // Activer la carte cliquée
                                document
                                    .querySelectorAll('.type-category-card.active')
                                    .forEach(el => el.classList.remove('active'));
                                this.classList.add('active');

                                filterProductsByTypeCategoryFromSlide(String(type.id));
                            });

                            typeSlider.appendChild(slide);
                        });
                        typeSliderSection.style.display = 'block';
                    }
                } else {
                    // Ajouter une option pour indiquer qu'il n'y a pas de types
                    const noTypeOption = document.createElement('option');
                    noTypeOption.value = 'none';
                    noTypeOption.textContent = 'Aucun type disponible';
                    noTypeOption.disabled = true;
                    typeSelect.appendChild(noTypeOption);
                    // Cacher le slider des types s'il n'y a pas de données
                    if (typeSliderSection && typeSlider) {
                        typeSliderSection.style.display = 'none';
                        typeSlider.innerHTML = '';
                    }
                }

                // Forcer l'affichage - TOUJOURS afficher même s'il n'y a pas de types
                typeContainer.classList.remove('hidden');

                // Forcer le reflow avant de modifier les styles
                void typeContainer.offsetHeight;

                // Nettoyer tous les styles inline et réappliquer
                typeContainer.removeAttribute('style');

                // Réappliquer seulement les styles nécessaires avec setProperty pour forcer
                typeContainer.style.setProperty('display', 'flex', 'important');
                typeContainer.style.setProperty('visibility', 'visible', 'important');
                typeContainer.style.setProperty('opacity', '1', 'important');
                typeContainer.style.setProperty('align-items', 'center', 'important');
                typeContainer.style.setProperty('gap', '1rem', 'important');
                typeContainer.style.setProperty('flex', '1', 'important');
                typeContainer.style.setProperty('min-width', '200px', 'important');

                // Si un type_category est présent dans l'URL, le sélectionner après chargement
                if (initialTypeCategoryId && typeSelect.querySelector(`option[value="${initialTypeCategoryId}"]`)) {
                    typeSelect.value = String(initialTypeCategoryId);
                    // Charger aussi les articles
                    loadItemsByType(initialTypeCategoryId);
                    // Appliquer le filtrage une fois que la valeur est sélectionnée
                    if (typeof window.filterProducts === 'function') {
                        window.filterProducts();
                    }
                    // Éviter de réutiliser cette valeur lors des changements ultérieurs de catégorie
                    initialTypeCategoryId = "";
                }
                typeContainer.style.setProperty('position', 'relative', 'important');
                // ... rest of the code inherited
            })
            .catch(error => {
                console.error('❌ ERREUR lors du chargement des types:', error);
                typeSelect.disabled = false;
                typeSelect.innerHTML = '<option value="all">Tous les types</option>';
                typeContainer.classList.add('hidden');
                typeContainer.style.display = 'none';
            });
    }

    // Charger les articles par type de catégorie
    function loadItemsByType(typeCategoryId) {
        const itemSliderSection = document.getElementById('items-slider-section');
        const itemSlider = document.getElementById('items-slider');

        if (!itemSliderSection || !itemSlider) return;

        if (!typeCategoryId || typeCategoryId === 'all' || typeCategoryId === '') {
            itemSliderSection.style.display = 'none';
            itemSlider.innerHTML = '';
            return;
        }

        fetch(`admin/get_items_by_type.php?type_category_id=${typeCategoryId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.items && data.items.length > 0) {
                    itemSlider.innerHTML = '';
                    data.items.forEach(item => {
                        const slide = document.createElement('button');
                        slide.type = 'button';
                        slide.className = 'item-slide';
                        slide.setAttribute('data-id', item.id);

                        const imgSrc = item.image && item.image !== ''
                            ? (item.image.startsWith('http') ? item.image : item.image)
                            : 'images/placeholder-item.jpg';

                        slide.innerHTML = `
                        <div class="category-slide-image-wrapper">
                            <img src="${imgSrc}" alt="${item.name}">
                            <div class="category-slide-overlay"></div>
                        </div>
                        <div class="category-slide-content">
                            <span class="category-slide-name">${item.name}</span>
                        </div>
                    `;

                        slide.addEventListener('click', function () {
                            const isAlreadyActive = this.classList.contains('active');
                            document.querySelectorAll('.item-slide').forEach(el => el.classList.remove('active'));

                            if (!isAlreadyActive) {
                                this.classList.add('active');
                                filterProductsByItemFromSlide(item.id);
                            } else {
                                // Désélectionner
                                filterProducts(); // Re-filtrer sans l'item
                            }
                        });

                        itemSlider.appendChild(slide);
                    });
                    itemSliderSection.style.display = 'block';
                } else {
                    itemSliderSection.style.display = 'none';
                    itemSlider.innerHTML = '';
                }
            })
            .catch(error => {
                console.error('Erreur lors du chargement des articles:', error);
                itemSliderSection.style.display = 'none';
            });
    }

    // Gérer le changement de catégorie
    function onCategoryChange() {
        const categorySelect = document.getElementById('filter-category');
        if (!categorySelect) {
            return;
        }

        const categoryId = categorySelect.value;

        // Réinitialiser le filtre de type de catégorie
        const typeFilter = document.getElementById('filter-type-category');
        if (typeFilter) {
            typeFilter.value = 'all';
        }

        // Charger les types pour cette catégorie (en tenant compte du type sélectionné)
        loadTypesByCategory(categoryId);

        // Appliquer le filtre après un court délai pour laisser le temps de charger les types
        setTimeout(function () {
            filterProducts();
        }, 300);
    }

    // Gérer le changement de type (En stock / Sur mesure)
    function onTypeChange() {
        const categorySelect = document.getElementById('filter-category');
        const categoryId = categorySelect ? categorySelect.value : 'all';

        // Réinitialiser le filtre de type de catégorie
        const typeCategoryFilter = document.getElementById('filter-type-category');
        if (typeCategoryFilter) {
            typeCategoryFilter.value = 'all';
        }

        // Recharger les types de catégorie en fonction du type sélectionné
        if (categoryId && categoryId !== 'all' && categoryId !== '') {
            loadTypesByCategory(categoryId);
        }

        // Appliquer le filtre
        setTimeout(function () {
            filterProducts();
        }, 300);
    }

    // Mettre à jour le compteur de résultats
    function updateResultsCount() {
        const productCards = document.querySelectorAll('.product-card');
        const visible = Array.from(productCards).filter(card => {
            return card.style.display !== 'none';
        }).length;

        const resultsCount = document.getElementById('results-count');
        const resultsNumber = document.getElementById('results-number');

        if (resultsCount && resultsNumber) {
            resultsNumber.textContent = visible;

            // Animation
            resultsCount.style.animation = 'none';
            setTimeout(() => {
                resultsCount.style.animation = 'fadeIn 0.5s ease';
            }, 10);
        }
    }

    // La fonction filterProducts sera redéfinie après le chargement de script.js
    // pour supporter type_category_id

    // Initialiser le compteur au chargement et appliquer les filtres si une catégorie est sélectionnée
    document.addEventListener('DOMContentLoaded', function () {
        console.log('📄 DOM Content Loaded - Initialisation');

        // S'assurer que tous les produits sont visibles par défaut
        const productCards = document.querySelectorAll('.product-card');
        console.log('📦 Produits trouvés dans le DOM:', productCards.length);

        productCards.forEach(card => {
            if (!card.style.display || card.style.display === 'none') {
                card.style.display = 'flex';
            }
        });

        updateResultsCount();

        const typeContainer = document.getElementById('type-category-filter-container');
        const categorySelect = document.getElementById('filter-category');

        <?php if ($selectedCategoryId): ?>
        // Si une catégorie est sélectionnée, afficher le conteneur des types
        if (typeContainer) {
            typeContainer.classList.remove('hidden');
            typeContainer.style.display = 'flex';
        }
        <?php if (empty($selectedTypeCategory)): ?>
        // Si aucun type_category n'est imposé par l'URL, charger dynamiquement les types
        setTimeout(function () {
            loadTypesByCategory(<?php echo $selectedCategoryId; ?>);
        }, 100);
        <?php endif; ?>
        <?php endif; ?>

        <?php if ($selectedCategory || $selectedTypeCategory || $selectedType): ?>
        // Appliquer le filtre si présent dans l'URL
        setTimeout(function () {
            filterProducts();
        }, 200);
        <?php else: ?>
        // Si aucun filtre dans l'URL, s'assurer que tous les produits sont visibles
        console.log('📋 Aucun filtre dans l\'URL, affichage de tous les produits');

        // Forcer l'affichage de tous les produits immédiatement
        productCards.forEach(card => {
            card.style.display = 'flex';
        });
        updateResultsCount();

        // Appeler filterProducts pour s'assurer que tous les produits sont visibles
        setTimeout(function () {
            console.log('🔄 Appel de filterProducts après 100ms');
            filterProducts();
        }, 100);
        <?php endif; ?>

        // S'assurer que le select de catégorie fonctionne correctement
        // Ne pas recharger les types ici si un type_category est déjà imposé par l'URL,
        // pour éviter d'écraser la valeur sélectionnée côté PHP.
        <?php if (empty($selectedTypeCategory)): ?>
        if (categorySelect && categorySelect.value && categorySelect.value !== 'all') {
            setTimeout(function () {
                loadTypesByCategory(categorySelect.value);
            }, 150);
        }
        <?php endif; ?>
    });

    // S'assurer que filterProducts est définie après le chargement de script.js
    window.addEventListener('load', function () {
        console.log('📦 Page complètement chargée, redéfinition de filterProducts');

        // Redéfinir filterProducts pour supporter type_category_id
        window.filterProducts = function () {
            console.log('🚀 filterProducts appelée (version mise à jour)');

            const category = document.getElementById('filter-category')?.value || 'all';
            const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';

            const productCards = document.querySelectorAll('.product-card');

            const typeCategory = document.getElementById('filter-type-category')?.value || 'all';
            const type = document.getElementById('filter-type')?.value || 'all';

            console.log('🔍 Filtrage des produits:', {
                category,
                typeCategory,
                type,
                searchTerm,
                totalProducts: productCards.length
            });

            if (productCards.length === 0) {
                console.warn('⚠️ Aucun produit trouvé dans le DOM!');
                return;
            }

            let visibleCount = 0;
            let hiddenByCategory = 0;
            let hiddenByType = 0;
            let hiddenByTypeCategory = 0;
            let hiddenBySearch = 0;

            function applyFilters(applyTypeFilters = true) {
                visibleCount = 0;
                hiddenByCategory = 0;
                hiddenByType = 0;
                hiddenByTypeCategory = 0;
                hiddenBySearch = 0;

                productCards.forEach((card, index) => {
                    const productCategory = card.dataset.category || '';
                    const productCategoryId = card.dataset.categoryId || '';
                    const productTypeCategoryId = card.dataset.typeCategoryId || '';
                    const productTypeId = card.dataset.typeId || '';
                    const productTypeItemId = card.dataset.typeItemId || '';
                    const productName = card.querySelector('.product-name')?.textContent || '';

                    let show = true;
                    let reason = '';

                    // Filtre par catégorie désactivé côté JS.
                    // Le filtrage par catégorie est déjà fait côté PHP dans la requête SQL,
                    // donc on ne re-filtre plus au niveau du navigateur pour éviter les "category mismatch".

                    if (applyTypeFilters) {
                        // Filtre par type de catégorie
                        if (show && typeCategory !== 'all' && typeCategory !== '' && typeCategory !== 'none') {
                            if (productTypeCategoryId && productTypeCategoryId !== typeCategory) {
                                show = false;
                                reason = 'type category mismatch: ' + productTypeCategoryId + ' !== ' + typeCategory;
                                hiddenByTypeCategory++;
                            } else if (!productTypeCategoryId && typeCategory !== 'all') {
                                show = false;
                                reason = 'no type_category_id';
                                hiddenByTypeCategory++;
                            }
                        }

                        // Filtre par type (En stock, Sur mesure)
                        if (show && type !== 'all' && type !== '') {
                            if (productTypeId && productTypeId !== type) {
                                show = false;
                                reason = 'type mismatch: ' + productTypeId + ' !== ' + type;
                                hiddenByType++;
                            }
                        }

                        // Filtre par article spécifique (item)
                        if (show) {
                            const activeItemSlide = document.querySelector('.item-slide.active');
                            if (activeItemSlide) {
                                const selectedItemId = activeItemSlide.dataset.id;
                                if (productTypeItemId !== selectedItemId) {
                                    show = false;
                                    reason = 'item mismatch: ' + productTypeItemId + ' !== ' + selectedItemId;
                                    hiddenByType++; // Recategorize as type mismatch for count
                                }
                            }
                        }
                    }

                    // Filtre par recherche (toujours appliqué)
                    if (show && searchTerm) {
                        const productNameLower = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
                        const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
                        if (!productNameLower.includes(searchTerm) && !productDesc.includes(searchTerm)) {
                            show = false;
                            reason = 'search term not found';
                            hiddenBySearch++;
                        }
                    }

                    card.style.display = show ? 'flex' : 'none';

                    if (show) {
                        visibleCount++;
                    } else if (index < 3) {
                        console.log('❌ Produit caché:', productName, '- Raison:', reason, {
                            productCategory,
                            productCategoryId,
                            productTypeCategoryId,
                            productTypeId,
                            category,
                            typeCategory,
                            type
                        });
                    }
                });
            }

            // 1) Appliquer tous les filtres normalement
            applyFilters(true);

            // 2) Si aucun produit visible, relâcher les filtres de type / type_category
            if (visibleCount === 0 && (type !== 'all' || (typeCategory !== 'all' && typeCategory !== '' && typeCategory !== 'none'))) {
                console.log('⚠️ Aucun produit après filtrage par type/type_category, on relâche ces filtres.');
                applyFilters(false);
            }

            console.log('✅ Résultats du filtrage:', {
                visibles: visibleCount,
                total: productCards.length,
                cachésParCatégorie: hiddenByCategory,
                cachésParTypeCatégorie: hiddenByTypeCategory,
                cachésParType: hiddenByType,
                cachésParRecherche: hiddenBySearch
            });

            updateResultsCount();
        };

        // Réappliquer les filtres après la redéfinition
        const categorySelect = document.getElementById('filter-category');
        if (categorySelect && categorySelect.value && categorySelect.value !== 'all') {
            console.log('🔄 Réapplication des filtres après redéfinition');
            filterProducts();
        } else {
            // S'assurer que tous les produits sont visibles
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                card.style.display = 'flex';
            });
            updateResultsCount();
        }
    });

    // Gestion de la modal de dimensions pour produits sur mesure
    document.addEventListener('DOMContentLoaded', function () {
        // Créer la modal de dimensions
        const modalHTML = `
        <div id="dimensions-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
            <div style="background: #f8f9fa; border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%; position: relative; border-top: 4px solid #8B4513; border-bottom: 4px solid #8B4513;">
                <button id="close-dimensions-modal" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; padding: 0.5rem; line-height: 1;">×</button>
                <h2 style="color: #8B4513; font-weight: bold; margin-bottom: 1rem; font-size: 1.5rem;">Spécifier les dimensions</h2>
                <p id="dimensions-instruction" style="color: #666; font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.6;">
                    Veuillez entrer les dimensions de votre tapis pour calculer le prix exact.
                </p>
                <p id="max-dimensions-info" style="color: #8B4513; font-weight: 600; margin-bottom: 1rem; font-size: 1rem;">
                    Dimensions maximales pour ce modèle : <span id="max-dimensions-text">-</span>
                </p>
                <div id="calculated-price" style="background: #e8f5e9; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; display: none;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="color: #2e7d32; font-weight: 600;">Prix calculé :</span>
                        <span id="calculated-price-value" style="color: #8B4513; font-size: 1.5rem; font-weight: bold;">0.00 DH</span>
                    </div>
                    <small style="color: #666; font-size: 0.85rem; display: block; margin-top: 0.5rem;">
                        Prix de base : <span id="base-price-text">0.00</span> DH/m² × Surface : <span id="surface-text">0</span> m²
                    </small>
                </div>
                <form id="dimensions-form">
                    <div style="margin-bottom: 1.5rem;">
                        <label for="dimension-length" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">
                            Longueur (cm) *
                        </label>
                        <input type="number" id="dimension-length" name="length" step="0.01" min="0" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; background: white;"
                               placeholder="0"
                               oninput="calculatePrice()">
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <label for="dimension-width" style="display: block; margin-bottom: 0.5rem; color: #333; font-weight: 600;">
                            Largeur (cm) *
                        </label>
                        <input type="number" id="dimension-width" name="width" step="0.01" min="0" required
                               style="width: 100%; padding: 0.875rem; border: 1px solid #ddd; border-radius: 8px; font-size: 1rem; background: white;"
                               placeholder="0"
                               oninput="calculatePrice()">
                    </div>
                    <div id="dimensions-error" style="color: #e74c3c; margin-bottom: 1rem; display: none; font-size: 0.9rem;"></div>
                    <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                        <button type="button" id="cancel-dimensions" 
                                style="padding: 0.875rem 2rem; border: none; border-radius: 8px; background: #95a5a6; color: white; cursor: pointer; font-weight: 600; font-size: 1rem;">
                            Annuler
                        </button>
                        <button type="submit" id="add-to-cart-with-dimensions"
                                style="padding: 0.875rem 2rem; border: none; border-radius: 8px; background: #8B4513; color: white; cursor: pointer; font-weight: 600; font-size: 1rem; transition: all 0.3s ease;"
                                onmouseover="this.style.background='#6B3410'; this.style.transform='scale(1.05)';"
                                onmouseout="this.style.background='#8B4513'; this.style.transform='scale(1)';">
                            Ajouter au panier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    `;

        // Ajouter la modal au body
        document.body.insertAdjacentHTML('beforeend', modalHTML);

        const modal = document.getElementById('dimensions-modal');
        const closeBtn = document.getElementById('close-dimensions-modal');
        const cancelBtn = document.getElementById('cancel-dimensions');
        const form = document.getElementById('dimensions-form');
        const errorDiv = document.getElementById('dimensions-error');
        let currentProductData = null;

        // Fonction pour ouvrir la modal
        function openDimensionsModal(productData) {
            currentProductData = productData;
            const maxLength = parseFloat(productData.maxLength) || 0;
            const maxWidth = parseFloat(productData.maxWidth) || 0;

            // Mettre à jour les informations de la modal
            if (maxLength > 0 && maxWidth > 0) {
                document.getElementById('max-dimensions-text').textContent = `${maxWidth} cm × ${maxLength} cm`;
            } else {
                document.getElementById('max-dimensions-text').textContent = 'Non spécifié';
            }

            // Réinitialiser le formulaire
            document.getElementById('dimension-length').value = '';
            document.getElementById('dimension-width').value = '';
            errorDiv.style.display = 'none';

            // Afficher le prix de base
            const basePrice = parseFloat(productData.price) || 0;
            document.getElementById('base-price-text').textContent = basePrice.toFixed(2);

            // Masquer le prix calculé initialement
            document.getElementById('calculated-price').style.display = 'none';

            // Afficher la modal
            modal.style.display = 'flex';
        }

        // Fonction pour calculer le prix en temps réel
        function calculatePrice() {
            try {
                if (!currentProductData) {
                    return;
                }

                const lengthInput = document.getElementById('dimension-length');
                const widthInput = document.getElementById('dimension-width');

                if (!lengthInput || !widthInput) {
                    return;
                }

                const length = parseFloat(lengthInput.value) || 0;
                const width = parseFloat(widthInput.value) || 0;
                const basePrice = parseFloat(currentProductData.price) || 0;
                const calculatedPriceDiv = document.getElementById('calculated-price');
                const calculatedPriceValue = document.getElementById('calculated-price-value');
                const surfaceText = document.getElementById('surface-text');

                if (!calculatedPriceDiv || !calculatedPriceValue || !surfaceText) {
                    return;
                }

                if (length > 0 && width > 0 && basePrice > 0) {
                    // Calculer la surface en m² (cm² / 10000)
                    const surface = (length * width) / 10000;
                    // Calculer le prix total = prix par m² × surface
                    const calculatedPrice = basePrice * surface;

                    // Vérifier que les calculs sont valides
                    if (!isNaN(surface) && !isNaN(calculatedPrice) && calculatedPrice > 0) {
                        // Afficher le prix calculé
                        calculatedPriceValue.textContent = calculatedPrice.toFixed(2) + ' DH';
                        surfaceText.textContent = surface.toFixed(2);
                        calculatedPriceDiv.style.display = 'block';
                    } else {
                        calculatedPriceDiv.style.display = 'none';
                    }
                } else {
                    calculatedPriceDiv.style.display = 'none';
                }
            } catch (error) {
                console.error('Erreur dans calculatePrice:', error);
            }
        }

        // Fonction pour fermer la modal
        function closeDimensionsModal() {
            modal.style.display = 'none';
            currentProductData = null;
            form.reset();
            errorDiv.style.display = 'none';
        }

        // Événements pour fermer la modal
        closeBtn.addEventListener('click', closeDimensionsModal);
        cancelBtn.addEventListener('click', closeDimensionsModal);
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                closeDimensionsModal();
            }
        });

        // Fonction pour valider et ajouter au panier
        function addToCartWithDimensions() {
            try {
                if (!currentProductData) {
                    console.error('Aucune donnée produit disponible');
                    errorDiv.textContent = 'Erreur: Aucune donnée produit disponible.';
                    errorDiv.style.display = 'block';
                    return;
                }

                const lengthInput = document.getElementById('dimension-length');
                const widthInput = document.getElementById('dimension-width');

                if (!lengthInput || !widthInput) {
                    console.error('Champs de dimensions non trouvés');
                    errorDiv.textContent = 'Erreur: Champs de dimensions non trouvés.';
                    errorDiv.style.display = 'block';
                    return;
                }

                const length = parseFloat(lengthInput.value);
                const width = parseFloat(widthInput.value);
                const maxLength = parseFloat(currentProductData.maxLength) || 0;
                const maxWidth = parseFloat(currentProductData.maxWidth) || 0;

                // Validation
                if (isNaN(length) || length <= 0) {
                    errorDiv.textContent = 'Veuillez entrer une longueur valide (supérieure à 0).';
                    errorDiv.style.display = 'block';
                    lengthInput.focus();
                    return;
                }

                if (isNaN(width) || width <= 0) {
                    errorDiv.textContent = 'Veuillez entrer une largeur valide (supérieure à 0).';
                    errorDiv.style.display = 'block';
                    widthInput.focus();
                    return;
                }

                if (maxLength > 0 && length > maxLength) {
                    errorDiv.textContent = `La longueur ne peut pas dépasser ${maxLength} cm.`;
                    errorDiv.style.display = 'block';
                    lengthInput.focus();
                    return;
                }

                if (maxWidth > 0 && width > maxWidth) {
                    errorDiv.textContent = `La largeur ne peut pas dépasser ${maxWidth} cm.`;
                    errorDiv.style.display = 'block';
                    widthInput.focus();
                    return;
                }

                // Calculer le prix basé sur les dimensions
                const basePrice = parseFloat(currentProductData.price) || 0;

                if (isNaN(basePrice) || basePrice <= 0) {
                    console.error('Prix de base invalide:', currentProductData.price);
                    errorDiv.textContent = 'Erreur: Prix de base invalide.';
                    errorDiv.style.display = 'block';
                    return;
                }

                const surface = (length * width) / 10000; // Surface en m²
                const calculatedPrice = basePrice * surface;

                // Vérifier que le prix calculé est valide
                if (isNaN(calculatedPrice) || calculatedPrice <= 0) {
                    console.error('Prix calculé invalide:', calculatedPrice);
                    errorDiv.textContent = 'Erreur: Impossible de calculer le prix. Veuillez vérifier les dimensions.';
                    errorDiv.style.display = 'block';
                    return;
                }

                // Vérifier que cartManager est disponible
                const manager = window.cartManager;
                if (!manager || typeof manager.addToCart !== 'function') {
                    console.error('cartManager n\'est pas disponible ou addToCart n\'est pas une fonction');
                    errorDiv.textContent = 'Erreur: Impossible d\'ajouter au panier. Veuillez rafraîchir la page.';
                    errorDiv.style.display = 'block';
                    return;
                }

                // Valider les données du produit
                const productId = String(currentProductData.id || '').trim();
                const productName = String(currentProductData.name || '').trim();
                const productImage = String(currentProductData.image || '').trim();

                if (!productId || !productName) {
                    console.error('Données produit invalides:', currentProductData);
                    errorDiv.textContent = 'Erreur: Données produit invalides.';
                    errorDiv.style.display = 'block';
                    return;
                }

                // Ajouter au panier avec les dimensions et le prix calculé
                manager.addToCart(
                    productId,
                    productName,
                    calculatedPrice, // Utiliser le prix calculé au lieu du prix de base
                    productImage,
                    1,
                    {
                        length: length,
                        width: width,
                        maxLength: maxLength,
                        maxWidth: maxWidth,
                        basePrice: basePrice, // Garder le prix de base pour référence
                        surface: surface
                    }
                );

                // Fermer la modal après succès
                closeDimensionsModal();

            } catch (error) {
                console.error('Erreur dans addToCartWithDimensions:', error);
                errorDiv.textContent = 'Erreur: ' + (error.message || 'Une erreur est survenue lors de l\'ajout au panier.');
                errorDiv.style.display = 'block';
            }
        }

        // Gestion de la soumission du formulaire
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            addToCartWithDimensions();
            return false;
        });

        // Ajouter aussi un gestionnaire direct sur le bouton pour plus de fiabilité
        const addToCartBtn = document.getElementById('add-to-cart-with-dimensions');
        if (addToCartBtn) {
            addToCartBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                addToCartWithDimensions();
                return false;
            });
        }

        // Intercepter les clics sur les boutons "Ajouter au panier"
        // Utiliser une fonction pour éviter les doublons d'event listeners
        function initProductsAddToCart() {
            document.querySelectorAll('.btn-add-cart').forEach(btn => {
                // Vérifier si l'event listener existe déjà
                if (btn.hasAttribute('data-listener-attached')) {
                    return;
                }
                btn.setAttribute('data-listener-attached', 'true');

                // Utiliser capture phase pour s'exécuter avant script.js
                btn.addEventListener('click', function (e) {
                    // Pour les produits sur mesure, prendre le contrôle complet
                    const typeName = String(this.dataset.typeName || '').toLowerCase();
                    const maxLength = String(this.dataset.maxLength || '').trim();
                    const maxWidth = String(this.dataset.maxWidth || '').trim();

                    if (typeName.includes('sur mesure') && (maxLength || maxWidth)) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation(); // Empêcher les autres listeners

                        try {
                            if (typeof openDimensionsModal === 'function') {
                                openDimensionsModal({
                                    id: this.dataset.id,
                                    name: this.dataset.name,
                                    price: this.dataset.price,
                                    image: this.dataset.image,
                                    maxLength: maxLength,
                                    maxWidth: maxWidth
                                });
                            } else {
                                console.error('openDimensionsModal n\'est pas disponible');
                                alert('Erreur: Fonction de modal non disponible.');
                            }
                        } catch (error) {
                            console.error('Erreur lors de l\'ouverture de la modal:', error);
                            alert('Erreur: ' + (error.message || 'Impossible d\'ouvrir la modal.'));
                        }

                        return false;
                    }
                }, true); // Utiliser capture phase

                // Pour les produits normaux, ajouter aussi un listener normal
                btn.addEventListener('click', function (e) {
                    const typeName = String(this.dataset.typeName || '').toLowerCase();
                    const maxLength = String(this.dataset.maxLength || '').trim();
                    const maxWidth = String(this.dataset.maxWidth || '').trim();

                    // Si c'est un produit sur mesure, ne rien faire (déjà géré par le listener en capture)
                    if (typeName.includes('sur mesure') && (maxLength || maxWidth)) {
                        return;
                    }

                    e.preventDefault();
                    e.stopPropagation();

                    try {
                        // Ajouter directement au panier
                        const manager = window.cartManager;
                        if (manager && typeof manager.addToCart === 'function') {
                            const productId = String(this.dataset.id || '').trim();
                            const productName = String(this.dataset.name || '').trim();
                            const productPrice = String(this.dataset.price || '0').trim();
                            const productImage = String(this.dataset.image || '').trim();

                            if (!productId || !productName || !productPrice) {
                                throw new Error('Données produit manquantes');
                            }

                            manager.addToCart(productId, productName, productPrice, productImage);
                        } else {
                            console.error('cartManager n\'est pas disponible');
                            alert('Erreur: Impossible d\'ajouter au panier. Veuillez rafraîchir la page.');
                        }
                    } catch (error) {
                        console.error('Erreur lors de l\'ajout au panier:', error);
                        alert('Erreur: ' + (error.message || 'Impossible d\'ajouter au panier.'));
                    }

                    return false;
                });
            });
        }

        // Initialiser au chargement - AVANT script.js
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initProductsAddToCart);
        } else {
            initProductsAddToCart();
        }

        // Réinitialiser après un court délai pour les éléments chargés dynamiquement
        setTimeout(initProductsAddToCart, 100);

        // Exposer les fonctions globalement pour les event handlers inline
        window.calculatePrice = calculatePrice;
        window.openDimensionsModal = openDimensionsModal;
        window.closeDimensionsModal = closeDimensionsModal;
    });

    // ===== SLIDERS PRODUITS (catégories / types de catégories) avec AUTO-PLAY =====
    // Stocker les intervals pour éviter les doublons
    const sliderIntervals = new Map();

    function initProductsSliders() {
        const sliderSections = [
            { sliderId: 'categories-slider', wrapperSelector: '.categories-slider-wrapper' },
            { sliderId: 'type-categories-slider', wrapperSelector: '.type-categories-slider-section .categories-slider-wrapper' }
        ];

        sliderSections.forEach(({ sliderId, wrapperSelector }) => {
            const slider = document.getElementById(sliderId);
            if (!slider) return;

            // Nettoyer l'interval existant si présent
            if (sliderIntervals.has(sliderId)) {
                clearInterval(sliderIntervals.get(sliderId));
                sliderIntervals.delete(sliderId);
            }

            const wrapper = slider.closest(wrapperSelector);
            if (!wrapper) return;

            const prevBtn = wrapper.querySelector('.categories-slider-arrow.prev');
            const nextBtn = wrapper.querySelector('.categories-slider-arrow.next');
            if (!prevBtn || !nextBtn) return;

            const firstSlide = slider.querySelector('.category-slide, .type-category-card');
            if (!firstSlide) return;

            const gap = 16; // écart entre les slides (gap: 1rem = 16px)
            let isPaused = false;

            // Fonction pour calculer la largeur d'un slide
            function getSlideWidth() {
                const currentFirstSlide = slider.querySelector('.category-slide, .type-category-card');
                if (!currentFirstSlide) return 180; // valeur par défaut
                return currentFirstSlide.getBoundingClientRect().width + gap;
            }

            // Fonction pour faire défiler vers la droite
            function scrollNext() {
                const slideWidth = getSlideWidth();
                const maxScroll = slider.scrollWidth - slider.clientWidth;
                const currentScroll = slider.scrollLeft;

                if (currentScroll >= maxScroll - 10) {
                    // Si on est à la fin, revenir au début (loop)
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: slideWidth, behavior: 'smooth' });
                }
            }

            // Fonction pour faire défiler vers la gauche
            function scrollPrev() {
                const slideWidth = getSlideWidth();
                const currentScroll = slider.scrollLeft;

                if (currentScroll <= 10) {
                    // Si on est au début, aller à la fin (loop)
                    slider.scrollTo({ left: slider.scrollWidth, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: -slideWidth, behavior: 'smooth' });
                }
            }

            // Fonction pour démarrer l'auto-play
            function startAutoPlay() {
                if (sliderIntervals.has(sliderId)) return; // Déjà démarré
                const interval = setInterval(() => {
                    if (!isPaused) {
                        scrollNext();
                    }
                }, 1000); // 1 seconde (1000ms)
                sliderIntervals.set(sliderId, interval);
            }

            // Fonction pour arrêter l'auto-play
            function stopAutoPlay() {
                if (sliderIntervals.has(sliderId)) {
                    clearInterval(sliderIntervals.get(sliderId));
                    sliderIntervals.delete(sliderId);
                }
            }

            // Vérifier si les event listeners sont déjà attachés
            if (prevBtn.dataset.autoPlayInitialized === 'true') {
                return; // Déjà initialisé
            }
            prevBtn.dataset.autoPlayInitialized = 'true';
            nextBtn.dataset.autoPlayInitialized = 'true';

            // Event listeners pour les boutons
            prevBtn.addEventListener('click', function () {
                scrollPrev();
                // Réinitialiser l'auto-play après un clic manuel
                stopAutoPlay();
                setTimeout(startAutoPlay, 3000); // Redémarrer après 3 secondes
            });

            nextBtn.addEventListener('click', function () {
                scrollNext();
                // Réinitialiser l'auto-play après un clic manuel
                stopAutoPlay();
                setTimeout(startAutoPlay, 3000); // Redémarrer après 3 secondes
            });

            // Pause on hover
            wrapper.addEventListener('mouseenter', function () {
                isPaused = true;
            });

            wrapper.addEventListener('mouseleave', function () {
                isPaused = false;
            });

            // Démarrer l'auto-play au chargement
            // Attendre un peu pour que le DOM soit complètement chargé
            setTimeout(() => {
                startAutoPlay();
            }, 500);

            // Réinitialiser l'auto-play si la fenêtre change de taille
            let resizeTimeout;
            const resizeHandler = function () {
                clearTimeout(resizeTimeout);
                resizeTimeout = setTimeout(function () {
                    stopAutoPlay();
                    startAutoPlay();
                }, 250);
            };
            window.addEventListener('resize', resizeHandler);
        });
    }

    // Initialiser les sliders au chargement
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductsSliders);
    } else {
        initProductsSliders();
    }

    // Réinitialiser les sliders quand le slider des types de catégories est créé dynamiquement
    const originalLoadTypesByCategory = window.loadTypesByCategory;
    if (typeof originalLoadTypesByCategory === 'function') {
        window.loadTypesByCategory = function (...args) {
            const result = originalLoadTypesByCategory.apply(this, args);
            // Réinitialiser les sliders après création dynamique
            setTimeout(() => {
                initProductsSliders();
            }, 300);
            return result;
        };
    }
</script>

<?php require_once 'includes/footer.php'; ?>