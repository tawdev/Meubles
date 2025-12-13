<?php
$pageTitle = "Gestion des types de catégories";
require_once 'includes/header.php';

$success = false;
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name'] ?? '');
                $categoryId = intval($_POST['category_id'] ?? 0);
                
                if (empty($name) || $categoryId <= 0) {
                    $error = 'Le nom et la catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO types_categories (name, category_id) VALUES (?, ?)");
                        $stmt->execute([$name, $categoryId]);
                        $success = true;
                        $_POST = [];
                    } catch (PDOException $e) {
                        $error = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                    }
                }
                break;
                
            case 'edit':
                $id = intval($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $categoryId = intval($_POST['category_id'] ?? 0);
                
                if (empty($name) || $categoryId <= 0) {
                    $error = 'Le nom et la catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE types_categories SET name = ?, category_id = ? WHERE id = ?");
                        $stmt->execute([$name, $categoryId, $id]);
                        $success = true;
                    } catch (PDOException $e) {
                        $error = 'Erreur lors de la modification : ' . $e->getMessage();
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
        // Vérifier si des produits utilisent ce type
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE type_category_id = ?");
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            $error = 'Impossible de supprimer ce type car il est utilisé par ' . $result['count'] . ' produit(s).';
        } else {
            $stmt = $pdo->prepare("DELETE FROM types_categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = true;
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression : ' . $e->getMessage();
    }
}

// Récupérer toutes les catégories pour le select
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    $categoriesList = [];
}

// Récupérer tous les types de catégories avec leurs catégories
try {
    $stmt = $pdo->query("
        SELECT tc.*, c.name as category_name, c.icon as category_icon,
               (SELECT COUNT(*) FROM products WHERE type_category_id = tc.id) as product_count
        FROM types_categories tc
        LEFT JOIN categories c ON tc.category_id = c.id
        ORDER BY c.name, tc.name
    ");
    $typesCategories = $stmt->fetchAll();
} catch (PDOException $e) {
    $typesCategories = [];
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error = 'La table types_categories n\'existe pas encore. Veuillez exécuter le fichier SQL create_types_categories_table.sql';
    }
}

// Type à modifier
$editType = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $editStmt = $pdo->prepare("SELECT * FROM types_categories WHERE id = ?");
        $editStmt->execute([$editId]);
        $editType = $editStmt->fetch();
        
        if (!$editType) {
            $error = 'Type de catégorie non trouvé.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération du type : ' . $e->getMessage();
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des types de catégories</h1>
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
                <?php echo $editType ? 'Modifier le type de catégorie' : 'Ajouter un nouveau type de catégorie'; ?>
            </h2>
            <form method="POST" action="types_categories.php">
                <input type="hidden" name="action" value="<?php echo $editType ? 'edit' : 'add'; ?>">
                <?php if ($editType): ?>
                    <input type="hidden" name="id" value="<?php echo $editType['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="category_id">Catégorie *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo ($editType && $editType['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="name">Nom du type *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Ex: Canapé, Table basse, Lit..."
                           value="<?php echo $editType ? htmlspecialchars($editType['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>">
                    <small style="color: var(--text-light);">Le nom du type de catégorie (ex: Canapé pour Salon)</small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">
                        <?php echo $editType ? 'Enregistrer les modifications' : 'Ajouter le type'; ?>
                    </button>
                    <a href="types_categories.php" class="btn" style="background: var(--text-light); text-decoration: none; display: inline-block;">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des types de catégories -->
    <h2 style="margin-bottom: 1rem;">Liste des types de catégories</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Catégorie</th>
                    <th>Nom du type</th>
                    <th>Produits</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($typesCategories)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            <?php if (empty($categoriesList)): ?>
                                Aucune catégorie trouvée. Veuillez d'abord créer des catégories.
                            <?php else: ?>
                                Aucun type de catégorie trouvé. Cliquez sur "Ajouter un type" pour en créer un.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($typesCategories as $type): ?>
                    <tr>
                        <td><?php echo $type['id']; ?></td>
                        <td>
                            <span style="font-size: 1.2rem;"><?php echo htmlspecialchars($type['category_icon'] ?? ''); ?></span>
                            <strong><?php echo htmlspecialchars($type['category_name'] ?? 'N/A'); ?></strong>
                        </td>
                        <td><strong><?php echo htmlspecialchars($type['name']); ?></strong></td>
                        <td><?php echo $type['product_count']; ?> produit(s)</td>
                        <td><?php echo $type['created_at'] ? date('d/m/Y H:i', strtotime($type['created_at'])) : 'N/A'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="types_categories.php?edit=<?php echo $type['id']; ?>" class="btn-edit" title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="types_categories.php?delete=<?php echo $type['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce type de catégorie ?')"
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

