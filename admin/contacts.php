<?php
$pageTitle = "Gestion des contacts";
require_once 'includes/header.php';

$success = false;
$error = '';

// Marquer un message comme lu/non lu
if (isset($_GET['toggle_read'])) {
    $messageId = $_GET['toggle_read'];
    try {
        $stmt = $pdo->prepare("UPDATE contact_messages SET read_status = NOT read_status WHERE id = ?");
        $stmt->execute([$messageId]);
        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour du statut.';
    }
}

// Supprimer un message
if (isset($_GET['delete'])) {
    $messageId = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$messageId]);
        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression du message.';
    }
}

// Marquer tous comme lus
if (isset($_GET['mark_all_read'])) {
    try {
        $pdo->exec("UPDATE contact_messages SET read_status = TRUE");
        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour.';
    }
}

// Récupérer les messages
$filter = $_GET['filter'] ?? 'all';
$query = "SELECT * FROM contact_messages ORDER BY created_at DESC";

if ($filter === 'unread') {
    $query = "SELECT * FROM contact_messages WHERE read_status = FALSE ORDER BY created_at DESC";
} elseif ($filter === 'read') {
    $query = "SELECT * FROM contact_messages WHERE read_status = TRUE ORDER BY created_at DESC";
}

$stmt = $pdo->query($query);
$messages = $stmt->fetchAll();

// Statistiques
$statsStmt = $pdo->query("SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN read_status = FALSE THEN 1 ELSE 0 END) as unread,
    SUM(CASE WHEN read_status = TRUE THEN 1 ELSE 0 END) as read_count
    FROM contact_messages");
$stats = $statsStmt->fetch();

// Message sélectionné pour affichage détaillé
$selectedMessage = null;
if (isset($_GET['view'])) {
    $messageId = $_GET['view'];
    $viewStmt = $pdo->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $viewStmt->execute([$messageId]);
    $selectedMessage = $viewStmt->fetch();
    
    // Marquer comme lu automatiquement
    if ($selectedMessage && !$selectedMessage['read_status']) {
        $updateStmt = $pdo->prepare("UPDATE contact_messages SET read_status = TRUE WHERE id = ?");
        $updateStmt->execute([$messageId]);
        $selectedMessage['read_status'] = true;
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des Messages de Contact</h1>
        <div style="display: flex; gap: 1rem;">
            <a href="contacts.php?mark_all_read=1" class="btn" style="background: var(--secondary-color);">✓ Tout marquer comme lu</a>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success-message">
            Opération réussie !
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Total</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--secondary-color);"><?php echo $stats['total']; ?></p>
        </div>
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Non lus</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--accent-color);"><?php echo $stats['unread']; ?></p>
        </div>
        <div class="category-card">
            <h3 style="color: var(--primary-color);">Lus</h3>
            <p style="font-size: 2rem; font-weight: bold; color: var(--success-color);"><?php echo $stats['read_count']; ?></p>
        </div>
    </div>

    <!-- Filtres -->
    <div style="margin-bottom: 2rem; display: flex; gap: 1rem; flex-wrap: wrap;">
        <a href="contacts.php?filter=all" 
           class="btn" 
           style="background: <?php echo $filter === 'all' ? 'var(--primary-color)' : 'var(--bg-light)'; ?>; color: <?php echo $filter === 'all' ? 'white' : 'var(--text-dark)'; ?>;">
            Tous (<?php echo $stats['total']; ?>)
        </a>
        <a href="contacts.php?filter=unread" 
           class="btn" 
           style="background: <?php echo $filter === 'unread' ? 'var(--accent-color)' : 'var(--bg-light)'; ?>; color: <?php echo $filter === 'unread' ? 'white' : 'var(--text-dark)'; ?>;">
            Non lus (<?php echo $stats['unread']; ?>)
        </a>
        <a href="contacts.php?filter=read" 
           class="btn" 
           style="background: <?php echo $filter === 'read' ? 'var(--success-color)' : 'var(--bg-light)'; ?>; color: <?php echo $filter === 'read' ? 'white' : 'var(--text-dark)'; ?>;">
            Lus (<?php echo $stats['read_count']; ?>)
        </a>
    </div>

    <!-- Détails du message sélectionné -->
    <?php if ($selectedMessage): ?>
        <div style="background: var(--bg-light); padding: 2rem; border-radius: 10px; margin-bottom: 2rem; border-left: 4px solid var(--secondary-color);">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1.5rem;">
                <h2 style="color: var(--primary-color); margin: 0;">Message de <?php echo htmlspecialchars($selectedMessage['name']); ?></h2>
                <a href="contacts.php?filter=<?php echo $filter; ?>" class="btn" style="background: var(--text-light); padding: 0.5rem 1rem;">✕ Fermer</a>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <strong style="color: var(--primary-color);">📧 Email :</strong>
                    <p style="margin-top: 0.5rem;">
                        <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>" style="color: var(--secondary-color);">
                            <?php echo htmlspecialchars($selectedMessage['email']); ?>
                        </a>
                    </p>
                </div>
                <div>
                    <strong style="color: var(--primary-color);">📞 Téléphone :</strong>
                    <p style="margin-top: 0.5rem;">
                        <?php if (!empty($selectedMessage['phone'])): ?>
                            <a href="tel:<?php echo htmlspecialchars($selectedMessage['phone']); ?>" style="color: var(--secondary-color);">
                                <?php echo htmlspecialchars($selectedMessage['phone']); ?>
                            </a>
                        <?php else: ?>
                            <span style="color: var(--text-light);">Non renseigné</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <strong style="color: var(--primary-color);">📅 Date :</strong>
                    <p style="margin-top: 0.5rem;">
                        <?php echo date('d/m/Y à H:i', strtotime($selectedMessage['created_at'])); ?>
                    </p>
                </div>
                <div>
                    <strong style="color: var(--primary-color);">Statut :</strong>
                    <p style="margin-top: 0.5rem;">
                        <span class="status-badge <?php echo $selectedMessage['read_status'] ? 'status-delivered' : 'status-pending'; ?>">
                            <?php echo $selectedMessage['read_status'] ? 'Lu' : 'Non lu'; ?>
                        </span>
                    </p>
                </div>
            </div>
            
            <div>
                <strong style="color: var(--primary-color);">💬 Message :</strong>
                <div style="background: white; padding: 1.5rem; border-radius: 5px; margin-top: 1rem; line-height: 1.8; white-space: pre-wrap;">
                    <?php echo nl2br(htmlspecialchars($selectedMessage['message'])); ?>
                </div>
            </div>
            
            <div style="display: flex; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
                <a href="mailto:<?php echo htmlspecialchars($selectedMessage['email']); ?>?subject=Re: Votre message" class="btn" style="background: var(--secondary-color);">
                    📧 Répondre
                </a>
                <?php if (!empty($selectedMessage['phone'])): ?>
                    <a href="tel:<?php echo htmlspecialchars($selectedMessage['phone']); ?>" class="btn" style="background: var(--success-color);">
                        📞 Appeler
                    </a>
                <?php endif; ?>
                <a href="contacts.php?toggle_read=<?php echo $selectedMessage['id']; ?>&filter=<?php echo $filter; ?>" class="btn" style="background: var(--text-light);">
                    <?php echo $selectedMessage['read_status'] ? '📭 Marquer non lu' : '✓ Marquer lu'; ?>
                </a>
                <a href="contacts.php?delete=<?php echo $selectedMessage['id']; ?>&filter=<?php echo $filter; ?>" 
                   class="btn-delete" 
                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')">
                    🗑️ Supprimer
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- Liste des messages -->
    <h2 style="margin-bottom: 1rem;">Liste des Messages</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Message</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($messages)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">
                            Aucun message trouvé.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <tr style="<?php echo !$message['read_status'] ? 'background: #fff3cd; font-weight: 600;' : ''; ?>">
                            <td><?php echo $message['id']; ?></td>
                            <td><strong><?php echo htmlspecialchars($message['name']); ?></strong></td>
                            <td>
                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" style="color: var(--secondary-color);">
                                    <?php echo htmlspecialchars($message['email']); ?>
                                </a>
                            </td>
                            <td>
                                <?php if (!empty($message['phone'])): ?>
                                    <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>" style="color: var(--secondary-color);">
                                        <?php echo htmlspecialchars($message['phone']); ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-light);">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                <?php echo htmlspecialchars(substr($message['message'], 0, 100)) . (strlen($message['message']) > 100 ? '...' : ''); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($message['created_at'])); ?></td>
                            <td>
                                <span class="status-badge <?php echo $message['read_status'] ? 'status-delivered' : 'status-pending'; ?>">
                                    <?php echo $message['read_status'] ? 'Lu' : 'Non lu'; ?>
                                </span>
                            </td>
                            <td>
                                <div class="admin-actions">
                                    <a href="contacts.php?view=<?php echo $message['id']; ?>&filter=<?php echo $filter; ?>" class="btn-edit" title="Voir détails">
                                        👁️ Voir
                                    </a>
                                    <a href="contacts.php?toggle_read=<?php echo $message['id']; ?>&filter=<?php echo $filter; ?>" 
                                       class="btn-edit" 
                                       title="<?php echo $message['read_status'] ? 'Marquer non lu' : 'Marquer lu'; ?>">
                                        <?php echo $message['read_status'] ? '📭' : '✓'; ?>
                                    </a>
                                    <a href="contacts.php?delete=<?php echo $message['id']; ?>&filter=<?php echo $filter; ?>" 
                                       class="btn-delete" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce message ?')"
                                       title="Supprimer">
                                        🗑️
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

