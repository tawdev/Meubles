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
        <div class="alert alert-success">
            ✅ Statut de la commande mis à jour avec succès !
        </div>
    <?php endif; ?>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-error">
            ❌ <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="table-wrapper">
        <table class="admin-table orders-table">
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
                    <tr class="order-row">
                        <td class="order-id">#<?php echo $order['id']; ?></td>
                        <td class="order-customer"><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td class="order-email"><?php echo htmlspecialchars($order['customer_email']); ?></td>
                        <td class="order-address"><?php echo htmlspecialchars($order['customer_address']); ?></td>
                        <td class="order-total"><strong><?php echo number_format($order['total_amount'], 2, ',', ' '); ?> DH</strong></td>
                        <td class="order-status">
                            <form method="POST" action="orders.php" class="status-form">
                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="status-select status-<?php echo strtolower(str_replace(' ', '-', $order['status'])); ?>">
                                    <option value="En attente" <?php echo $order['status'] === 'En attente' ? 'selected' : ''; ?>>En attente</option>
                                    <option value="Confirmée" <?php echo $order['status'] === 'Confirmée' ? 'selected' : ''; ?>>Confirmée</option>
                                    <option value="Livrée" <?php echo $order['status'] === 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                                    <option value="Annulée" <?php echo $order['status'] === 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                                </select>
                            </form>
                        </td>
                        <td class="order-date"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                        <td class="order-actions">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn-edit btn-details">
                                <span class="btn-icon">📋</span> Détails
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

