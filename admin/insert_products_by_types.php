<?php
// Script pour insérer des produits pour chaque type de catégorie
// Accédez à ce fichier via: http://localhost/MeublesMaison/admin/insert_products_by_types.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../../db.php';

$pageTitle = "Insertion des produits par types";
require_once 'includes/header.php';

$results = [];
$errors = [];
$success = false;

// Définir tous les produits par catégorie et type
$products = [
    // BUREAU - Chaise de bureau
    ['Chaise de bureau ergonomique noire', 'Chaise de bureau ergonomique noire – Confort optimal avec dossier réglable', 129.00, 'Bureau', 'Chaise de bureau', 10, 'images/Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg'],
    ['Chaise de bureau en tissu gris', 'Chaise de bureau en tissu gris – Design moderne et assise confortable', 99.00, 'Bureau', 'Chaise de bureau', 12, 'images/1765286166_Chaise de bureau ergonomique Fauteuil pivotant Hauteur réglable, accoudoirs inclinables pour bureau - Marron 65x68x94_104 cm.jpeg'],
    
    // BUREAU - Bureau droit
    ['Bureau droit en bois chêne', 'Bureau droit en bois chêne – Surface large et solide', 189.00, 'Bureau', 'Bureau droit', 8, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    ['Bureau droit moderne blanc', 'Bureau droit moderne blanc – Minimaliste et élégant', 159.00, 'Bureau', 'Bureau droit', 9, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    
    // BUREAU - Bureau d'angle
    ['Bureau d\'angle professionnel', 'Bureau d\'angle professionnel – Gain d\'espace et grande surface', 249.00, 'Bureau', 'Bureau d\'angle', 6, 'images/1765373147_Bureau d\'angle en mélamine coloris imitation chêne….jpeg'],
    ['Bureau d\'angle compact', 'Bureau d\'angle compact – Idéal pour petits espaces', 199.00, 'Bureau', 'Bureau d\'angle', 7, 'images/1765373147_Bureau d\'angle en mélamine coloris imitation chêne….jpeg'],
    
    // BUREAU - Caisson de rangement
    ['Caisson de bureau 3 tiroirs', 'Caisson de bureau 3 tiroirs – Pratique et mobile', 89.00, 'Bureau', 'Caisson de rangement', 15, 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],

    // BUREAU - Bureau sur mesure
    ['Bureau sur mesure premium', 'Bureau sur mesure premium – Finition haut de gamme adaptée à votre espace', 899.00, 'Bureau', 'Bureau sur mesure', 3, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    ['Bureau sur mesure compact', 'Bureau sur mesure compact – Optimisé pour les petits espaces', 749.00, 'Bureau', 'Bureau sur mesure', 4, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],

    // BUREAU - Bureau moderne
    ['Bureau moderne bois et métal', 'Bureau moderne bois et métal – Design industriel tendance', 329.00, 'Bureau', 'Bureau moderne', 6, 'images/1765372581_Améliorez votre espace de travail avec ce bureau….jpeg'],
    ['Bureau moderne blanc laqué', 'Bureau moderne blanc laqué – Minimal et lumineux', 299.00, 'Bureau', 'Bureau moderne', 5, 'images/1765372616_Add a bold pop of color and modern style to your….jpeg'],

    // BUREAU - Bureau classique
    ['Bureau classique en chêne', 'Bureau classique en chêne – Style traditionnel intemporel', 399.00, 'Bureau', 'Bureau classique', 4, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    ['Bureau classique avec tiroirs', 'Bureau classique avec tiroirs – Idéal pour un bureau à domicile', 429.00, 'Bureau', 'Bureau classique', 5, 'images/1765286150_study table ideas study motivation study aesthetic study study tips study note study table study har.jpeg'],
    
    // CHAMBRE - Lit double
    ['Lit double en bois massif', 'Lit double en bois massif – Robuste et élégant', 399.00, 'Chambre', 'Lit double', 5, 'images/Lit double MARIUS 160x200 tissu beige sommier inclus.jpeg'],
    ['Lit double avec rangement', 'Lit double avec rangement – Tiroirs intégrés', 459.00, 'Chambre', 'Lit double', 4, 'images/Lit double MARIUS 160x200 tissu beige sommier inclus.jpeg'],
    
    // CHAMBRE - Armoire
    ['Armoire 3 portes blanche', 'Armoire 3 portes blanche – Grande capacité de rangement', 499.00, 'Chambre', 'Armoire', 3, 'images/Willa Arlo™ Interiors Armoire 3 portes 70 _Monchat.jpeg'],
    ['Armoire coulissante miroir', 'Armoire coulissante miroir – Moderne et fonctionnelle', 549.00, 'Chambre', 'Armoire', 2, 'images/1765373186_L\'armoire Max 2 à portes coulissantes est une….jpeg'],
    
    // CHAMBRE - Commode
    ['Commode 4 tiroirs en bois', 'Commode 4 tiroirs en bois – Style naturel', 229.00, 'Chambre', 'Commode', 8, 'images/Commode 4 Tiroirs Pricy.jpeg'],
    
    // CHAMBRE - Table de chevet
    ['Table de chevet moderne', 'Table de chevet moderne – 2 tiroirs', 89.00, 'Chambre', 'Table de chevet', 12, 'images/1765373611_- 2-drawers nightstand in your bedroom due in part….jpeg'],
    
    // CUISINE - Meuble bas de cuisine
    ['Meuble bas 2 portes blanc', 'Meuble bas 2 portes blanc – Résistant à l\'humidité', 149.00, 'Cuisine', 'Meuble bas de cuisine', 10, 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    
    // CUISINE - Meuble haut de cuisine
    ['Meuble haut mural', 'Meuble haut mural – Gain de place', 129.00, 'Cuisine', 'Meuble haut de cuisine', 11, 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    
    // CUISINE - Table de cuisine
    ['Table de cuisine compacte', 'Table de cuisine compacte – Idéale pour 4 personnes', 199.00, 'Cuisine', 'Table de cuisine', 7, 'images/1765372682_Amari 4 Seater Round Dining Table _ Dunelm.jpeg'],
    
    // CUISINE - Chaise de cuisine
    ['Chaise de cuisine en plastique renforcé', 'Chaise de cuisine en plastique renforcé – Facile à nettoyer', 49.00, 'Cuisine', 'Chaise de cuisine', 20, 'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg'],
    
    // CUISINE - Îlot de cuisine
    ['Îlot de cuisine avec rangement', 'Îlot de cuisine avec rangement – Moderne et pratique', 349.00, 'Cuisine', 'Îlot de cuisine', 4, 'images/1765373455_✨ Cuisine blanche et bois moderne avec îlot _ 7….jpeg'],
    
    // DÉCORATION - Étagère murale
    ['Étagère murale en bois', 'Étagère murale en bois – Design naturel', 59.00, 'Décoration', 'Étagère murale', 15, 'images/Étagère blanche murale avec 8 cubes.jpeg'],
    
    // DÉCORATION - Miroir décoratif
    ['Miroir mural rond doré', 'Miroir mural rond doré – Style élégant', 89.00, 'Décoration', 'Miroir décoratif', 10, 'images/1765373339_✓ Design Ovale Sophistiqué _ Ajoute une touche de….jpeg'],
    
    // DÉCORATION - Cadre mural
    ['Cadre mural décoratif', 'Cadre mural décoratif – Style moderne', 39.00, 'Décoration', 'Cadre mural', 18, 'images/1765373220_Panneau mural à lattes larges en placage de chêne….jpeg'],
    
    // DÉCORATION - Lampe décorative
    ['Lampe décorative LED', 'Lampe décorative LED – Lumière chaude', 69.00, 'Décoration', 'Lampe décorative', 12, 'images/1765373370_Lampe de bureau à éclairage LED pour les yeux….jpeg'],
    
    // DÉCORATION - Accessoire décoratif
    ['Vase décoratif moderne', 'Vase décoratif moderne – Finition mate', 45.00, 'Décoration', 'Accessoire décoratif', 20, 'images/1765373274_Ensemble de 2 vases en céramique creux, vases….jpeg'],
    
    // PLACARD - Placard coulissant
    ['Placard coulissant miroir', 'Placard coulissant miroir – Gain de place', 599.00, 'Placard', 'Placard coulissant', 3, 'images/1765373422_The versatile design of our 3-door mirrored….jpeg'],
    
    // PLACARD - Dressing
    ['Dressing modulable', 'Dressing modulable – Adaptable à tous les espaces', 699.00, 'Placard', 'Dressing', 2, 'images/1765373092_[Grand espace] Cette penderie (43 x 182 x 182 cm)….jpeg'],
    
    // PLACARD - Meuble à chaussures
    ['Meuble à chaussures 3 niveaux', 'Meuble à chaussures 3 niveaux – Pratique et élégant', 119.00, 'Placard', 'Meuble à chaussures', 8, 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],
    
    // PLACARD - Étagère de rangement
    ['Étagère de rangement métallique', 'Étagère de rangement métallique – Solide', 79.00, 'Placard', 'Étagère de rangement', 12, 'images/1765373576_Rehaussez l\'organisation de votre maison avec ce….jpeg'],
    
    // SALLE À MANGER - Table à manger
    ['Table à manger en bois massif', 'Table à manger en bois massif – 6 personnes', 499.00, 'Salle à manger', 'Table à manger', 4, 'images/Table à manger design en bois d\'acacia et métal – Élégance brute & moderne.jpeg'],
    
    // SALLE À MANGER - Chaise salle à manger
    ['Chaise salle à manger rembourrée', 'Chaise salle à manger rembourrée – Confort premium', 129.00, 'Salle à manger', 'Chaise de salle à manger', 10, 'images/1765373515_Creative Retro Wood Dining Chair for Living Room….jpeg'],
    
    // SALLE À MANGER - Buffet
    ['Buffet moderne 3 portes', 'Buffet moderne 3 portes – Grand espace de rangement', 399.00, 'Salle à manger', 'Buffet', 5, 'images/1765372717_Buffet salle à manger blanc cachemire 1 niche….jpeg'],
    
    // SALLE À MANGER - Vaisselier
    ['Vaisselier vitré', 'Vaisselier vitré – Élégant', 549.00, 'Salle à manger', 'Vaisselier', 3, 'images/1765372658_Optez pour le vaisselier vitré H_ 200 cm décor….jpeg'],
    
    // SALON - Canapé
    ['Canapé 3 places en tissu', 'Canapé 3 places en tissu – Confort et style', 699.00, 'Salon', 'Canapé', 3, 'images/vidaXL 3-Sitzer-Sofa XT6927 Grau_Dunkelgrau Material_ Textil 198cm.jpeg'],
    
    // SALON - Canapé d'angle
    ['Canapé d\'angle moderne', 'Canapé d\'angle moderne – Idéal pour familles', 999.00, 'Salon', 'Canapé d\'angle', 2, 'images/1765372973_Un canapé 3 places convertible au design moderne….jpeg'],
    
    // SALON - Table basse
    ['Table basse en bois et métal', 'Table basse en bois et métal – Design industriel', 179.00, 'Salon', 'Table basse', 8, 'images/1765373245_Table Basse Travertin Bois Moderne Pour Salon….jpeg'],
    
    // SALON - Fauteuil
    ['Fauteuil confortable en velours', 'Fauteuil confortable en velours – Style chic', 299.00, 'Salon', 'Fauteuil', 6, 'images/1765372906_Vente en ligne Fauteuil Molto sable de la….jpeg'],
    
    // SALON - Meuble TV
    ['Meuble TV moderne', 'Meuble TV moderne – Avec rangement', 249.00, 'Salon', 'Meuble TV', 7, 'images/1765373480_Description Design Élégant Le buffet est conçu….jpeg'],
];

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
                $errors[] = "Type non trouvé: $typeName pour $name";
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

// Récupérer les statistiques actuelles
$stats = [];
try {
    $stmt = $pdo->query("
        SELECT 
            c.name as category_name,
            c.icon,
            tc.name as type_name,
            COUNT(p.id) as product_count,
            SUM(p.stock) as total_stock
        FROM categories c
        LEFT JOIN types_categories tc ON c.id = tc.category_id
        LEFT JOIN products p ON tc.id = p.type_category_id
        WHERE c.name IN ('Bureau', 'Chambre', 'Cuisine', 'Décoration', 'Placard', 'Salle à manger', 'Salon')
        GROUP BY c.id, c.name, c.icon, tc.id, tc.name
        HAVING COUNT(p.id) > 0
        ORDER BY c.name, tc.name
    ");
    $stats = $stmt->fetchAll();
} catch (PDOException $e) {
    $errors[] = "Erreur lors de la récupération des statistiques: " . $e->getMessage();
}
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Insertion des produits par types</h1>
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
        <?php if (empty($stats)): ?>
            <p style="color: var(--text-light);">Aucun produit trouvé pour les types de catégories.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Type</th>
                            <th>Produits</th>
                            <th>Stock total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($stats as $stat): ?>
                            <tr>
                                <td>
                                    <span style="font-size: 1.2rem;"><?php echo htmlspecialchars($stat['icon']); ?></span>
                                    <strong><?php echo htmlspecialchars($stat['category_name']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($stat['type_name']); ?></td>
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
            Les produits seront associés automatiquement à leur catégorie et type de catégorie.
        </p>
        <div style="max-height: 400px; overflow-y: auto; background: white; padding: 1rem; border-radius: 8px;">
            <?php
            $currentCategory = '';
            foreach ($products as $productData):
                list($name, $description, $price, $categoryName, $typeName, $stock) = $productData;
                if ($currentCategory !== $categoryName):
                    if ($currentCategory !== '') echo '</div>';
                    $currentCategory = $categoryName;
                    echo '<div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--border-light);">';
                    echo '<h3 style="color: var(--primary-color); margin-bottom: 0.5rem;">' . htmlspecialchars($categoryName) . ' - ' . htmlspecialchars($typeName) . '</h3>';
                endif;
            ?>
                <div style="padding: 0.5rem; background: var(--bg-light); margin-bottom: 0.5rem; border-radius: 5px;">
                    <strong><?php echo htmlspecialchars($name); ?></strong> - 
                    <span style="color: var(--text-light);"><?php echo htmlspecialchars($description); ?></span> - 
                    <span style="color: var(--primary-color); font-weight: bold;"><?php echo number_format($price, 2, ',', ' '); ?> €</span>
                </div>
            <?php endforeach; ?>
            <?php if ($currentCategory !== '') echo '</div>'; ?>
        </div>
    </div>

    <!-- Formulaire d'insertion -->
    <div style="background: var(--bg-light); padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; color: var(--primary-color);">🚀 Insérer tous les produits</h2>
        <p style="margin-bottom: 1rem; color: var(--text-light);">
            Cliquez sur le bouton ci-dessous pour insérer tous les produits. 
            Les produits déjà existants seront ignorés (pas de doublons).
        </p>
        <form method="POST" action="insert_products_by_types.php">
            <input type="hidden" name="action" value="insert">
            <button type="submit" class="btn" style="font-size: 1.1rem; padding: 1rem 2rem;">
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

    <!-- Lien retour -->
    <div style="text-align: center; margin-top: 2rem;">
        <a href="dashboard.php" class="btn" style="background: var(--text-light);">
            ← Retour au tableau de bord
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

