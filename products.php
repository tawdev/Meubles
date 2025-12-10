<?php
$pageTitle = "Nos Produits";
require_once 'includes/header.php';

// Récupérer la catégorie depuis l'URL si présente
$selectedCategory = $_GET['category'] ?? '';

// Récupérer les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['name' => 'Salon', 'icon' => '🛋️'],
        ['name' => 'Chambre', 'icon' => '🛏️'],
        ['name' => 'Salle à manger', 'icon' => '🍽️'],
        ['name' => 'Bureau', 'icon' => '💼'],
        ['name' => 'Décoration', 'icon' => '🖼️']
    ];
}

// Récupérer tous les produits
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$allProducts = $stmt->fetchAll();
?>

<section class="hero" style="padding: 4rem 2rem;">
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
                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                    <select id="filter-category" onchange="filterProducts()" 
                            style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                        <option value="all">Toutes les catégories</option>
                        <?php foreach ($categoriesList as $category): ?>
                            <option value="<?php echo htmlspecialchars($category['name']); ?>" 
                                    <?php echo $selectedCategory === $category['name'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['icon'] ?? ''); ?> <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                    <select id="filter-price" onchange="filterProducts()" 
                            style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease; appearance: none; background-image: url('data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'><path fill=\'%23333\' d=\'M6 9L1 4h10z\'/></svg>'); background-repeat: no-repeat; background-position: right 1rem center; padding-right: 2.5rem;">
                        <option value="all">Tous les prix</option>
                        <option value="0-200">Moins de 200€</option>
                        <option value="200-500">200€ - 500€</option>
                        <option value="500-1000">500€ - 1000€</option>
                        <option value="1000-max">Plus de 1000€</option>
                    </select>
                </div>
                
                <div style="display: flex; align-items: center; gap: 0.5rem; flex: 2; min-width: 250px; position: relative;">
                    <input type="text" id="search-products" 
                           placeholder="Rechercher un produit..." 
                           oninput="filterProducts()"
                           style="flex: 1; padding: 0.875rem 1rem 0.875rem 2.75rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.08); transition: all 0.3s ease;">
                    <span style="position: absolute; left: 1rem; color: var(--text-light); font-size: 1.1rem;">🔍</span>
                </div>
                
                <button onclick="resetFilters()" 
                        style="padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-size: 0.95rem; background: var(--primary-color); color: white; cursor: pointer; font-weight: 600; transition: all 0.3s ease; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1);"
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
                                <?php echo number_format($product['price'], 2, ',', ' '); ?> €
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
                                        style="width: 50px; height: 50px; border-radius: 8px; font-size: 1.3rem; display: flex; align-items: center; justify-content: center; transition: all 0.3s ease;"
                                        title="Ajouter au panier">
                                    🛒
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
#filter-price:focus,
#search-products:focus {
    outline: none;
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15), 0 4px 10px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

#filter-category:hover,
#filter-price:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
}

#search-products:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.12);
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
    document.getElementById('filter-price').value = 'all';
    document.getElementById('search-products').value = '';
    filterProducts();
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

// Modifier la fonction filterProducts pour mettre à jour le compteur
const originalFilterProducts = window.filterProducts;
window.filterProducts = function() {
    if (originalFilterProducts) {
        originalFilterProducts();
    } else {
        // Fonction de base si elle n'existe pas
        const category = document.getElementById('filter-category')?.value || 'all';
        const priceRange = document.getElementById('filter-price')?.value || 'all';
        const searchTerm = document.getElementById('search-products')?.value.toLowerCase() || '';

        const productCards = document.querySelectorAll('.product-card');
        
        productCards.forEach(card => {
            const productCategory = card.dataset.category || '';
            const productPrice = parseFloat(card.dataset.price || 0);
            
            let show = true;

            if (category !== 'all' && productCategory !== category) {
                show = false;
            }

            if (priceRange !== 'all') {
                const [min, max] = priceRange.split('-').map(p => p === 'max' ? Infinity : parseFloat(p));
                if (productPrice < min || productPrice > max) {
                    show = false;
                }
            }

            if (searchTerm) {
                const productName = card.querySelector('.product-name')?.textContent.toLowerCase() || '';
                const productDesc = card.querySelector('.product-description')?.textContent.toLowerCase() || '';
                if (!productName.includes(searchTerm) && !productDesc.includes(searchTerm)) {
                    show = false;
                }
            }

            card.style.display = show ? 'flex' : 'none';
        });
    }
    
    // Mettre à jour le compteur
    updateResultsCount();
};

// Initialiser le compteur au chargement et appliquer les filtres si une catégorie est sélectionnée
document.addEventListener('DOMContentLoaded', function() {
    updateResultsCount();
    <?php if ($selectedCategory): ?>
        // Appliquer le filtre de catégorie si présent dans l'URL
        filterProducts();
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>
