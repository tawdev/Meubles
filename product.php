<?php
$pageTitle = "Détails Produit";
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$productId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: index.php');
    exit;
}

// Récupérer des produits similaires
$stmt = $pdo->prepare("SELECT * FROM products WHERE category = ? AND id != ? LIMIT 4");
$stmt->execute([$product['category'], $productId]);
$relatedProducts = $stmt->fetchAll();
?>

<div class="container">
    <div class="product-detail">
        <div class="product-gallery">
            <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                 class="main-image"
                 onerror="this.src='https://via.placeholder.com/600x500?text=Produit'">
            <div class="thumbnail-images">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="Vue 1" 
                     class="thumbnail active"
                     onerror="this.src='https://via.placeholder.com/100x100?text=Produit'">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="Vue 2" 
                     class="thumbnail"
                     onerror="this.src='https://via.placeholder.com/100x100?text=Produit'">
                <img src="<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="Vue 3" 
                     class="thumbnail"
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
                         alt="<?php echo htmlspecialchars($related['name']); ?>" 
                         class="product-image"
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
                                    data-image="<?php echo htmlspecialchars($related['image']); ?>">
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
document.addEventListener('DOMContentLoaded', () => {
    const addToCartBtn = document.querySelector('.btn-add-cart');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const quantity = parseInt(document.getElementById('quantity').value) || 1;
            const productId = this.dataset.id;
            const productName = this.dataset.name;
            const productPrice = this.dataset.price;
            const productImage = this.dataset.image;
            
            cartManager.addToCart(productId, productName, productPrice, productImage, quantity);
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

