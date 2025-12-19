<?php
$pageTitle = "Gestion des produits";
require_once 'includes/header.php';

$success = false;
$error = '';

// Vérifier si on vient d'une modification réussie
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $success = true;
}

// Traitement du formulaire d'ajout
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $categoryId = intval($_POST['category_id'] ?? 0);
    $typeCategoryId = !empty($_POST['type_category_id']) ? intval($_POST['type_category_id']) : null;
    $typeId = !empty($_POST['type_id']) ? intval($_POST['type_id']) : null;
    $category = trim($_POST['category'] ?? ''); // Garder pour compatibilité
    $stock = $_POST['stock'] ?? 0;
    $maxLength = !empty($_POST['max_length']) ? floatval($_POST['max_length']) : null;
    $maxWidth = !empty($_POST['max_width']) ? floatval($_POST['max_width']) : null;
    
    // Gestion de l'upload d'image
    $image = '';
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
            
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock, max_length, max_width) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image ?: 'images/placeholder.jpg', $category, $categoryId, $typeCategoryId, $stock, $maxLength, $maxWidth]);
            $success = true;
            $_POST = []; // Réinitialiser le formulaire
        } catch (PDOException $e) {
            $error = 'Erreur lors de l\'ajout du produit : ' . $e->getMessage();
        }
    }
}

// Récupérer toutes les catégories
try {
    $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categoriesList = $categoriesStmt->fetchAll();
} catch (PDOException $e) {
    // Si la table categories n'existe pas, utiliser les catégories par défaut
    $categoriesList = [
        ['name' => 'Salon', 'icon' => '🛋️'],
        ['name' => 'Chambre', 'icon' => '🛏️'],
        ['name' => 'Salle à manger', 'icon' => '🍽️'],
        ['name' => 'Bureau', 'icon' => '💼'],
        ['name' => 'Décoration', 'icon' => '🖼️']
    ];
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
$filterTypeCategory = $_GET['filter_type_category'] ?? '';
$searchTerm = $_GET['search'] ?? '';

// Construire la requête avec filtres
$query = "
    SELECT p.*, 
           c.name as category_name, c.icon as category_icon,
           tc.name as type_category_name, tc.id as type_category_id,
           t.name as type_name, t.id as type_id
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    LEFT JOIN types_categories tc ON p.type_category_id = tc.id
    LEFT JOIN types t ON tc.types_id = t.id
    WHERE 1=1
";

$params = [];

if (!empty($filterCategory)) {
    $query .= " AND (p.category_id = ? OR p.category = ?)";
    $params[] = $filterCategory;
    $params[] = $filterCategory;
}

if (!empty($filterType)) {
    $query .= " AND t.id = ?";
    $params[] = $filterType;
}

if (!empty($filterTypeCategory)) {
    $query .= " AND tc.id = ?";
    $params[] = $filterTypeCategory;
}

if (!empty($searchTerm)) {
    $query .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchParam = '%' . $searchTerm . '%';
    $params[] = $searchParam;
    $params[] = $searchParam;
}

$query .= " ORDER BY p.id DESC";

try {
    if (!empty($params)) {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    } else {
        $stmt = $pdo->query($query);
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // En cas d'erreur, récupérer tous les produits
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Gestion des produits</h1>
        <button onclick="toggleAddForm()" class="btn" id="toggle-btn">➕ Ajouter un produit</button>
    </div>

    <?php if ($success): ?>
        <div class="success-message">
            <?php if (isset($_GET['id'])): ?>
                Produit modifié avec succès !
            <?php else: ?>
                Produit ajouté avec succès !
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire d'ajout (masqué par défaut) -->
    <div id="add-form-container" style="display: none; margin-bottom: 2rem;">
        <div class="form-container">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">Ajouter un nouveau produit</h2>
            <form method="POST" action="add.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label for="name">Nom du produit *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price" id="price-label">Prix (DH) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                    <small id="price-hint" style="color: var(--text-light); display: none;">Prix par m² pour les produits sur mesure</small>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Catégorie *</label>
                    <select id="category_id" name="category_id" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="hidden" id="category" name="category" value="<?php echo isset($_POST['category']) ? htmlspecialchars($_POST['category']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="type_id">Type (stock/mesure)</label>
                    <select id="type_id" name="type_id" onchange="onTypeChange()">
                        <option value="">Tous les types</option>
                        <?php foreach ($allTypes as $type): ?>
                            <option value="<?php echo $type['id']; ?>" 
                                    <?php echo (isset($_POST['type_id']) && $_POST['type_id'] == $type['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color: var(--text-light);">Sélectionnez un type pour filtrer les types de catégories disponibles</small>
                </div>
                
                <div class="form-group" id="type-category-group" style="display: none;">
                    <label for="type_category_id">Type de catégorie</label>
                    <select id="type_category_id" name="type_category_id">
                        <option value="">Sélectionner un type (optionnel)</option>
                    </select>
                </div>
                
                <!-- Champs de dimensions pour produits sur mesure -->
                <div class="form-group" id="dimensions-group" style="display: none;">
                    <label style="color: var(--primary-color); font-weight: 600; margin-bottom: 0.5rem; display: block;">📏 Dimensions maximales (pour produits sur mesure)</label>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label for="max_length">Longueur maximale (cm)</label>
                            <input type="number" id="max_length" name="max_length" step="0.01" min="0" 
                                   value="<?php echo isset($_POST['max_length']) ? htmlspecialchars($_POST['max_length']) : ''; ?>"
                                   placeholder="Ex: 200.00">
                        </div>
                        <div>
                            <label for="max_width">Largeur maximale (cm)</label>
                            <input type="number" id="max_width" name="max_width" step="0.01" min="0" 
                                   value="<?php echo isset($_POST['max_width']) ? htmlspecialchars($_POST['max_width']) : ''; ?>"
                                   placeholder="Ex: 150.00">
                        </div>
                    </div>
                    <small style="color: var(--text-light);">Ces dimensions s'affichent uniquement pour les produits sur mesure</small>
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo isset($_POST['stock']) ? htmlspecialchars($_POST['stock']) : 0; ?>">
                </div>
                
                <div class="form-group">
                    <label for="image">Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: var(--text-light);">Formats acceptés : JPG, PNG, GIF (max 5MB)</small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn">Ajouter le produit</button>
                    <button type="button" onclick="toggleAddForm()" class="btn" style="background: var(--text-light);">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Liste des produits -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
        <h2 style="margin-bottom: 0;">Liste des produits</h2>
        <div style="background: var(--bg-light); padding: 0.75rem 1.5rem; border-radius: 25px; color: var(--primary-color); font-weight: 600; font-size: 1rem;">
            <?php echo count($products); ?> produit(s) trouvé(s)
        </div>
    </div>
    
    <!-- Filtres -->
    <div style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid rgba(0,0,0,0.05);">
        <form method="GET" action="add.php" id="filter-form" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem; flex: 1; min-width: 200px;">
                <select name="filter_category" id="filter-category-list" 
                        style="flex: 1; padding: 0.875rem 1rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); cursor: pointer; box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                                <?php echo ($filterCategory == $cat['id'] || $filterCategory == $cat['name']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
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
                       placeholder="Rechercher un produit..." 
                       value="<?php echo htmlspecialchars($searchTerm); ?>"
                       style="flex: 1; padding: 0.875rem 1rem 0.875rem 2.75rem; border: none; border-radius: 8px; font-size: 0.95rem; background: white; color: var(--text-dark); box-shadow: 0 2px 5px rgba(0,0,0,0.08);">
                <span style="position: absolute; left: 1rem; color: var(--text-light); font-size: 1.1rem;">🔍</span>
            </div>
            
            <div style="display: flex; gap: 0.5rem;">
                <a href="add.php" 
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
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Type</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2rem;">
                            <?php if (!empty($filterCategory) || !empty($filterType) || !empty($searchTerm)): ?>
                                Aucun produit trouvé avec ces filtres. <a href="add.php" style="color: var(--primary-color);">Réinitialiser les filtres</a>
                            <?php else: ?>
                                Aucun produit trouvé. Cliquez sur "Ajouter un produit" pour en créer un.
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?php echo $product['id']; ?></td>
                        <td>
                            <img src="../<?php echo htmlspecialchars($product['image']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 5px;"
                                 onerror="this.src='https://via.placeholder.com/60x60?text=Produit'">
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td>
                            <span style="font-size: 1.2rem;"><?php echo htmlspecialchars($product['category_icon'] ?? ''); ?></span>
                            <?php echo htmlspecialchars($product['category'] ?? $product['category_name'] ?? 'N/A'); ?>
                        </td>
                        <td>
                            <?php if (!empty($product['type_name'])): ?>
                                <span style="background: var(--primary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                    <?php echo htmlspecialchars($product['type_name']); ?>
                                </span>
                                <?php if (!empty($product['type_category_name'])): ?>
                                    <br><small style="color: var(--text-light);"><?php echo htmlspecialchars($product['type_category_name']); ?></small>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: var(--text-light);">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo number_format($product['price'], 2, ',', ' '); ?> DH</td>
                        <td><?php echo isset($product['stock']) ? $product['stock'] : 'N/A'; ?></td>
                        <td>
                            <div class="admin-actions">
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-edit" title="Modifier">
                                    ✏️ Modifier
                                </a>
                                <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')"
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
    
    if (formContainer.style.display === 'none') {
        formContainer.style.display = 'block';
        toggleBtn.textContent = '❌ Annuler';
        toggleBtn.style.background = 'var(--text-light)';
        // Scroll vers le formulaire
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        formContainer.style.display = 'none';
        toggleBtn.textContent = '➕ Ajouter un produit';
        toggleBtn.style.background = '';
    }
}

// Charger les types de catégorie
function loadTypesByCategory(categoryId) {
    const typeSelect = document.getElementById('type_category_id');
    const typeGroup = document.getElementById('type-category-group');
    const categoryInput = document.getElementById('category');
    const typeFilter = document.getElementById('type_id');
    
    if (!typeSelect || !typeGroup) {
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
    
    // Récupérer le type sélectionné (En stock / Sur mesure)
    const selectedTypeId = typeFilter ? typeFilter.value : '';
    
    // Afficher le groupe pendant le chargement
    typeGroup.style.display = 'block';
    typeSelect.disabled = true;
    typeSelect.innerHTML = '<option value="">Chargement...</option>';
    
    // Charger les types via AJAX avec le filtre de type si sélectionné
    let apiUrl = `get_types_by_category.php?category_id=${categoryId}`;
    if (selectedTypeId && selectedTypeId !== '' && selectedTypeId !== 'all') {
        apiUrl += `&type_id=${selectedTypeId}`;
    }
    
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error('Erreur réseau: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            typeSelect.disabled = false;
            typeSelect.innerHTML = '<option value="">Sélectionner un type (optionnel)</option>';
            
            if (data.success && data.types && data.types.length > 0) {
                data.types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.id;
                    option.textContent = type.name;
                    typeSelect.appendChild(option);
                });
                typeGroup.style.display = 'block';
            } else {
                // Afficher le groupe même s'il n'y a pas de types, pour montrer qu'il n'y a pas de types disponibles
                const noTypeOption = document.createElement('option');
                noTypeOption.value = '';
                noTypeOption.textContent = 'Aucun type disponible';
                noTypeOption.disabled = true;
                typeSelect.appendChild(noTypeOption);
                typeGroup.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Erreur lors du chargement des types:', error);
            typeSelect.disabled = false;
            typeSelect.innerHTML = '<option value="">Erreur de chargement</option>';
            typeGroup.style.display = 'block';
        });
}

// Gérer le changement de type (En stock / Sur mesure)
function onTypeChange() {
    console.log('🔄 Changement de type détecté');
    const categorySelect = document.getElementById('category_id');
    const categoryId = categorySelect ? categorySelect.value : '';
    const typeSelect = document.getElementById('type_id');
    const dimensionsGroup = document.getElementById('dimensions-group');
    const priceLabel = document.getElementById('price-label');
    const priceHint = document.getElementById('price-hint');
    
    // Réinitialiser le filtre de type de catégorie
    const typeCategorySelect = document.getElementById('type_category_id');
    if (typeCategorySelect) {
        typeCategorySelect.value = '';
    }
    
    // Vérifier si "Sur mesure" est sélectionné pour afficher les champs de dimensions
    if (typeSelect && dimensionsGroup) {
        const selectedTypeId = typeSelect.value;
        const selectedTypeText = typeSelect.options[typeSelect.selectedIndex] ? typeSelect.options[typeSelect.selectedIndex].textContent.trim() : '';
        
        // Afficher les dimensions si "Sur mesure" est sélectionné
        if (selectedTypeText === 'Sur mesure' || selectedTypeText.toLowerCase().includes('sur mesure')) {
            dimensionsGroup.style.display = 'block';
            // Changer le label du prix
            if (priceLabel) {
                priceLabel.textContent = 'Prix (DH) / m² *';
            }
            if (priceHint) {
                priceHint.style.display = 'block';
            }
            console.log('📏 Affichage des champs de dimensions pour produit sur mesure');
        } else {
            dimensionsGroup.style.display = 'none';
            // Réinitialiser les valeurs
            const maxLengthInput = document.getElementById('max_length');
            const maxWidthInput = document.getElementById('max_width');
            if (maxLengthInput) maxLengthInput.value = '';
            if (maxWidthInput) maxWidthInput.value = '';
            // Remettre le label du prix normal
            if (priceLabel) {
                priceLabel.textContent = 'Prix (DH) *';
            }
            if (priceHint) {
                priceHint.style.display = 'none';
            }
            console.log('📏 Masquage des champs de dimensions');
        }
    }
    
    // Recharger les types de catégorie en fonction du type sélectionné
    if (categoryId && categoryId !== '' && categoryId !== '0') {
        console.log('📋 Rechargement des types de catégorie pour la catégorie:', categoryId);
        loadTypesByCategory(categoryId);
    } else {
        console.log('⚠️ Aucune catégorie sélectionnée, impossible de charger les types');
        const typeGroup = document.getElementById('type-category-group');
        if (typeGroup) {
            typeGroup.style.display = 'none';
        }
    }
}

// Écouter les changements de catégorie et de type
document.addEventListener('DOMContentLoaded', function() {
    console.log('📄 Initialisation du formulaire d\'ajout');
    
    const categorySelect = document.getElementById('category_id');
    const typeSelect = document.getElementById('type_id');
    
    // Fonction pour initialiser les event listeners
    function initEventListeners() {
        if (categorySelect) {
            // Supprimer les anciens listeners pour éviter les doublons
            const newCategorySelect = categorySelect.cloneNode(true);
            categorySelect.parentNode.replaceChild(newCategorySelect, categorySelect);
            
            newCategorySelect.addEventListener('change', function() {
                console.log('📁 Changement de catégorie:', this.value);
                loadTypesByCategory(this.value);
            });
            
            // Charger les types si une catégorie est déjà sélectionnée
            if (newCategorySelect.value) {
                loadTypesByCategory(newCategorySelect.value);
            }
        }
        
        if (typeSelect) {
            // Supprimer les anciens listeners pour éviter les doublons
            const newTypeSelect = typeSelect.cloneNode(true);
            typeSelect.parentNode.replaceChild(newTypeSelect, typeSelect);
            
            newTypeSelect.addEventListener('change', function() {
                console.log('🏷️ Changement de type:', this.value);
                onTypeChange();
            });
            
            // Vérifier au chargement si "Sur mesure" est déjà sélectionné
            if (newTypeSelect.value) {
                setTimeout(() => onTypeChange(), 100);
            }
        }
    }
    
    // Initialiser les listeners
    initEventListeners();
    
    // Réinitialiser les listeners quand le formulaire est affiché
    const formContainer = document.getElementById('add-form-container');
    if (formContainer) {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    const isVisible = formContainer.style.display !== 'none' && formContainer.style.display !== '';
                    if (isVisible) {
                        console.log('👁️ Formulaire affiché, réinitialisation des listeners');
                        setTimeout(initEventListeners, 100);
                    }
                }
            });
        });
        
        observer.observe(formContainer, {
            attributes: true,
            attributeFilter: ['style']
        });
    }
    
    // Afficher le formulaire si on vient de soumettre avec une erreur
    <?php if ($error): ?>
    toggleAddForm();
    // Réinitialiser les listeners après l'affichage du formulaire
    setTimeout(initEventListeners, 200);
    <?php endif; ?>
    
    // Si une catégorie et un type sont déjà sélectionnés (après erreur de soumission)
    <?php if (isset($_POST['category_id']) && isset($_POST['type_id'])): ?>
    setTimeout(function() {
        const catSelect = document.getElementById('category_id');
        const typeSelect = document.getElementById('type_id');
        if (catSelect && catSelect.value) {
            loadTypesByCategory(catSelect.value);
        }
    }, 300);
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
