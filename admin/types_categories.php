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
                $typeId = !empty($_POST['type_id']) ? intval($_POST['type_id']) : null;
                $image = trim($_POST['image'] ?? ''); // Pour compatibilité avec saisie manuelle
                
                // Gestion de l'upload d'image
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/types/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = $_FILES['image_file']['type'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image_file']['name']));
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                            $image = 'images/types/' . $fileName;
                        } else {
                            $error = 'Erreur lors de l\'upload de l\'image.';
                        }
                    } else {
                        $error = 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
                    }
                }
                
                if (empty($name) || $categoryId <= 0) {
                    $error = 'Le nom et la catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO types_categories (name, category_id, types_id, image) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$name, $categoryId, $typeId, $image]);
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
                $typeId = !empty($_POST['type_id']) ? intval($_POST['type_id']) : null;
                $image = $editType['image'] ?? ''; // Conserver l'image existante par défaut
                
                // Gestion de l'upload d'image si une nouvelle est fournie
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/types/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = $_FILES['image_file']['type'];
                    
                    if (in_array($fileType, $allowedTypes)) {
                        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image_file']['name']));
                        $targetFile = $uploadDir . $fileName;
                        
                        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                            // Supprimer l'ancienne image si elle existe
                            if (!empty($image) && file_exists('../' . $image)) {
                                unlink('../' . $image);
                            }
                            $image = 'images/types/' . $fileName;
                        } else {
                            $error = 'Erreur lors de l\'upload de l\'image.';
                        }
                    } else {
                        $error = 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
                    }
                } elseif (!empty($_POST['image'])) {
                    // Permettre aussi la saisie manuelle du chemin
                    $image = trim($_POST['image']);
                }
                
                if (empty($name) || $categoryId <= 0) {
                    $error = 'Le nom et la catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE types_categories SET name = ?, category_id = ?, types_id = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $categoryId, $typeId, $image, $id]);
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

// Récupérer tous les types (En stock, Sur mesure)
$allTypes = [];
try {
    $typesStmt = $pdo->query("SELECT * FROM types ORDER BY name");
    $allTypes = $typesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table types n'existe pas encore
    $allTypes = [];
}

// Récupérer les filtres depuis l'URL
$filterCategory = $_GET['filter_category'] ?? '';
$filterType = $_GET['filter_type'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Construire la requête avec filtres
$query = "
    SELECT tc.*, c.name as category_name, c.image as category_image,
           t.name as type_name, t.id as type_id,
           (SELECT COUNT(*) FROM products WHERE type_category_id = tc.id) as product_count
    FROM types_categories tc
    LEFT JOIN categories c ON tc.category_id = c.id
    LEFT JOIN types t ON tc.types_id = t.id
    WHERE 1=1
";

$params = [];

if (!empty($filterCategory)) {
    $query .= " AND tc.category_id = ?";
    $params[] = $filterCategory;
}

if (!empty($filterType)) {
    $query .= " AND tc.types_id = ?";
    $params[] = $filterType;
}

if (!empty($searchTerm)) {
    $query .= " AND (tc.name LIKE ? OR c.name LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY c.name, tc.name";

try {
    if (!empty($params)) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($query);
    }
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
        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
            <button onclick="toggleAddForm()" class="btn" id="toggle-btn">➕ Ajouter un type</button>
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

    <!-- Formulaire d'ajout/modification (masqué par défaut) -->
    <div id="add-form-container" style="display: <?php echo ($editType || isset($_GET['edit'])) ? 'block' : 'none'; ?>; margin-bottom: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">
                <?php echo $editType ? 'Modifier le type de catégorie' : 'Ajouter un nouveau type de catégorie'; ?>
            </h2>
            <form method="POST" action="types_categories.php" enctype="multipart/form-data">
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
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="type_id">Type (stock/mesure)</label>
                    <select id="type_id" name="type_id">
                        <option value="">Sélectionner un type (optionnel)</option>
                        <?php foreach ($allTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                    <?php echo ($editType && isset($editType['types_id']) && $editType['types_id'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-light);">Sélectionnez si ce type de catégorie est "En stock" ou "Sur mesure"</small>
                </div>
                
                <div class="form-group">
                    <label for="name">Nom du type *</label>
                    <input type="text" id="name" name="name" required 
                           placeholder="Ex: Canapé, Table basse, Lit..."
                           value="<?php echo $editType ? htmlspecialchars($editType['name']) : (isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''); ?>">
                    <small style="color: var(--text-light);">Le nom du type de catégorie (ex: Canapé pour Salon)</small>
                </div>
                
                <div class="form-group">
                    <label for="image_file">Image du type</label>
                    <?php if ($editType && !empty($editType['image'])): ?>
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem; color: var(--text-light);">Image actuelle :</label>
                            <img src="../<?php echo htmlspecialchars($editType['image']); ?>" 
                                 alt="Image actuelle" 
                                 style="max-width: 200px; height: auto; border-radius: 8px; border: 2px solid var(--border-light);"
                                 onerror="this.style.display='none';">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image_file" name="image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                           style="padding: 0.5rem; border: 2px dashed var(--border-light); border-radius: 8px; width: 100%; cursor: pointer; background: var(--bg-light);">
                    <small style="color: var(--text-light); display: block; margin-top: 0.5rem;">
                        Formats acceptés : JPG, PNG, GIF, WEBP (max 5MB). 
                        <?php if ($editType): ?>
                            Laisser vide pour conserver l'image actuelle.
                        <?php endif; ?>
                    </small>
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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
        <h2 style="margin-bottom: 0;">Liste des types de catégories</h2>
        <div style="background: var(--bg-light); padding: 0.75rem 1.5rem; border-radius: 25px; color: var(--primary-color); font-weight: 600; font-size: 1rem;">
            <?php echo count($typesCategories); ?> type(s) trouvé(s)
        </div>
    </div>
    
    <!-- Filtres -->
    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05);">
        <form method="GET" action="types_categories.php" id="filter-form" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <!-- Préserver le paramètre edit si présent -->
            <?php if (isset($_GET['edit'])): ?>
                <input type="hidden" name="edit" value="<?php echo htmlspecialchars($_GET['edit']); ?>">
            <?php endif; ?>
            
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                <select name="filter_category" id="filter-category-list" 
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($filterCategory == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                <select name="filter_type" id="filter-type-list" 
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
                    <option value="">Tous les types (stock/mesure)</option>
                    <?php foreach ($allTypes as $type): ?>
                        <option value="<?php echo $type['id']; ?>" 
                                <?php echo ($filterType == $type['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="display: flex; align-items: center; gap: 0.5rem; flex: 2; min-width: 250px; position: relative;">
                <input type="text" name="search" 
                       placeholder="Rechercher un type de catégorie..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>"
                       style="flex: 1; padding: 0.875rem 1rem 0.875rem 2.75rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
                <span style="position: absolute; left: 1rem; color: var(--text-light); font-size: 1.1rem;">🔍</span>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="types_categories.php<?php echo isset($_GET['edit']) ? '?edit=' . htmlspecialchars($_GET['edit']) : ''; ?>" 
                   style="padding: 0.875rem 1.5rem; border: none; border-radius: 8px; font-size: 0.95rem; background: var(--text-light); color: white; cursor: pointer; font-weight: 600; text-decoration: none; white-space: nowrap; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: inline-block; text-align: center;">
                    🔄 Réinitialiser
                </a>
            </div>
        </form>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Catégorie</th>
                    <th>Image du type</th>
                    <th>Type (stock/mesure)</th>
                    <th>Nom du type</th>
                    <th>Produits</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($typesCategories)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">
                            <?php if (!empty($filterCategory) || !empty($filterType) || !empty($searchTerm)): ?>
                                Aucun type de catégorie trouvé avec ces filtres. <a href="types_categories.php" style="color: var(--primary-color);">Réinitialiser les filtres</a>
                            <?php elseif (empty($categoriesList)): ?>
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
                            <strong><?php echo htmlspecialchars($type['category_name'] ?? 'N/A'); ?></strong>
                        </td>
                        <td>
                            <?php if (!empty($type['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($type['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($type['name']); ?>" 
                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px; border: 1px solid var(--border-light);"
                                     onerror="this.src='../images/placeholder.jpg';">
                            <?php else: ?>
                                <span style="color: var(--text-light);">Aucune image</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($type['type_name'])): ?>
                                <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: var(--text-light);">-</span>
                            <?php endif; ?>
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
    
    // Filtrage automatique lors du changement des filtres
    const filterForm = document.getElementById('filter-form');
    if (filterForm) {
        const filterInputs = filterForm.querySelectorAll('select[name], input[name="search"]');
        filterInputs.forEach(input => {
            input.addEventListener('change', function() {
                // Délai pour éviter trop de requêtes si l'utilisateur tape rapidement
                if (input.name === 'search') {
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                } else {
                    filterForm.submit();
                }
            });
            
            // Pour le champ de recherche, aussi écouter l'événement input
            if (input.name === 'search') {
                input.addEventListener('input', function() {
                    clearTimeout(window.searchTimeout);
                    window.searchTimeout = setTimeout(function() {
                        filterForm.submit();
                    }, 500);
                });
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

