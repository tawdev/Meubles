<?php
$pageTitle = "Gestion des types (stock/mesure)";
require_once 'includes/header.php';

$success = false;
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name'] ?? '');
                
                if (empty($name)) {
                    $error = 'Le nom du type est obligatoire.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO types (name) VALUES (?)");
                        $stmt->execute([$name]);
                        $success = true;
                        $_POST = [];
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $error = 'Ce type existe déjà.';
                        } else {
                            $error = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                
                if (empty($name)) {
                    $error = 'Le nom du type est obligatoire.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE types SET name = ? WHERE id = ?");
                        $stmt->execute([$name, $id]);
                        $success = true;
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $error = 'Ce type existe déjà.';
                        } else {
                            $error = 'Erreur lors de la modification : ' . $e->getMessage();
                        }
                    }
                }
                break;
        }
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        // Vérifier si des types_categories utilisent ce type
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM types_categories WHERE types_id = ?");
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            $error = 'Impossible de supprimer ce type car il est utilisé par ' . $result['count'] . ' type(s) de catégorie.';
        } else {
            $stmt = $pdo->prepare("DELETE FROM types WHERE id = ?");
            $stmt->execute([$id]);
            $success = true;
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression : ' . $e->getMessage();
    }
}

// Récupérer tous les types
try {
    $stmt = $pdo->query("
        SELECT t.*,
               (SELECT COUNT(*) FROM types_categories WHERE types_id = t.id) as types_categories_count
        FROM types t
        ORDER BY t.name
    ");
    $types = $stmt->fetchAll();
} catch (PDOException $e) {
    $types = [];
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error = 'La table types n\'existe pas encore. Veuillez exécuter le fichier SQL create_types_table.sql';
    }
}

// Type à modifier
$editType = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $editStmt = $pdo->prepare("SELECT * FROM types WHERE id = ?");
        $editStmt->execute([$editId]);
        $editType = $editStmt->fetch();
        
        if (!$editType) {
            $error = 'Type non trouvé.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération du type : ' . $e->getMessage();
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des types (stock/mesure)</h1>
        <button onclick="toggleAddForm()" class="btn" id="toggle-btn">➕ Ajouter un type</button>
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

    <!-- Formulaire d'ajout/modification (masqué par défaut) -->
    <div id="add-form-container" style="display: <?php echo ($editType || isset($_GET['edit'])) ? 'block' : 'none'; ?>; margin-bottom: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">
                <?php echo $editType ? 'Modifier le type' : 'Ajouter un nouveau type'; ?>
            </h2>
            <form method="POST" action="types.php">
                <input type="hidden" name="action" value="<?php echo $editType ? 'edit' : 'add'; ?>">
                <?php if ($editType): ?>
                    <input type="hidden" name="id" value="<?php echo $editType['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nom du type *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Ex: En stock, Sur mesure..."
                           value="<?php echo $editType ? htmlspecialchars($editType['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>">
                    <small style="color: var(--text-light);">Le nom du type (ex: En stock, Sur mesure)</small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">
                        <?php echo $editType ? 'Enregistrer les modifications' : 'Ajouter le type'; ?>
                    </button>
                    <a href="types.php" class="btn" style="background: var(--text-light); text-decoration: none; display: inline-block;">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des types -->
    <h2 style="margin-bottom: 1rem;">Liste des types</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom du type</th>
                    <th>Types de catégories</th>
                    <th>Créé le</th>
                    <th>Modifié le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($types)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            Aucun type trouvé. Cliquez sur "Ajouter un type" pour en créer un.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($types as $type): ?>
                    <tr>
                        <td><?php echo $type['id']; ?></td>
                        <td>
                            <strong style="font-size: 1.1rem;"><?php echo htmlspecialchars($type['name']); ?></strong>
                        </td>
                        <td>
                            <?php if ($type['types_categories_count'] > 0): ?>
                                <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo $type['types_categories_count']; ?> type(s) de catégorie
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-light);">Aucun</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $type['created_at'] ? date('d/m/Y H:i', strtotime($type['created_at'])) : 'N/A'; ?></td>
                        <td><?php echo $type['updated_at'] ? date('d/m/Y H:i', strtotime($type['updated_at'])) : 'N/A'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="types.php?edit=<?php echo $type['id']; ?>" class="btn-edit" title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="types.php?delete=<?php echo $type['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type ?')"
                                   title="Supprimer">
                                    🗑️ Supprimer
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

<script>
function toggleAddForm() {
    const formContainer = document.getElementById('add-form-container');
    const toggleBtn = document.getElementById('toggle-btn');
    
    if (!formContainer || !toggleBtn) return;
    
    if (formContainer.style.display === 'none' || formContainer.style.display === '') {
        formContainer.style.display = 'block';
        toggleBtn.textContent = '❌ Annuler';
        toggleBtn.style.background = 'var(--text-light)';
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        formContainer.style.display = 'none';
        toggleBtn.textContent = '➕ Ajouter un type';
        toggleBtn.style.background = '';
    }
}

// Initialiser le formulaire si on est en mode édition
document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('add-form-container');
    const toggleBtn = document.getElementById('toggle-btn');
    
    <?php if ($editType || isset($_GET['edit'])): ?>
    if (formContainer && toggleBtn) {
        formContainer.style.display = 'block';
        toggleBtn.textContent = '❌ Annuler';
        toggleBtn.style.background = 'var(--text-light)';
        // Scroll vers le formulaire après un court délai pour s'assurer que le DOM est prêt
        setTimeout(function() {
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }
    <?php endif; ?>
    
    <?php if ($error && !isset($_GET['edit'])): ?>
    // Afficher le formulaire s'il y a une erreur
    if (formContainer && toggleBtn) {
        formContainer.style.display = 'block';
        toggleBtn.textContent = '❌ Annuler';
        toggleBtn.style.background = 'var(--text-light)';
    }
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>

