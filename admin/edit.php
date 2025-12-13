<?php
$pageTitle = "Modifier un produit";
require_once 'includes/header.php';

if (!isset($_GET['id'])) {
    header('Location: add.php');
    exit;
}

$productId = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$productId]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: add.php');
    exit;
}

// Récupérer toutes les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['id' => 1, 'name' => 'Salon', 'icon' => '🛋️'],
        ['id' => 2, 'name' => 'Chambre', 'icon' => '🛏️'],
        ['id' => 3, 'name' => 'Salle à manger', 'icon' => '🍽️'],
        ['id' => 4, 'name' => 'Bureau', 'icon' => '💼'],
        ['id' => 5, 'name' => 'Décoration', 'icon' => '🖼️']
    ];
}

// Déterminer la catégorie actuelle du produit
$currentCategoryId = $product['category_id'] ?? null;
$currentCategoryName = $product['category'] ?? '';

// Si category_id n'existe pas mais category existe, trouver l'ID de la catégorie
if (!$currentCategoryId && $currentCategoryName) {
    try {
        $findCatStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ? LIMIT 1");
        $findCatStmt->execute([$currentCategoryName]);
        $foundCat = $findCatStmt->fetch();
        if ($foundCat) {
            $currentCategoryId = $foundCat['id'];
        }
    } catch (PDOException $e) {
        // Ignorer l'erreur
    }
}

// Récupérer les types de catégorie si le produit a une catégorie
$typesList = [];
if ($currentCategoryId) {
    try {
        $typesStmt = $pdo->prepare("SELECT * FROM types_categories WHERE category_id = ? ORDER BY name");
        $typesStmt->execute([$currentCategoryId]);
        $typesList = $typesStmt->fetchAll();
    } catch (PDOException $e) {
        // Table n'existe pas encore
        $typesList = [];
    }
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $categoryId = intval($_POST['category_id'] ?? 0);
    $typeCategoryId = !empty($_POST['type_category_id']) ? intval($_POST['type_category_id']) : null;
    $category = trim($_POST['category'] ?? ''); // Garder pour compatibilité
    $stock = $_POST['stock'] ?? 0;
    $image = $product['image']; // Conserver l'image existante par défaut
    
    // Gestion de l'upload d'image si une nouvelle est fournie
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = 'images/' . $fileName;
        }
    }
    
    if (empty($name) || empty($price) || empty($categoryId)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Le prix doit être un nombre positif.';
    } else {
        try {
            // Récupérer le nom de la catégorie pour compatibilité
            if ($categoryId > 0) {
                $catStmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
                $catStmt->execute([$categoryId]);
                $catData = $catStmt->fetch();
                $category = $catData ? $catData['name'] : $category;
            }
            
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ?, category_id = ?, type_category_id = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $image, $category, $categoryId, $typeCategoryId, $stock, $productId]);
            // Rediriger vers add.php après modification réussie
            header('Location: add.php?success=1&id=' . $productId);
            exit;
        } catch (PDOException $e) {
            $error = 'Erreur lors de la modification du produit : ' . $e->getMessage();
        }
    }
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Modifier le produit</h1>
        <a href="add.php" class="btn">← Retour</a>
    </div>
    
    <?php if ($error): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST" action="edit.php?id=<?php echo $productId; ?>" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Nom du produit *</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Prix (DH) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $product['price']; ?>">
            </div>
            
            <div class="form-group">
                <label for="category_id">Catégorie *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php 
                    // Déterminer quelle catégorie est sélectionnée
                    $selectedCategoryId = $currentCategoryId;
                    if (!$selectedCategoryId && $currentCategoryName) {
                        // Si pas d'ID mais un nom, chercher l'ID
                        foreach ($categoriesList as $cat) {
                            if (isset($cat['name']) && $cat['name'] === $currentCategoryName) {
                                $selectedCategoryId = $cat['id'];
                                break;
                            }
                        }
                    }
                    
                    foreach ($categoriesList as $cat): 
                        $catId = isset($cat['id']) ? $cat['id'] : null;
                        $isSelected = ($selectedCategoryId && $catId && intval($selectedCategoryId) == intval($catId)) || 
                                      (!$selectedCategoryId && isset($cat['name']) && $cat['name'] === $currentCategoryName);
                    ?>
                        <option value="<?php echo $catId; ?>" 
                                <?php echo $isSelected ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="hidden" id="category" name="category" value="<?php echo htmlspecialchars($currentCategoryName); ?>">
            </div>
            
            <div class="form-group" id="type-category-group" style="<?php echo (!empty($typesList) || ($currentCategoryId && $currentCategoryId > 0)) ? '' : 'display: none;'; ?>">
                <label for="type_category_id">Type de catégorie</label>
                <select id="type_category_id" name="type_category_id">
                    <option value="">Sélectionner un type (optionnel)</option>
                    <?php 
                    $currentTypeId = $product['type_category_id'] ?? null;
                    foreach ($typesList as $type): 
                    ?>
                        <option value="<?php echo $type['id']; ?>" 
                                <?php echo $currentTypeId == $type['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small style="color: var(--text-light);">Sélectionnez un type de catégorie pour mieux classer votre produit</small>
            </div>
            
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" min="0" value="<?php echo $product['stock']; ?>">
            </div>
            
            <div class="form-group">
                <label>Image actuelle</label>
                <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                     alt="Image actuelle" 
                     style="max-width: 200px; height: auto; border-radius: 5px; margin-bottom: 1rem;"
                     onerror="this.src='https://via.placeholder.com/200x200?text=Produit'">
                <label for="image">Nouvelle image (laisser vide pour conserver l'actuelle)</label>
                <input type="file" id="image" name="image" accept="image/*">
                <small style="color: var(--text-light);">Formats acceptés : JPG, PNG, GIF (max 5MB)</small>
            </div>
            
            <button type="submit" class="btn">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<script>
// Charger les types de catégorie
function loadTypesByCategory(categoryId, selectedTypeId = null) {
    const typeSelect = document.getElementById('type_category_id');
    const typeGroup = document.getElementById('type-category-group');
    const categoryInput = document.getElementById('category');
    
    if (!typeSelect || !typeGroup || !categoryInput) {
        console.error('Éléments du formulaire non trouvés');
        return;
    }
    
    // Réinitialiser le select des types
    typeSelect.innerHTML = '<option value="">Sélectionner un type (optionnel)</option>';
    
    if (!categoryId || categoryId === '' || categoryId === '0') {
        typeGroup.style.display = 'none';
        if (categoryInput) {
            categoryInput.value = '';
        }
        return;
    }
    
    // Récupérer le nom de la catégorie
    const categorySelect = document.getElementById('category_id');
    if (categorySelect && categorySelect.selectedIndex >= 0) {
        const selectedOption = categorySelect.options[categorySelect.selectedIndex];
        if (selectedOption && categoryInput) {
            categoryInput.value = selectedOption.textContent.replace(/[^\w\s]/g, '').trim();
        }
    }
    
    // Charger les types via AJAX
    fetch(`get_types_by_category.php?category_id=${categoryId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau');
            }
            return response.json();
        })
        .then(data => {
            if (data.success && data.types && data.types.length > 0) {
                data.types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    if (selectedTypeId && parseInt(type.id) === parseInt(selectedTypeId)) {
                        option.selected = true;
                    }
                    typeSelect.appendChild(option);
                });
                typeGroup.style.display = 'block';
            } else {
                // Afficher le groupe même s'il n'y a pas de types, pour montrer qu'il n'y a pas de types disponibles
                typeGroup.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des types:', error);
            typeGroup.style.display = 'none';
        });
}

// Écouter les changements de catégorie
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category_id');
    const typeSelect = document.getElementById('type_category_id');
    const currentTypeId = <?php echo isset($product['type_category_id']) && $product['type_category_id'] ? $product['type_category_id'] : 'null'; ?>;
    const currentCategoryId = <?php echo $currentCategoryId ? $currentCategoryId : 'null'; ?>;
    
    if (categorySelect) {
        // Vérifier que la catégorie est bien sélectionnée
        if (categorySelect.value && categorySelect.value !== '' && categorySelect.value !== '0') {
            // Attendre un peu pour s'assurer que le DOM est prêt
            setTimeout(function() {
                loadTypesByCategory(categorySelect.value, currentTypeId);
            }, 200);
        } else if (currentCategoryId) {
            // Si la catégorie n'est pas sélectionnée mais qu'on a l'ID, la sélectionner
            categorySelect.value = currentCategoryId;
            setTimeout(function() {
                loadTypesByCategory(currentCategoryId, currentTypeId);
            }, 200);
        }
        
        // Écouter les changements de catégorie
        categorySelect.addEventListener('change', function() {
            loadTypesByCategory(this.value);
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

