<?php
$pageTitle = "Tableau de bord";
require_once 'includes/header.php';

// Récupérer tous les produits
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// Statistiques
try {
    // Vérifier si la colonne stock existe
    $checkColumn = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
    $hasStockColumn = $checkColumn->rowCount() > 0;
    
    if ($hasStockColumn) {
        $statsStmt = $pdo->query("SELECT 
            COUNT(*) as total_products,
            SUM(stock) as total_stock,
            (SELECT COUNT(*) FROM orders) as total_orders,
            (SELECT SUM(total_amount) FROM orders) as total_revenue
        ");
    } else {
        // Si la colonne stock n'existe pas, ne pas l'inclure dans la requête
        $statsStmt = $pdo->query("SELECT 
            COUNT(*) as total_products,
            0 as total_stock,
            (SELECT COUNT(*) FROM orders) as total_orders,
            (SELECT SUM(total_amount) FROM orders) as total_revenue
        ");
    }
    $stats = $statsStmt->fetch();
} catch (PDOException $e) {
    // En cas d'erreur, utiliser des valeurs par défaut
    $stats = [
        'total_products' => 0,
        'total_stock' => 0,
        'total_orders' => 0,
        'total_revenue' => 0
    ];
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Tableau de bord</h1>
        <a href="add.php" class="btn">➕ Ajouter un produit</a>
    </div>

    <!-- Statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Produits</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats['total_products']; ?></p>
        </div>
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Stock total</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats['total_stock'] ?? 0; ?></p>
        </div>
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Commandes</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats['total_orders']; ?></p>
        </div>
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Chiffre d'affaires</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--success-color);"><?php echo number_format($stats['total_revenue'] ?? 0, 2, ',', ' '); ?> DH</p>
        </div>
    </div>

    <!-- Liste des produits -->
    <h2 style="margin-bottom: 1rem;">Liste des produits</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><?php echo $product['id']; ?></td>
                    <td>
                        <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                             style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"
                             onerror="this.src='https://via.placeholder.com/60x60?text=Produit'">
                    </td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                    <td><?php echo number_format($product['price'], 2, ',', ' '); ?> DH</td>
                    <td><?php echo isset($product['stock']) ? $product['stock'] : 'N/A'; ?></td>
                    <td>
                        <div class="admin-actions">
                            <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-edit">✏️ Modifier</a>
                            <a href="delete.php?id=<?php echo $product['id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')">🗑️ Supprimer</a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

