<?php
$pageTitle = "Gestion des commandes";
require_once 'includes/header.php';

// Gérer le changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour du statut.';
    }
}

// Récupérer toutes les commandes
$stmt = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$orders = $stmt->fetchAll();
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des commandes</h1>
    </div>

    <?php if (isset($success) && $success): ?>
        <div class="success-message">
            Statut de la commande mis à jour avec succès !
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Adresse</th>
                    <th>Montant total</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <?php
                    // Récupérer les articles de la commande
                    $itemsStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                    $itemsStmt->execute([$order['id']]);
                    $items = $itemsStmt->fetchAll();
                    ?>
                    <tr>
                        <td><?php echo $order['id']; ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_address']); ?></td>
                        <td><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> DH</td>
                        <td>
                            <form method="POST" action="orders.php" style="display: inline;">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" style="padding: 0.5rem;">
                                    <option value="En attente" <?php echo $order['status'] === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="Confirmée" <?php echo $order['status'] === 'Confirmée' ? 'selected' : ''; ?>>Confirmée</option>
                                    <option value="Livrée" <?php echo $order['status'] === 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                                    <option value="Annulée" <?php echo $order['status'] === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </form>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td>
                            <button onclick="showOrderDetails(<?php echo $order['id']; ?>)" class="btn-edit">📋 Détails</button>
                        </td>
                    </tr>
                    <tr id="details-<?php echo $order['id']; ?>" style="display: none;">
                        <td colspan="8">
                            <div style="background: var(--bg-light); padding: 1rem; border-radius: 5px; margin: 1rem 0;">
                                <h4>Articles de la commande :</h4>
                                <table style="width: 100%; margin-top: 1rem;">
                                    <thead>
                                        <tr>
                                            <th>Produit</th>
                                            <th>Quantité</th>
                                            <th>Prix unitaire</th>
                                            <th>Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                <td><?php echo $item['quantity']; ?></td>
                                                <td><?php echo number_format($item['price'], 2, ',', ' '); ?> DH</td>
                                                <td><?php echo number_format($item['price'] * $item['quantity'], 2, ',', ' '); ?> DH</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function showOrderDetails(orderId) {
    const detailsRow = document.getElementById('details-' + orderId);
    if (detailsRow.style.display === 'none') {
        detailsRow.style.display = 'table-row';
    } else {
        detailsRow.style.display = 'none';
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

