<?php
// Script pour insérer tous les types de catégories
// Accédez à ce fichier via: http://localhost/MeublesMaison/admin/insert_all_types.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';

$pageTitle = "Insertion des types de catégories";
require_once 'includes/header.php';

$results = [];
$errors = [];
$success = false;

// Définir tous les types par catégorie
$typesByCategory = [
    'Bureau' => [
        'Bureau droit',
        'Bureau d\'angle',
        'Chaise de bureau',
        'Fauteuil de bureau',
        'Étagère de bureau',
        'Caisson de rangement',
        'Bureau sur mesure',
        'Bureau moderne',
        'Bureau classique'
    ],
    'Chambre' => [
        'Lit simple',
        'Lit double',
        'Armoire',
        'Commode',
        'Table de chevet',
        'Coiffeuse',
        'Tête de lit'
    ],
    'Cuisine' => [
        'Meuble bas de cuisine',
        'Meuble haut de cuisine',
        'Table de cuisine',
        'Chaise de cuisine',
        'Îlot de cuisine'
    ],
    'Décoration' => [
        'Étagère murale',
        'Miroir décoratif',
        'Cadre mural',
        'Lampe décorative',
        'Accessoire décoratif'
    ],
    'Placard' => [
        'Placard encastré',
        'Placard coulissant',
        'Dressing',
        'Étagère de rangement',
        'Meuble à chaussures'
    ],
    'Salle à manger' => [
        'Table à manger',
        'Chaise de salle à manger',
        'Buffet',
        'Vaisselier',
        'Table extensible',
        'Banc de salle à manger'
    ],
    'Salon' => [
        'Canapé',
        'Canapé d\'angle',
        'Table basse',
        'Fauteuil',
        'Meuble TV',
        'Bibliothèque'
    ]
];

// Icônes pour chaque catégorie
$categoryIcons = [
    'Bureau' => '🗄️',
    'Chambre' => '🛏️',
    'Cuisine' => '🍳',
    'Décoration' => '🖼️',
    'Placard' => '🗄️',
    'Salle à manger' => '🍽️',
    'Salon' => '🛋️'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    try {
        $pdo->beginTransaction();
        
        // 1. S'assurer que toutes les catégories existent
        foreach ($typesByCategory as $categoryName => $types) {
            $icon = $categoryIcons[$categoryName] ?? '';
            $stmt = $pdo->prepare("
                INSERT INTO categories (name, description, icon) 
                VALUES (?, ?, ?) 
                ON DUPLICATE KEY UPDATE name=name
            ");
            $description = "Types de meubles pour " . strtolower($categoryName);
            $stmt->execute([$categoryName, $description, $icon]);
        }
        
        // 2. Insérer tous les types
        $insertedCount = 0;
        foreach ($typesByCategory as $categoryName => $types) {
            // Récupérer l'ID de la catégorie
            $catStmt = $pdo->prepare("SELECT id FROM categories WHERE name = ?");
            $catStmt->execute([$categoryName]);
            $category = $catStmt->fetch();
            
            if ($category) {
                $categoryId = $category['id'];
                
                foreach ($types as $typeName) {
                    // Vérifier si le type existe déjà
                    $checkStmt = $pdo->prepare("SELECT id FROM types_categories WHERE name = ? AND category_id = ?");
                    $checkStmt->execute([$typeName, $categoryId]);
                    
                    if (!$checkStmt->fetch()) {
                        // Insérer le type
                        $insertStmt = $pdo->prepare("INSERT INTO types_categories (name, category_id) VALUES (?, ?)");
                        $insertStmt->execute([$typeName, $categoryId]);
                        $insertedCount++;
                        $results[] = "✅ Inséré: $typeName dans $categoryName";
                    } else {
                        $results[] = "⏭️ Déjà existant: $typeName dans $categoryName";
                    }
                }
            }
        }
        
        $pdo->commit();
        $success = true;
        $results[] = "<strong>✅ Total: $insertedCount nouveaux types insérés avec succès!</strong>";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        $errors[] = "❌ Erreur: " . $e->getMessage();
    }
}

// Récupérer les statistiques actuelles
$stats = [];
try {
    $stmt = $pdo->query("
        SELECT 
            c.name as category_name,
            c.icon,
            COUNT(tc.id) as type_count
        FROM categories c
        LEFT JOIN types_categories tc ON c.id = tc.category_id
        WHERE c.name IN ('Bureau', 'Chambre', 'Cuisine', 'Décoration', 'Placard', 'Salle à manger', 'Salon')
        GROUP BY c.id, c.name, c.icon
        ORDER BY c.name
    ");
    $stats = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Insertion des types de catégories</h1>
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

    <!-- Statistiques actuelles -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📊 Statistiques actuelles</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
            <?php foreach ($stats as $stat): ?>
                <div style="background: white; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--secondary-color);">
                    <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">
                        <?php echo htmlspecialchars($stat['icon']); ?>
                        <strong><?php echo htmlspecialchars($stat['category_name']); ?></strong>
                    </div>
                    <div style="font-size: 1.2rem; color: var(--primary-color); font-weight: bold;">
                        <?php echo $stat['type_count']; ?> type(s)
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Liste des types à insérer -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">📋 Types à insérer</h2>
        <?php foreach ($typesByCategory as $categoryName => $types): ?>
            <div style="margin-bottom: 1.5rem; background: white; padding: 1rem; border-radius: 8px;">
                <h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                    <?php echo $categoryIcons[$categoryName] ?? ''; ?> <?php echo htmlspecialchars($categoryName); ?>
                    <span style="color: var(--text-light); font-size: 0.9rem;">(<?php echo count($types); ?> types)</span>
                </h3>
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                    <?php foreach ($types as $type): ?>
                        <span style="background: var(--secondary-color); color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($type); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Formulaire d'insertion -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">🚀 Insérer tous les types</h2>
        <p style="margin-bottom: 1rem; color: var(--text-light);">
            Cliquez sur le bouton ci-dessous pour insérer tous les types de catégories. 
            Les types déjà existants seront ignorés (pas de doublons).
        </p>
        <form method="POST" action="insert_all_types.php">
            <input type="hidden" name="action" value="insert">
            <button type="submit" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
                ✅ Insérer tous les types de catégories
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

    <!-- Lien retour -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="types_categories.php" class="btn" style="background: var(--text-light);">
            ← Retour à la gestion des types
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

