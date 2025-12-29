<?php
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$productId = $_GET['id'];

// Récupérer le produit avec ses relations (type_category, type, etc.)
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               tc.name as type_category_name,
               tc.id as type_category_id,
               t.id as type_id,
               t.name as type_name,
               p.max_length,
               p.max_width
        FROM products p
        LEFT JOIN types_categories tc ON p.type_category_id = tc.id
        LEFT JOIN types t ON tc.types_id = t.id
        WHERE p.id = ?
    ");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    // Fallback: récupérer sans jointures
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

if (!$product) {
    header('Location: index.php');
    exit;
}

// Configuration SEO pour la page produit
$siteUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'];
$productName = htmlspecialchars($product['name']);
$productDescription = !empty($product['description']) ? htmlspecialchars(substr(strip_tags($product['description']), 0, 160)) : $productName . ' - Meuble de qualité disponible chez Frachdark';
$productPrice = number_format($product['price'], 2, '.', '');
$productImage = $siteUrl . '/' . htmlspecialchars($product['image']);
$productCategory = htmlspecialchars($product['category']);
$productUrl = $siteUrl . $_SERVER['REQUEST_URI'];

$pageTitle = $productName . ' - Frachdark Maroc';
$pageMetaDescription = $productDescription . ' | Prix: ' . number_format($product['price'], 2, ',', ' ') . ' DH | Catégorie: ' . $productCategory . ' | Livraison rapide partout au Maroc | Frachdark';
$pageKeywords = strtolower($productName) . ', ' . strtolower($productCategory) . ' maroc, meuble maroc, mobilier maroc, frachdark, achat meuble maroc, prix meuble maroc, livraison meubles maroc';
$pageImage = $productImage;

// Récupérer des produits similaires avec leurs relations
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               tc.name as type_category_name,
               tc.id as type_category_id,
               t.id as type_id,
               t.name as type_name,
               p.max_length,
               p.max_width
        FROM products p
        LEFT JOIN types_categories tc ON p.type_category_id = tc.id
        LEFT JOIN types t ON tc.types_id = t.id
        WHERE p.category = ? AND p.id != ? 
        LIMIT 4
    ");
    $stmt->execute([$product['category'], $productId]);
    $relatedProducts = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback: récupérer sans jointures
    $stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
    $stmt->execute([$product['category'], $productId]);
    $relatedProducts = $stmt->fetchAll();
}
?>

<!-- Structured Data (JSON-LD) pour SEO -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "<?php echo addslashes($productName); ?>",
    "description": "<?php echo addslashes($productDescription); ?>",
    "image": "<?php echo $productImage; ?>",
    "brand": {
        "@type": "Brand",
        "name": "Frachdark"
    },
    "offers": {
        "@type": "Offer",
        "url": "<?php echo $productUrl; ?>",
        "priceCurrency": "MAD",
        "price": "<?php echo $productPrice; ?>",
        "priceValidUntil": "<?php echo date('Y-m-d', strtotime('+1 year')); ?>",
        "availability": "<?php echo ($product['stock'] > 0) ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock'; ?>",
        "itemCondition": "https://schema.org/NewCondition",
        "seller": {
            "@type": "Organization",
            "name": "Frachdark"
        }
    },
    "category": "<?php echo addslashes($productCategory); ?>",
    "sku": "PROD-<?php echo $productId; ?>",
    "mpn": "<?php echo $productId; ?>"
}
</script>

<!-- Breadcrumb Structured Data -->
<script type="application/ld+json">
{
    "@context": "https://schema.org",
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
            "name": "Produits",
            "item": "<?php echo $siteUrl; ?>/products.php"
        },
        {
            "@type": "ListItem",
            "position": 3,
            "name": "<?php echo addslashes($productCategory); ?>",
            "item": "<?php echo $siteUrl; ?>/products.php?category=<?php echo urlencode($productCategory); ?>"
        },
        {
            "@type": "ListItem",
            "position": 4,
            "name": "<?php echo addslashes($productName); ?>",
            "item": "<?php echo $productUrl; ?>"
        }
    ]
}
</script>

<div class="container">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="Fil d'Ariane" style="margin-bottom: 2rem; padding: 1rem 0;">
        <ol style="display: flex; list-style: none; padding: 0; margin: 0; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
            <li><a href="index.php" style="color: var(--text-light); text-decoration: none;">Accueil</a></li>
            <li style="color: var(--text-light);">/</li>
            <li><a href="products.php" style="color: var(--text-light); text-decoration: none;">Produits</a></li>
            <li style="color: var(--text-light);">/</li>
            <li><a href="products.php?category=<?php echo urlencode($product['category']); ?>" style="color: var(--text-light); text-decoration: none;"><?php echo htmlspecialchars($product['category']); ?></a></li>
            <li style="color: var(--text-light);">/</li>
            <li style="color: var(--primary-color); font-weight: 600;"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>
    
    <div style="margin-bottom: 2rem;">
        <a href="javascript:history.back()" class="btn" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
            ← Retour
        </a>
    </div>
    <div class="product-detail">
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name'] . ' - Meubles ' . $productCategory . ' - Frachdark Maroc'); ?>" 
                 class="main-image"
                 loading="lazy"
                 width="600"
                 height="500"
                 onerror="this.src='https://via.placeholder.com/600x500?text=Produit'">
            <div class="thumbnail-images">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name'] . ' - Vue 1'); ?>" 
                     class="thumbnail active"
                     loading="lazy"
                     width="100"
                     height="100"
                     onerror="this.src='https://via.placeholder.com/100x100?text=Produit'">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name'] . ' - Vue 2'); ?>" 
                     class="thumbnail"
                     loading="lazy"
                     width="100"
                     height="100"
                     onerror="this.src='https://via.placeholder.com/100x100?text=Produit'">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['name'] . ' - Vue 3'); ?>" 
                     class="thumbnail"
                     loading="lazy"
                     width="100"
                     height="100"
                     onerror="this.src='https://via.placeholder.com/100x100?text=Produit'">
            </div>
        </div>
        
        <div class="product-details">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="price"><?php echo number_format($product['price'], 2, ',', ' '); ?> DH</div>
            
            <div class="stock-info <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-of-stock'; ?>">
                <?php if ($product['stock'] > 0): ?>
                    ✓ En stock (<?php echo $product['stock']; ?> disponibles)
                <?php else: ?>
                    ✗ Rupture de stock
                <?php endif; ?>
            </div>
            
            <div class="description">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>
            
            <div class="quantity-selector">
                <label for="quantity">Quantité :</label>
                <input type="number" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
            </div>
            
            <button class="btn btn-add-cart" 
                    data-id="<?php echo $product['id']; ?>"
                    data-name="<?php echo htmlspecialchars($product['name']); ?>"
                    data-price="<?php echo $product['price']; ?>"
                    data-image="<?php echo htmlspecialchars($product['image']); ?>"
                    data-type-name="<?php echo htmlspecialchars($product['type_name'] ?? ''); ?>"
                    data-max-length="<?php echo isset($product['max_length']) && $product['max_length'] ? $product['max_length'] : ''; ?>"
                    data-max-width="<?php echo isset($product['max_width']) && $product['max_width'] ? $product['max_width'] : ''; ?>"
                    <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                <?php echo $product['stock'] > 0 ? 'Ajouter au panier' : 'Indisponible'; ?>
            </button>
        </div>
    </div>

    <!-- Produits similaires -->
    <?php if (count($relatedProducts) > 0): ?>
    <section style="margin-top: 4rem;">
        <h2 class="section-title">Produits Similaires</h2>
        <div class="products-grid">
            <?php foreach ($relatedProducts as $related): ?>
                <div class="product-card" data-id="<?php echo $related['id']; ?>" 
                     data-category="<?php echo htmlspecialchars($related['category']); ?>" 
                     data-price="<?php echo $related['price']; ?>">
                    <img src="<?php echo htmlspecialchars($related['image']); ?>" 
                         alt="<?php echo htmlspecialchars($related['name'] . ' - Meubles ' . $related['category'] . ' - Frachdark Maroc'); ?>" 
                         class="product-image"
                         loading="lazy"
                         width="300"
                         height="250"
                         onerror="this.src='https://via.placeholder.com/300x250?text=Produit'">
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($related['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($related['description'], 0, 100)) . '...'; ?></p>
                        <div class="product-price"><?php echo number_format($related['price'], 2, ',', ' '); ?> DH</div>
                        <div class="product-actions">
                            <a href="product.php?id=<?php echo $related['id']; ?>" class="btn" style="flex: 1; text-align: center;">Voir détails</a>
                            <button class="btn-add-cart" 
                                    data-id="<?php echo $related['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($related['name']); ?>"
                                    data-price="<?php echo $related['price']; ?>"
                                    data-image="<?php echo htmlspecialchars($related['image']); ?>"
                                    data-type-name="<?php echo htmlspecialchars($related['type_name'] ?? ''); ?>"
                                    data-max-length="<?php echo isset($related['max_length']) && $related['max_length'] ? $related['max_length'] : ''; ?>"
                                    data-max-width="<?php echo isset($related['max_width']) && $related['max_width'] ? $related['max_width'] : ''; ?>">
                                🛒
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>

<script>
// Gestion de la modal de dimensions pour produits sur mesure
document.addEventListener('DOMContentLoaded', function() {
    // Créer la modal de dimensions
    const modalHTML = `
        <div id="dimensions-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; align-items: center; justify-content: center;">
            <div style="background: #f8f9fa; border-radius: 12px; padding: 2rem; max-width: 500px; width: 90%; position: relative; border-top: 4px solid #8B4513; border-bottom: 4px solid #8B4513;">
                <button id="close-dimensions-modal" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666; padding: 0.5rem; line-height: 1;">×</button>
                <h2 style="color: #8B4513; font-weight: bold; margin-bottom: 1rem; font-size: 1.5rem;">Spécifier les dimensions</h2>
                <p id="dimensions-instruction" style="color: #666; font-size: 0.95rem; margin-bottom: 1.5rem; line-height: 1.6;">
                    Veuillez entrer les dimensions de votre produit pour calculer le prix exact.
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
    modal.addEventListener('click', function(e) {
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
            const quantityInput = document.getElementById('quantity');
            const quantity = quantityInput ? parseInt(quantityInput.value) || 1 : 1;
            
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
                quantity,
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
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        e.stopPropagation();
        addToCartWithDimensions();
        return false;
    });
    
    // Ajouter aussi un gestionnaire direct sur le bouton pour plus de fiabilité
    const addToCartBtn = document.getElementById('add-to-cart-with-dimensions');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            addToCartWithDimensions();
            return false;
        });
    }
    
    // Intercepter les clics sur tous les boutons "Ajouter au panier"
    function initProductAddToCart() {
        document.querySelectorAll('.btn-add-cart').forEach(btn => {
            // Vérifier si l'event listener existe déjà
            if (btn.hasAttribute('data-listener-attached')) {
                return;
            }
            btn.setAttribute('data-listener-attached', 'true');
            
            // Utiliser capture phase pour s'exécuter avant script.js
            btn.addEventListener('click', function(e) {
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
            btn.addEventListener('click', function(e) {
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
                    // Vérifier si le bouton est désactivé
                    if (this.disabled) {
                        return false;
                    }
                    
                    const productId = String(this.dataset.id || '').trim();
                    const productName = String(this.dataset.name || '').trim();
                    const productPrice = String(this.dataset.price || '0').trim();
                    const productImage = String(this.dataset.image || '').trim();
                    
                    // Validation des données
                    if (!productId || !productName || !productPrice || parseFloat(productPrice) <= 0) {
                        throw new Error('Données produit invalides');
                    }
                    
                    // Obtenir la quantité (seulement pour le bouton principal)
                    let quantity = 1;
                    if (this.closest('.product-details')) {
                        const quantityInput = document.getElementById('quantity');
                        if (quantityInput) {
                            quantity = parseInt(quantityInput.value) || 1;
                            if (quantity <= 0) quantity = 1;
                        }
                    }
                    
                    // Ajouter directement au panier
                    const manager = window.cartManager;
                    if (manager && typeof manager.addToCart === 'function') {
                        manager.addToCart(
                            productId,
                            productName,
                            productPrice,
                            productImage,
                            quantity
                        );
                    } else {
                        throw new Error('cartManager n\'est pas disponible');
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
        document.addEventListener('DOMContentLoaded', initProductAddToCart);
    } else {
        initProductAddToCart();
    }
    
    // Réinitialiser après un court délai pour les éléments chargés dynamiquement
    setTimeout(initProductAddToCart, 100);
    
    // Exposer les fonctions globalement pour les event handlers inline
    window.calculatePrice = calculatePrice;
    window.openDimensionsModal = openDimensionsModal;
    window.closeDimensionsModal = closeDimensionsModal;
});
</script>

<?php require_once 'includes/footer.php'; ?>

