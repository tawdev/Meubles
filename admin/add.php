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
    $category = trim($_POST['category'] ?? '');
    $stock = $_POST['stock'] ?? 0;
    
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
    
    if (empty($name) || empty($price) || empty($category)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Le prix doit être un nombre positif.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $description, $price, $image ?: 'images/placeholder.jpg', $category, $stock]);
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

// Récupérer tous les produits
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
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
                    <label for="price">Prix (€) *</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" required
                           value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="category">Catégorie *</label>
                    <select id="category" name="category" required>
                        <option value="">Sélectionner une catégorie</option>
                        <?php foreach ($categoriesList as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                    <?php echo (isset($_POST['category']) && $_POST['category'] === $cat['name']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
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
    <h2 style="margin-bottom: 1rem;">Liste des produits</h2>
    <div style="overflow-x: auto;">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Nom</th>
                    <th>Catégorie</th>
                    <th>Prix</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">
                            Aucun produit trouvé. Cliquez sur "Ajouter un produit" pour en créer un.
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
                        <td><?php echo htmlspecialchars($product['category']); ?></td>
                        <td><?php echo number_format($product['price'], 2, ',', ' '); ?> €</td>
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

// Afficher le formulaire si on vient de soumettre avec une erreur
<?php if ($error): ?>
document.addEventListener('DOMContentLoaded', function() {
    toggleAddForm();
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
