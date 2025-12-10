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
        ['name' => 'Salon', 'icon' => '🛋️'],
        ['name' => 'Chambre', 'icon' => '🛏️'],
        ['name' => 'Salle à manger', 'icon' => '🍽️'],
        ['name' => 'Bureau', 'icon' => '💼'],
        ['name' => 'Décoration', 'icon' => '🖼️']
    ];
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = $_POST['price'] ?? '';
    $category = trim($_POST['category'] ?? '');
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
    
    if (empty($name) || empty($price) || empty($category)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!is_numeric($price) || $price <= 0) {
        $error = 'Le prix doit être un nombre positif.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, category = ?, stock = ? WHERE id = ?");
            $stmt->execute([$name, $description, $price, $image, $category, $stock, $productId]);
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
                <label for="price">Prix (€) *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" required value="<?php echo $product['price']; ?>">
            </div>
            
            <div class="form-group">
                <label for="category">Catégorie *</label>
                <select id="category" name="category" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categoriesList as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['name']); ?>" 
                                <?php echo $product['category'] === $cat['name'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['icon'] ?? ''); ?> <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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

<?php require_once 'includes/footer.php'; ?>

