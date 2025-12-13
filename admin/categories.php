<?php
$pageTitle = "Gestion des catégories";
require_once 'includes/header.php';

$success = false;
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                
                if (empty($name)) {
                    $error = 'Le nom de la catégorie est obligatoire.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $description, $icon]);
                        $success = true;
                        $_POST = [];
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $error = 'Cette catégorie existe déjà.';
                        } else {
                            $error = 'Erreur lors de l\'ajout : ' . $e->getMessage();
                        }
                    }
                }
                break;
                
            case 'edit':
                $id = $_POST['id'] ?? 0;
                $name = trim($_POST['name'] ?? '');
                $description = trim($_POST['description'] ?? '');
                $icon = trim($_POST['icon'] ?? '');
                
                if (empty($name)) {
                    $error = 'Le nom de la catégorie est obligatoire.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ?, icon = ? WHERE id = ?");
                        $stmt->execute([$name, $description, $icon, $id]);
                        $success = true;
                    } catch (PDOException $e) {
                        if ($e->getCode() == 23000) {
                            $error = 'Cette catégorie existe déjà.';
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
    $id = $_GET['delete'];
    try {
        // Vérifier si des produits utilisent cette catégorie
        $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category = (SELECT name FROM categories WHERE id = ?)");
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch();
        
        if ($result['count'] > 0) {
            $error = 'Impossible de supprimer cette catégorie car elle est utilisée par ' . $result['count'] . ' produit(s).';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $success = true;
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression : ' . $e->getMessage();
    }
}

// Récupérer toutes les catégories
$stmt = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM products WHERE category = c.name) as product_count FROM categories c ORDER BY name");
$categories = $stmt->fetchAll();

// Catégorie à modifier
$editCategory = null;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $editStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
        $editStmt->execute([$editId]);
        $editCategory = $editStmt->fetch();
        
        if (!$editCategory) {
            $error = 'Catégorie non trouvée.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération de la catégorie : ' . $e->getMessage();
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des catégories</h1>
        <button onclick="toggleAddForm()" class="btn" id="toggle-btn">➕ Ajouter une catégorie</button>
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
    <div id="add-form-container" style="display: <?php echo ($editCategory || isset($_GET['edit'])) ? 'block' : 'none'; ?>; margin-bottom: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">
                <?php echo $editCategory ? 'Modifier la catégorie' : 'Ajouter une nouvelle catégorie'; ?>
            </h2>
            <form method="POST" action="categories.php">
                <input type="hidden" name="action" value="<?php echo $editCategory ? 'edit' : 'add'; ?>">
                <?php if ($editCategory): ?>
                    <input type="hidden" name="id" value="<?php echo $editCategory['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="name">Nom de la catégorie *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo $editCategory ? htmlspecialchars($editCategory['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?php echo $editCategory ? htmlspecialchars($editCategory['description'] ?? '') : (isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="icon">Icône (emoji)</label>
                    <input type="text" id="icon" name="icon" placeholder="Ex: 🛋️" maxlength="10"
                           value="<?php echo $editCategory ? htmlspecialchars($editCategory['icon'] ?? '') : (isset($_POST['icon']) ? htmlspecialchars($_POST['icon']) : ''); ?>">
                    <small style="color: var(--text-light);">Optionnel : un emoji pour représenter la catégorie</small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">
                        <?php echo $editCategory ? 'Enregistrer les modifications' : 'Ajouter la catégorie'; ?>
                    </button>
                    <a href="categories.php" class="btn" style="background: var(--text-light); text-decoration: none; display: inline-block;">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des catégories -->
    <h2 style="margin-bottom: 1rem;">Liste des catégories</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Icône</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Produits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem;">
                            Aucune catégorie trouvée. Cliquez sur "Ajouter une catégorie" pour en créer une.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $category): ?>
                    <tr>
                        <td><?php echo $category['id']; ?></td>
                        <td style="font-size: 1.5rem;"><?php echo htmlspecialchars($category['icon'] ?? ''); ?></td>
                        <td><strong><?php echo htmlspecialchars($category['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($category['description'] ?? 'Aucune description'); ?></td>
                        <td><?php echo $category['product_count']; ?> produit(s)</td>
                        <td>
                            <div class="admin-actions">
                                <a href="categories.php?edit=<?php echo $category['id']; ?>" class="btn-edit" title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="categories.php?delete=<?php echo $category['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')"
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
        toggleBtn.textContent = '➕ Ajouter une catégorie';
        toggleBtn.style.background = '';
    }
}

// Initialiser le formulaire si on est en mode édition
document.addEventListener('DOMContentLoaded', function() {
    const formContainer = document.getElementById('add-form-container');
    const toggleBtn = document.getElementById('toggle-btn');
    
    <?php if ($editCategory || isset($_GET['edit'])): ?>
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

