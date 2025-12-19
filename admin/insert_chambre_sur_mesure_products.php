<?php
// Script pour insérer les produits de chambre sur mesure
// Accédez à ce fichier via: http://localhost/MeublesMaison/admin/insert_chambre_sur_mesure_products.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Depuis le dossier admin/, la connexion DB est à ../db.php
require_once __DIR__ . '/../db.php';

$pageTitle = "Insertion des produits Chambre - Sur mesure";
require_once 'includes/header.php';

$results = [];
$errors = [];
$success = false;

// Charger les produits depuis le fichier séparé
$products = require __DIR__ . '/insert_chambre_sur_mesure.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    try {
        $pdo->beginTransaction();
        
        $insertedCount = 0;
        $skippedCount = 0;
        
        foreach ($products as $productData) {
            list($name, $description, $price, $categoryName, $typeName, $stock, $image) = $productData;
            
            // Vérifier si le produit existe déjà
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE name = ?");
            $checkStmt->execute([$name]);
            
            if ($checkStmt->fetch()) {
                $skippedCount++;
                $results[] = "⏭️ Déjà existant: $name";
                continue;
            }
            
            // Récupérer l'ID de la catégorie
            $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $catStmt->execute([$categoryName]);
            $category = $catStmt->fetch();
            
            if (!$category) {
                $errors[] = "Catégorie non trouvée: $categoryName pour $name";
                continue;
            }
            
            $categoryId = $category['id'];
            
            // Récupérer l'ID du type de catégorie
            $typeStmt = $pdo->prepare("SELECT id FROM types_categories WHERE name = ? AND category_id = ?");
            $typeStmt->execute([$typeName, $categoryId]);
            $type = $typeStmt->fetch();
            
            if (!$type) {
                $errors[] = "Type non trouvé: $typeName pour $name. Veuillez d'abord créer ce type dans types_categories.php";
                continue;
            }
            
            $typeCategoryId = $type['id'];
            
            // Insérer le produit
            $insertStmt = $pdo->prepare("
                INSERT INTO products (name, description, price, image, category, category_id, type_category_id, stock) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $insertStmt->execute([
                $name,
                $description,
                $price,
                $image,
                $categoryName,
                $categoryId,
                $typeCategoryId,
                $stock
            ]);
            
            $insertedCount++;
            $results[] = "✅ Inséré: $name ($categoryName - $typeName)";
        }
        
        $pdo->commit();
        $success = true;
        $results[] = "<strong>✅ Total: $insertedCount nouveaux produits insérés avec succès!</strong>";
        if ($skippedCount > 0) {
            $results[] = "<strong>⏭️ $skippedCount produits déjà existants (ignorés).</strong>";
        }
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors[] = "❌ Erreur: " . $e->getMessage();
    }
}

// Vérifier si les types de catégories nécessaires existent
$requiredTypes = [
    'couvre lit',
    'décoré de lit',
    'housses de matelas',
    'les couvertures'
];

$missingTypes = [];
try {
    $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = 'Chambre'");
    $catStmt->execute();
    $chambreCategory = $catStmt->fetch();
    
    if ($chambreCategory) {
        $chambreCategoryId = $chambreCategory['id'];
        foreach ($requiredTypes as $typeName) {
            $typeStmt = $pdo->prepare("SELECT id FROM types_categories WHERE name = ? AND category_id = ?");
            $typeStmt->execute([$typeName, $chambreCategoryId]);
            if (!$typeStmt->fetch()) {
                $missingTypes[] = $typeName;
            }
        }
    } else {
        $errors[] = "⚠️ La catégorie 'Chambre' n'existe pas. Veuillez d'abord la créer.";
    }
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la vérification: " . $e->getMessage();
}

// Récupérer les statistiques actuelles pour les produits de chambre sur mesure
$stats = [];
try {
    $stmt = $pdo->query("
        SELECT 
            tc.name as type_name,
            COUNT(p.id) as product_count,
            SUM(p.stock) as total_stock
        FROM types_categories tc
        LEFT JOIN products p ON tc.id = p.type_category_id
        INNER JOIN categories c ON tc.category_id = c.id
        WHERE c.name = 'Chambre' 
        AND tc.name IN ('couvre lit', 'décoré de lit', 'housses de matelas', 'les couvertures')
        GROUP BY tc.id, tc.name
        ORDER BY tc.name
    ");
    $stats = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ignorer l'erreur si les types n'existent pas encore
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Insertion des produits Chambre - Sur mesure</h1>
    </div>

    <?php if ($success): ?>
        <div class="success-message">
            ✅ Insertion terminée avec succès!
        </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background: #e74c3c; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($missingTypes)): ?>
        <div style="background: #f39c12; color: white; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
            <h3 style="margin-top: 0;">⚠️ Types de catégories manquants</h3>
            <p>Les types suivants doivent être créés avant d'insérer les produits :</p>
            <ul>
                <?php foreach ($missingTypes as $type): ?>
                    <li><strong><?php echo htmlspecialchars($type); ?></strong></li>
                <?php endforeach; ?>
            </ul>
            <p style="margin-top: 1rem;">
                <a href="types_categories.php" class="btn" style="background: white; color: #f39c12; text-decoration: none; display: inline-block;">
                    ➕ Créer les types manquants
                </a>
            </p>
        </div>
    <?php endif; ?>

    <!-- Statistiques actuelles -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📊 Statistiques actuelles</h2>
        <?php if (empty($stats)): ?>
            <p style="color: var(--text-light);">Aucun produit trouvé pour les types de catégories sur mesure.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Type de catégorie</th>
                            <th>Produits</th>
                            <th>Stock total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($stat['type_name']); ?></strong></td>
                                <td><?php echo $stat['product_count']; ?></td>
                                <td><?php echo $stat['total_stock']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Liste des produits à insérer -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📋 Produits à insérer (<?php echo count($products); ?> produits)</h2>
        <p style="margin-bottom: 1rem; color: var(--text-light);">
            Les produits seront associés automatiquement à la catégorie <strong>Chambre</strong> et leurs types de catégories respectifs.
        </p>
        <div style="max-height: 500px; overflow-y: auto; background: white; padding: 1rem; border-radius: 8px;">
            <?php
            $currentType = '';
            foreach ($products as $productData):
                list($name, $description, $price, $categoryName, $typeName, $stock) = $productData;
                if ($currentType !== $typeName):
                    if ($currentType !== '') echo '</div>';
                    $currentType = $typeName;
                    echo '<div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border-light);">';
                    echo '<h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">🛏️ ' . htmlspecialchars($typeName) . '</h3>';
                endif;
            ?>
                <div style="padding: 0.5rem; background: var(--bg-light); margin-bottom: 0.5rem; border-radius: 5px;">
                    <strong><?php echo htmlspecialchars($name); ?></strong> - 
                    <span style="color: var(--text-light);"><?php echo htmlspecialchars($description); ?></span> - 
                    <span style="color: var(--primary-color); font-weight: bold;"><?php echo number_format($price, 2, ',', ' '); ?> DH</span>
                    <span style="color: var(--text-light); font-size: 0.9rem;"> (Stock: <?php echo $stock; ?>)</span>
                </div>
            <?php endforeach; ?>
            <?php if ($currentType !== '') echo '</div>'; ?>
        </div>
    </div>

    <!-- Formulaire d'insertion -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">🚀 Insérer tous les produits</h2>
        <p style="margin-bottom: 1rem; color: var(--text-light);">
            Cliquez sur le bouton ci-dessous pour insérer tous les produits de chambre sur mesure. 
            Les produits déjà existants seront ignorés (pas de doublons).
        </p>
        <?php if (!empty($missingTypes)): ?>
            <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                <p style="margin: 0; color: #856404;">
                    ⚠️ <strong>Attention:</strong> Certains types de catégories sont manquants. Veuillez les créer avant d'insérer les produits.
                </p>
            </div>
        <?php endif; ?>
        <form method="POST" action="insert_chambre_sur_mesure_products.php">
            <input type="hidden" name="action" value="insert">
            <button type="submit" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;" <?php echo !empty($missingTypes) ? 'disabled' : ''; ?>>
                ✅ Insérer tous les produits (<?php echo count($products); ?>)
            </button>
        </form>
    </div>

    <!-- Résultats -->
    <?php if (!empty($results)): ?>
        <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
            <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📝 Résultats</h2>
            <div style="background: white; padding: 1rem; border-radius: 8px; max-height: 400px; overflow-y: auto;">
                <?php foreach ($results as $result): ?>
                    <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border-light);">
                        <?php echo $result; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Liens -->
    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 2rem; flex-wrap: wrap;">
        <a href="types_categories.php" class="btn" style="background: var(--secondary-color); text-decoration: none;">
            🏷️ Gérer les types de catégories
        </a>
        <a href="insert_products_by_types.php" class="btn" style="background: var(--text-light); text-decoration: none;">
            📦 Autres produits
        </a>
        <a href="add.php" class="btn" style="background: var(--text-light); text-decoration: none;">
            ← Retour aux produits
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

