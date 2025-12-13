<?php
$pageTitle = "Nos Produits";
require_once 'includes/header.php';

// Récupérer la catégorie et le type depuis l'URL si présents
$selectedCategory = $_GET['category'] ?? '';
$selectedTypeCategory = $_GET['type_category'] ?? '';

// Récupérer les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['id' => 1, 'name' => 'Salon', 'icon' => '🛋️'],
        ['id' => 2, 'name' => 'Chambre', 'icon' => '🛏️'],
        ['id' => 3, 'name' => 'Salle à manger', 'icon' => '🍽️'],
        ['id' => 4, 'name' => 'Bureau', 'icon' => '💼'],
        ['id' => 5, 'name' => 'Décoration', 'icon' => '🖼️']
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

// Récupérer tous les produits avec leurs types de catégorie
try {
    $stmt = $pdo->query("
        SELECT p.*, 
               tc.name as type_category_name,
               tc.id as type_category_id
        FROM products p
        LEFT JOIN types_categories tc ON p.type_category_id = tc.id
        ORDER BY p.id DESC
    ");
    $allProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la jointure échoue, récupérer sans types
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $allProducts = $stmt->fetchAll();
}
?>

<section class="hero" style="padding: 4rem 2rem; position: relative;">
    <a href="index.php" style="position: absolute; top: 2rem; left: 2rem; display: inline-flex; align-items: center; gap: 0.5rem; color: white; text-decoration: none; padding: 0.75rem 1.25rem; background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); border-radius: 8px; transition: all 0.3s ease; font-size: 0.95rem; font-weight: 500; z-index: 10; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);" onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'; this.style.transform='translateX(-3px)';" onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'; this.style.transform='translateX(0)';">
        ← Retour à l'accueil
    </a>
    <div class="hero-content">
        <h1>Notre Catalogue Complet</h1>
        <p>Découvrez tous nos meubles et trouvez celui qui correspond à vos besoins</p>
    </div>
</section>

<div class="container">
    <!-- Section Produits -->
    <section id="products">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <h2 class="section-title" style="margin-bottom: 0;">Tous Nos Produits</h2>
            <div id="results-count" style="background: var(--bg-light); padding: 0.75rem 1.5rem; border-radius: 25px; color: var(--primary-color); font-weight: 600; font-size: 1rem;">
                <span id="results-number"><?php echo count($allProducts); ?></span> produit(s) trouvé(s)
            </div>
        </div>
        
        <!-- Filtres améliorés -->
        <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px; order: 0;">
                    <select id="filter-category" onchange="onCategoryChange()" 
                            style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                        <option value="all">Toutes les catégories</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?php echo isset($category['id']) ? $category['id'] : htmlspecialchars($category['name']); ?>" 
                                    data-name="<?php echo htmlspecialchars($category['name']); ?>"
                                    <?php echo ($selectedCategory === $category['name'] || (isset($category['id']) && $selectedCategoryId == $category['id'])) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['icon'] ?? ''); ?> <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="type-category-filter-container" class="hidden" style="display: none; align-items: center; gap: 1rem; flex: 1; min-width: 200px; order: 1;">
                    <label for="filter-type-category" style="display: none;">Type de catégorie</label>
                    <select id="filter-type-category" onchange="filterProducts()" 
                            style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem; width: 100%;">
                        <option value="all">Tous les types</option>
                        <?php foreach ($typesList as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                    <?php echo ($selectedTypeCategory && $selectedTypeCategory == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem; flex: 2; min-width: 250px; position: relative; order: 2;">
                    <input type="text" id="search-products" 
                           placeholder="Rechercher un produit..." 
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
        <div class="products-grid" style="gap: 2rem;">
            <?php if (empty($allProducts)): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 5rem 2rem; background: var(--bg-light); border-radius: 15px;">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">📦</div>
                    <h3 style="color: var(--primary-color); margin-bottom: 1rem; font-size: 1.5rem;">Aucun produit disponible</h3>
                    <p style="color: var(--text-light); font-size: 1.1rem;">Revenez bientôt pour découvrir nos nouveaux produits !</p>
                </div>
            <?php else: ?>
                <?php foreach ($allProducts as $product): ?>
                    <div class="product-card" data-id="<?php echo $product['id']; ?>" 
                         data-category="<?php echo htmlspecialchars($product['category']); ?>" 
                         data-category-id="<?php echo $product['category_id'] ?? ''; ?>"
                         data-type-category-id="<?php echo $product['type_category_id'] ?? ''; ?>"
                         data-type-category-name="<?php echo htmlspecialchars($product['type_category_name'] ?? ''); ?>"
                         data-price="<?php echo $product['price']; ?>"
                         style="overflow: hidden; position: relative;">
                        <!-- Badge catégorie -->
                        <div style="position: absolute; top: 1rem; right: 1rem; background: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.85rem; font-weight: 600; z-index: 10;">
                            <?php echo htmlspecialchars($product['category']); ?>
                        </div>
                        
                        <!-- Image produit -->
                        <div style="position: relative; overflow: hidden; height: 280px; background: var(--bg-light);">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image"
                                 style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                                 onerror="this.src='https://via.placeholder.com/300x280?text=Produit'">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; background: linear-gradient(to top, rgba(0,0,0,0.3), transparent); height: 50%;"></div>
                        </div>
                        
                        <div class="product-info" style="padding: 1.5rem;">
                            <h3 class="product-name" style="font-size: 1.3rem; margin-bottom: 0.75rem; min-height: 3rem; display: flex; align-items: center;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </h3>
                            <p class="product-description" style="color: var(--text-light); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem; min-height: 3rem;">
                                <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="product-price" style="font-size: 1.8rem; font-weight: bold; color: var(--accent-color); margin-bottom: 1.5rem; padding: 0.75rem 0; border-top: 2px solid var(--bg-light); border-bottom: 2px solid var(--bg-light);">
                                <?php echo number_format($product['price'], 2, ',', ' '); ?> DH
                            </div>
                            <div class="product-actions" style="display: flex; gap: 0.75rem;">
                                <a href="product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn" 
                                   style="flex: 1; text-align: center; padding: 0.875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem;">
                                    👁️ Voir détails
                                </a>
                                <button class="btn-add-cart" 
                                        data-id="<?php echo $product['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($product['name']); ?>"
                                        data-price="<?php echo $product['price']; ?>"
                                        data-image="<?php echo htmlspecialchars($product['image']); ?>"
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
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

/* Amélioration des filtres */
#filter-category:focus,
#filter-type-category:focus,
#search-products:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15), 0 4px 10px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

#filter-category:hover,
#filter-type-category:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}

#search-products:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
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
    
    .filters {
        padding: 1.5rem;
    }
    
    .filters > div {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
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
    const typeContainer = document.getElementById('type-category-filter-container');
    if (typeContainer) {
        typeContainer.classList.add('hidden');
        typeContainer.style.display = 'none';
    }
    filterProducts();
}

// Charger les types de catégorie
function loadTypesByCategory(categoryId) {
    const typeSelect = document.getElementById('filter-type-category');
    const typeContainer = document.getElementById('type-category-filter-container');
    
    if (!typeSelect || !typeContainer) {
        return;
    }
    
    // Réinitialiser
    typeSelect.innerHTML = '<option value="all">Tous les types</option>';
    typeSelect.value = 'all';
    
    if (!categoryId || categoryId === 'all' || categoryId === '' || categoryId === '0') {
        typeContainer.classList.add('hidden');
        typeContainer.style.display = 'none';
        return;
    }
    
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
    const apiUrl = `admin/get_types_by_category.php?category_id=${categoryId}`;
    
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
            } else {
                // Ajouter une option pour indiquer qu'il n'y a pas de types
                const noTypeOption = document.createElement('option');
                noTypeOption.value = 'none';
                noTypeOption.textContent = 'Aucun type disponible';
                noTypeOption.disabled = true;
                typeSelect.appendChild(noTypeOption);
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
            typeContainer.style.setProperty('position', 'relative', 'important');
            typeContainer.style.setProperty('z-index', '10', 'important');
            typeContainer.style.setProperty('margin', '0', 'important');
            typeContainer.style.setProperty('padding', '0', 'important');
            typeContainer.style.setProperty('order', '1', 'important');
            
            // S'assurer que le select est aussi visible
            typeSelect.style.setProperty('display', 'block', 'important');
            typeSelect.style.setProperty('visibility', 'visible', 'important');
            typeSelect.style.setProperty('opacity', '1', 'important');
            typeSelect.style.setProperty('width', '100%', 'important');
            typeSelect.style.setProperty('min-width', '200px', 'important');
            typeSelect.style.setProperty('flex', '1', 'important');
            
            // Forcer le reflow après modification
            void typeContainer.offsetHeight;
            void typeSelect.offsetHeight;
            
            // Scroll vers l'élément pour s'assurer qu'il est visible
            typeContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
            
            // Vérifier que l'élément est bien affiché
            const rect = typeContainer.getBoundingClientRect();
            if (rect.width > 0 && rect.height > 0) {
                console.log('✅ Filtre de type de catégorie affiché avec succès');
            }
        })
        .catch(error => {
            console.error('❌ ERREUR lors du chargement des types:', error);
            console.error('Détails:', error.message);
            typeSelect.disabled = false;
            typeSelect.innerHTML = '<option value="all">Tous les types</option>';
            // Masquer en cas d'erreur
            typeContainer.classList.add('hidden');
            typeContainer.style.display = 'none';
        });
}

// Gérer le changement de catégorie
function onCategoryChange() {
    const categorySelect = document.getElementById('filter-category');
    if (!categorySelect) {
        return;
    }
    
    const categoryId = categorySelect.value;
    
    // Réinitialiser le filtre de type
    const typeFilter = document.getElementById('filter-type-category');
    if (typeFilter) {
        typeFilter.value = 'all';
    }
    
    // Charger les types pour cette catégorie
    loadTypesByCategory(categoryId);
    
    // Appliquer le filtre après un court délai pour laisser le temps de charger les types
    setTimeout(function() {
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
document.addEventListener('DOMContentLoaded', function() {
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
        // Charger les types si une catégorie est sélectionnée
        if (typeContainer) {
            typeContainer.classList.remove('hidden');
            typeContainer.style.display = 'flex';
        }
        setTimeout(function() {
            loadTypesByCategory(<?php echo $selectedCategoryId; ?>);
        }, 100);
    <?php endif; ?>
    
    <?php if ($selectedCategory || $selectedTypeCategory): ?>
        // Appliquer le filtre si présent dans l'URL
        setTimeout(function() {
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
        setTimeout(function() {
            console.log('🔄 Appel de filterProducts après 100ms');
            filterProducts();
        }, 100);
    <?php endif; ?>
    
    // S'assurer que le select de catégorie fonctionne correctement
    if (categorySelect && categorySelect.value && categorySelect.value !== 'all') {
        setTimeout(function() {
            loadTypesByCategory(categorySelect.value);
        }, 150);
    }
});

// S'assurer que filterProducts est définie après le chargement de script.js
window.addEventListener('load', function() {
    console.log('📦 Page complètement chargée, redéfinition de filterProducts');
    
    // Redéfinir filterProducts pour supporter type_category_id
    window.filterProducts = function() {
        console.log('🚀 filterProducts appelée (version mise à jour)');
        
        const category = document.getElementById('filter-category')?.value || 'all';
        const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';

        const productCards = document.querySelectorAll('.product-card');
        
        const typeCategory = document.getElementById('filter-type-category')?.value || 'all';
        
        console.log('🔍 Filtrage des produits:', {
            category,
            typeCategory,
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
        let hiddenBySearch = 0;
        
        productCards.forEach((card, index) => {
            const productCategory = card.dataset.category || '';
            const productCategoryId = card.dataset.categoryId || '';
            const productTypeCategoryId = card.dataset.typeCategoryId || '';
            const productName = card.querySelector('.product-name')?.textContent || '';
            
            let show = true;
            let reason = '';

            // Filtre par catégorie (par ID ou nom)
            if (category !== 'all' && category !== '') {
                const categorySelect = document.getElementById('filter-category');
                if (categorySelect && categorySelect.selectedIndex >= 0) {
                    const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                    const categoryName = selectedOption ? (selectedOption.dataset.name || selectedOption.textContent.replace(/[^\w\s]/g, '').trim()) : '';
                    const categoryIdValue = selectedOption ? selectedOption.value : '';
                    
                    // Debug pour les 3 premiers produits
                    if (index < 3) {
                        console.log('🔍 Debug catégorie:', {
                            category,
                            categoryIdValue,
                            categoryName,
                            productCategoryId,
                            productCategory,
                            matchID: categoryIdValue && productCategoryId && parseInt(productCategoryId) === parseInt(categoryIdValue),
                            matchName: categoryName && productCategory && productCategory.toLowerCase() === categoryName.toLowerCase(),
                            matchDirect: category === productCategory
                        });
                    }
                    
                    // Vérifier par ID d'abord
                    if (categoryIdValue && productCategoryId && parseInt(productCategoryId) === parseInt(categoryIdValue)) {
                        reason = 'match category ID';
                    } 
                    // Vérifier par nom
                    else if (categoryName && productCategory && productCategory.toLowerCase() === categoryName.toLowerCase()) {
                        reason = 'match category name';
                    }
                    // Vérifier si la valeur est directement le nom
                    else if (category === productCategory) {
                        reason = 'match category direct';
                    }
                    // Vérifier si category est l'ID et productCategoryId correspond
                    else if (category && productCategoryId && parseInt(category) === parseInt(productCategoryId)) {
                        reason = 'match category by ID direct';
                    }
                    else {
                        show = false;
                        reason = 'category mismatch';
                        hiddenByCategory++;
                    }
                } else {
                    // Fallback: vérifier par nom seulement
                    if (productCategory !== category) {
                        show = false;
                        reason = 'category fallback mismatch';
                        hiddenByCategory++;
                    }
                }
            }

            // Filtre par type de catégorie
            if (show && typeCategory !== 'all' && typeCategory !== '' && typeCategory !== 'none') {
                if (productTypeCategoryId && productTypeCategoryId !== typeCategory) {
                    show = false;
                    reason = 'type mismatch: ' + productTypeCategoryId + ' !== ' + typeCategory;
                    hiddenByType++;
                } else if (!productTypeCategoryId && typeCategory !== 'all') {
                    show = false;
                    reason = 'no type_category_id';
                    hiddenByType++;
                }
            }

            // Filtre par recherche
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
                    category,
                    typeCategory
                });
            }
        });
        
        console.log('✅ Résultats du filtrage:', {
            visibles: visibleCount,
            total: productCards.length,
            cachésParCatégorie: hiddenByCategory,
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
</script>

<?php require_once 'includes/footer.php'; ?>
