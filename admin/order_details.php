<?php
$pageTitle = "Détails de la commande";
require_once 'includes/header.php';

// Vérifier si l'ID de commande est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: orders.php');
    exit;
}

$orderId = intval($_GET['id']);

// Récupérer les détails de la commande
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->execute([$orderId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: orders.php');
        exit;
    }
    
    // Récupérer les articles de la commande avec les images des produits
    $itemsStmt = $pdo->prepare("
        SELECT oi.*, p.image as product_image 
        FROM order_items oi 
        LEFT JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $itemsStmt->execute([$orderId]);
    $items = $itemsStmt->fetchAll();
} catch (PDOException $e) {
    header('Location: orders.php');
    exit;
}

// Gérer le changement de statut depuis cette page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        $success = true;
        // Recharger les données
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour du statut.';
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <div class="header-content">
            <h1>
                <span class="header-icon">📦</span>
                Détails de la commande #<?php echo $order['id']; ?>
            </h1>
            <a href="orders.php" class="btn btn-secondary">
                <span>←</span> Retour aux commandes
            </a>
        </div>
    </div>

    <?php if (isset($success) && $success): ?>
        <div class="alert alert-success">
            ✅ Statut de la commande mis à jour avec succès !
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="order-details-page">
        <!-- Statut de la commande en haut -->
        <div class="order-status-banner status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
            <div class="status-banner-content">
                <div class="status-info">
                    <span class="status-icon">
                        <?php 
                        $statusIcons = [
                            'en-attente' => '⏳',
                            'confirmée' => '✅',
                            'livrée' => '🚚',
                            'annulée' => '❌'
                        ];
                        $statusKey = strtolower(str_replace(' ', '-', $order['status']));
                        echo $statusIcons[$statusKey] ?? '📦';
                        ?>
                    </span>
                    <div class="status-text">
                        <span class="status-label">Statut actuel :</span>
                        <span class="status-value"><?php echo htmlspecialchars($order['status']); ?></span>
                    </div>
                </div>
                <form method="POST" action="order_details.php?id=<?php echo $order['id']; ?>" class="status-form-banner">
                    <select name="status" onchange="this.form.submit()" class="status-select-banner">
                        <option value="En attente" <?php echo $order['status'] === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                        <option value="Confirmée" <?php echo $order['status'] === 'Confirmée' ? 'selected' : ''; ?>>Confirmée</option>
                        <option value="Livrée" <?php echo $order['status'] === 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                        <option value="Annulée" <?php echo $order['status'] === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                    </select>
                </form>
            </div>
        </div>

        <!-- Informations de la commande -->
        <div class="order-info-section">
            <div class="info-card customer-card">
                <div class="card-header">
                    <h3 class="info-card-title">
                        <span class="icon">👤</span>
                        Informations client
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">👤</span>
                                <span class="info-label">Nom complet</span>
                            </div>
                            <span class="info-value"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">📧</span>
                                <span class="info-label">Email</span>
                            </div>
                            <span class="info-value">
                                <a href="mailto:<?php echo htmlspecialchars($order['customer_email']); ?>" class="info-link">
                                    <?php echo htmlspecialchars($order['customer_email']); ?>
                                </a>
                            </span>
                        </div>
                        <?php if (!empty($order['customer_phone'])): ?>
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">📞</span>
                                <span class="info-label">Téléphone</span>
                            </div>
                            <span class="info-value">
                                <a href="tel:<?php echo htmlspecialchars($order['customer_phone']); ?>" class="info-link">
                                    <?php echo htmlspecialchars($order['customer_phone']); ?>
                                </a>
                            </span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item full-width">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">📍</span>
                                <span class="info-label">Adresse de livraison</span>
                            </div>
                            <div class="info-value address-value"><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="info-card order-card">
                <div class="card-header">
                    <h3 class="info-card-title">
                        <span class="icon">📋</span>
                        Informations de commande
                    </h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">🔢</span>
                                <span class="info-label">Numéro de commande</span>
                            </div>
                            <span class="info-value order-number">#<?php echo $order['id']; ?></span>
                        </div>
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">📅</span>
                                <span class="info-label">Date de commande</span>
                            </div>
                            <span class="info-value"><?php echo date('d/m/Y à H:i', strtotime($order['created_at'])); ?></span>
                        </div>
                        <?php if (!empty($order['updated_at']) && $order['updated_at'] !== $order['created_at']): ?>
                        <div class="info-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">🔄</span>
                                <span class="info-label">Dernière mise à jour</span>
                            </div>
                            <span class="info-value"><?php echo date('d/m/Y à H:i', strtotime($order['updated_at'])); ?></span>
                        </div>
                        <?php endif; ?>
                        <div class="info-item highlight-item">
                            <div class="info-label-wrapper">
                                <span class="info-label-icon">💰</span>
                                <span class="info-label">Montant total</span>
                            </div>
                            <span class="info-value total-price"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> DH</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Articles de la commande -->
        <div class="order-items-section">
            <div class="section-header">
                <h2 class="section-title">
                    <span class="section-icon">🛒</span>
                    Articles de la commande
                    <span class="items-count">(<?php echo count($items); ?> <?php echo count($items) > 1 ? 'articles' : 'article'; ?>)</span>
                </h2>
            </div>
            
            <div class="order-items-wrapper">
                <table class="order-items-table">
                    <thead>
                        <tr>
                            <th class="col-product">Produit</th>
                            <th class="col-quantity">Quantité</th>
                            <th class="col-price">Prix unitaire</th>
                            <th class="col-total">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $index => $item): ?>
                            <tr class="item-row">
                                <td class="item-name">
                                    <div class="item-name-content">
                                        <div class="item-image-wrapper">
                                            <?php if (!empty($item['product_image'])): ?>
                                                <?php 
                                                // Ajuster le chemin de l'image selon qu'il commence par 'images/' ou non
                                                $imagePath = $item['product_image'];
                                                if (strpos($imagePath, 'images/') === 0) {
                                                    $imagePath = '../' . $imagePath;
                                                } elseif (strpos($imagePath, '../') !== 0 && strpos($imagePath, 'http') !== 0) {
                                                    $imagePath = '../images/' . $imagePath;
                                                }
                                                ?>
                                                <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                                                     alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                                     class="product-image"
                                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\'%3E%3Crect fill=\'%23E8DFD3\' width=\'80\' height=\'80\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%238B7355\' font-size=\'24\'%3E🖼️%3C/text%3E%3C/svg%3E';">
                                            <?php else: ?>
                                                <div class="product-image-placeholder">
                                                    <span class="placeholder-icon">🖼️</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="item-info">
                                            <span class="item-number"><?php echo $index + 1; ?>.</span>
                                            <div class="item-details">
                                                <strong class="item-title"><?php echo htmlspecialchars($item['product_name']); ?></strong>
                                                <?php if ($item['product_id']): ?>
                                                    <span class="product-id">ID Produit: <?php echo $item['product_id']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="item-quantity">
                                    <span class="quantity-badge"><?php echo $item['quantity']; ?></span>
                                </td>
                                <td class="item-price">
                                    <span class="price-value"><?php echo number_format($item['price'], 2, ',', ' '); ?> DH</span>
                                </td>
                                <td class="item-total">
                                    <strong class="total-value"><?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> DH</strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="order-items-total">
                            <td colspan="3" class="total-label">
                                <div class="total-label-content">
                                    <span class="total-icon">💰</span>
                                    <strong>Total de la commande</strong>
                                </div>
                            </td>
                            <td class="total-amount-cell">
                                <strong class="total-amount"><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> DH</strong>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

