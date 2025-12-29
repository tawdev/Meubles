<?php
$pageTitle = "Gestion des articles par type";
require_once 'includes/header.php';

$success = false;
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = trim($_POST['name'] ?? '');
                $typeCategoryId = intval($_POST['types_categories_id'] ?? 0);
                $image = '';

                // Gestion de l'upload d'image
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/items/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                    $fileType = $_FILES['image_file']['type'];

                    if (in_array($fileType, $allowedTypes)) {
                        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['image_file']['name']));
                        $targetFile = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $targetFile)) {
                            $image = 'images/items/' . $fileName;
                        } else {
                            $error = 'Erreur lors de l\'upload de l\'image.';
                        }
                    } else {
                        $error = 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
                    }
                }

                if (empty($name) || $typeCategoryId <= 0) {
                    $error = 'Le nom et le type de catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("INSERT INTO types_categories_items (name, types_categories_id, image) VALUES (?, ?, ?)");
                        $stmt->execute([$name, $typeCategoryId, $image]);
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
                $typeCategoryId = intval($_POST['types_categories_id'] ?? 0);

                // Récupérer l'ancienne image
                $stmt = $pdo->prepare("SELECT image FROM types_categories_items WHERE id = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch();
                $image = $item['image'] ?? '';

                // Gestion de l'upload d'image si une nouvelle est fournie
                if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../images/items/';
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
                                @unlink('../' . $image);
                            }
                            $image = 'images/items/' . $fileName;
                        } else {
                            $error = 'Erreur lors de l\'upload de l\'image.';
                        }
                    } else {
                        $error = 'Type de fichier non autorisé. Formats acceptés : JPG, PNG, GIF, WEBP';
                    }
                }

                if (empty($name) || $typeCategoryId <= 0) {
                    $error = 'Le nom et le type de catégorie sont obligatoires.';
                } else {
                    try {
                        $stmt = $pdo->prepare("UPDATE types_categories_items SET name = ?, types_categories_id = ?, image = ? WHERE id = ?");
                        $stmt->execute([$name, $typeCategoryId, $image, $id]);
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
        // Récupérer le chemin de l'image avant suppression pour la supprimer du disque
        $stmt = $pdo->prepare("SELECT image FROM types_categories_items WHERE id = ?");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        $deleteStmt = $pdo->prepare("DELETE FROM types_categories_items WHERE id = ?");
        $deleteStmt->execute([$id]);

        if ($item && !empty($item['image']) && file_exists('../' . $item['image'])) {
            @unlink('../' . $item['image']);
        }

        $success = true;
    } catch (PDOException $e) {
        $error = 'Erreur lors de la suppression : ' . $e->getMessage();
    }
}

// Récupérer les catégories pour le filtre
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    $categoriesList = [];
}

// Récupérer tous les types de catégories
try {
    $typesCategoriesList = $pdo->query("SELECT tc.*, c.name as category_name FROM types_categories tc LEFT JOIN categories c ON tc.category_id = c.id ORDER BY c.name, tc.name")->fetchAll();
} catch (PDOException $e) {
    $typesCategoriesList = [];
}

// Récupérer les filtres depuis l'URL
$filterCategory = $_GET['filter_category'] ?? '';
$filterTypeCategory = $_GET['filter_type_category'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Construire la requête
$query = "
    SELECT tci.*, tc.name as type_category_name, c.name as category_name
    FROM types_categories_items tci
    JOIN types_categories tc ON tci.types_categories_id = tc.id
    LEFT JOIN categories c ON tc.category_id = c.id
    WHERE 1=1
";

$params = [];

if (!empty($filterCategory)) {
    $query .= " AND tc.category_id = ?";
    $params[] = $filterCategory;
}

if (!empty($filterTypeCategory)) {
    $query .= " AND tci.types_categories_id = ?";
    $params[] = $filterTypeCategory;
}

if (!empty($searchTerm)) {
    $query .= " AND tci.name LIKE ?";
    $params[] = '%' . $searchTerm . '%';
}

$query .= " ORDER BY c.name, tc.name, tci.name";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $items = $stmt->fetchAll();
} catch (PDOException $e) {
    $items = [];
    if (strpos($e->getMessage(), "doesn't exist") !== false) {
        $error = "La table `types_categories_items` n'existe pas encore. Veuillez exécuter le fichier SQL de migration.";
    }
}

// Élément à modifier
$editItem = null;
$currentCategoryId = 0;
if (isset($_GET['edit'])) {
    $editId = intval($_GET['edit']);
    try {
        $editStmt = $pdo->prepare("
            SELECT tci.*, tc.category_id 
            FROM types_categories_items tci
            JOIN types_categories tc ON tci.types_categories_id = tc.id
            WHERE tci.id = ?
        ");
        $editStmt->execute([$editId]);
        $editItem = $editStmt->fetch();
        if ($editItem) {
            $currentCategoryId = $editItem['category_id'];
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération : ' . $e->getMessage();
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des articles par type</h1>
        <button onclick="toggleAddForm()" class="btn" id="toggle-btn">➕ Ajouter un article</button>
    </div>

    <?php if ($success): ?>
        <div class="success-message">Opération réussie !</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout/modification -->
    <div id="add-form-container" style="display: <?php echo ($editItem) ? 'block' : 'none'; ?>; margin-bottom: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">
                <?php echo $editItem ? 'Modifier l\'article' : 'Ajouter un nouvel article'; ?>
            </h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $editItem ? 'edit' : 'add'; ?>">
                <?php if ($editItem): ?>
                    <input type="hidden" name="id" value="<?php echo $editItem['id']; ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="category_id">Catégorie *</label>
                    <select id="category_id" name="category_id" required onchange="loadTypes(this.value)">
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo ($currentCategoryId == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="types_categories_id">Type de catégorie *</label>
                    <select id="types_categories_id" name="types_categories_id" required>
                        <option value="">Sélectionner d'abord une catégorie</option>
                        <?php if ($editItem): ?>
                            <?php
                            // Re-charger les types pour la catégorie actuelle lors de l'édition
                            $typesStmt = $pdo->prepare("SELECT id, name FROM types_categories WHERE category_id = ? ORDER BY name");
                            $typesStmt->execute([$currentCategoryId]);
                            $currentTypes = $typesStmt->fetchAll();
                            foreach ($currentTypes as $t): ?>
                                <option value="<?php echo $t['id']; ?>" <?php echo ($editItem['types_categories_id'] == $t['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($t['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Nom de l'article *</label>
                    <input type="text" id="name" name="name" required
                        value="<?php echo $editItem ? htmlspecialchars($editItem['name']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="image_file">Image</label>
                    <?php if ($editItem && !empty($editItem['image'])): ?>
                        <div style="margin-bottom: 0.5rem;">
                            <img src="../<?php echo htmlspecialchars($editItem['image']); ?>"
                                style="max-width: 100px; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image_file" name="image_file" accept="image/*">
                </div>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn"><?php echo $editItem ? 'Mettre à jour' : 'Ajouter'; ?></button>
                    <a href="types_categories_items.php" class="btn"
                        style="background: var(--text-light); text-decoration: none;">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtres -->
    <div
        style="background: #f8f9fa; padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem; border: 1px solid #ddd;">
        <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: flex-end;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem;">Catégorie</label>
                <select name="filter_category" onchange="this.form.submit()"
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="">Toutes</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($filterCategory == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem;">Type de catégorie</label>
                <select name="filter_type_category" onchange="this.form.submit()"
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc;">
                    <option value="">Tous</option>
                    <?php foreach ($typesCategoriesList as $tc): ?>
                        <?php if (empty($filterCategory) || $tc['category_id'] == $filterCategory): ?>
                            <option value="<?php echo $tc['id']; ?>" <?php echo ($filterTypeCategory == $tc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tc['category_name'] . ' > ' . $tc['name']); ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem;">Rechercher</label>
                <input type="text" name="search" value="<?php echo htmlspecialchars($searchTerm); ?>"
                    placeholder="Nom de l'article..."
                    style="width: 100%; padding: 0.75rem; border-radius: 8px; border: 1px solid #ccc;">
            </div>

            <button type="submit" class="btn">🔍 Filtrer</button>
            <a href="types_categories_items.php" class="btn"
                style="background: var(--text-light); text-decoration: none;">🔄</a>
        </form>
    </div>

    <!-- Liste -->
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Nom</th>
                <th>Type de catégorie</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($items)): ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem;">Aucun article trouvé.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['id']; ?></td>
                        <td>
                            <?php if (!empty($item['image'])): ?>
                                <img src="../<?php echo htmlspecialchars($item['image']); ?>"
                                    style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            <?php else: ?>
                                <span style="color: #999;">Pas d'image</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo htmlspecialchars($item['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($item['category_name'] . ' > ' . $item['type_category_name']); ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="?edit=<?php echo $item['id']; ?>" class="btn-edit">✏️</a>
                                <a href="?delete=<?php echo $item['id']; ?>" class="btn-delete"
                                    onclick="return confirm('Supprimer cet article ?')">🗑️</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function toggleAddForm() {
        const form = document.getElementById('add-form-container');
        const btn = document.getElementById('toggle-btn');
        if (form.style.display === 'none') {
            form.style.display = 'block';
            btn.textContent = '❌ Annuler';
            form.scrollIntoView({ behavior: 'smooth' });
        } else {
            form.style.display = 'none';
            btn.textContent = '➕ Ajouter un article';
        }
    }

    function loadTypes(categoryId) {
        const typeSelect = document.getElementById('types_categories_id');
        typeSelect.innerHTML = '<option value="">Chargement...</option>';

        if (!categoryId) {
            typeSelect.innerHTML = '<option value="">Sélectionner d\'abord une catégorie</option>';
            return;
        }

        fetch('get_types_by_category.php?category_id=' + categoryId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    typeSelect.innerHTML = '<option value="">Sélectionner un type</option>';
                    data.types.forEach(type => {
                        const option = document.createElement('option');
                        option.value = type.id;
                        option.textContent = type.name;
                        typeSelect.appendChild(option);
                    });
                } else {
                    typeSelect.innerHTML = '<option value="">Erreur lors du chargement</option>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                typeSelect.innerHTML = '<option value="">Erreur serveur</option>';
            });
    }
</script>

<?php require_once 'includes/footer.php'; ?>