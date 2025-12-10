<?php
/**
 * Script pour insérer 35 produits dans la base de données
 * 5 produits pour chaque catégorie: Bureau, Chambre, Cuisine, Décoration, placard, Salon, Salle à manger
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Insertion des Produits</title>";
echo "<style>
    body {
        font-family: Arial, sans-serif;
        max-width: 1000px;
        margin: 50px auto;
        padding: 20px;
        background: #f5f5f5;
    }
    .container {
        background: white;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .success {
        background: #27ae60;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .error {
        background: #e74c3c;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .info {
        background: #3498db;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    .warning {
        background: #f39c12;
        color: white;
        padding: 15px;
        border-radius: 5px;
        margin: 10px 0;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
    }
    th, td {
        padding: 10px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    th {
        background: #6b4e3d;
        color: white;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #6b4e3d;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px 5px;
    }
    .btn:hover {
        background: #8b6e5d;
    }
</style></head><body>";

echo "<div class='container'>";
echo "<h1>🛋️ Insertion de 35 Produits</h1>";

try {
    // 1. S'assurer que toutes les catégories existent
    echo "<div class='info'><h3>1. Vérification des catégories</h3>";
    
    $categories = [
        ['Bureau', 'Meubles de bureau : bureaux, chaises, étagères', '🖥️'],
        ['Chambre', 'Meubles pour la chambre : lits, armoires, commodes', '🛏️'],
        ['Cuisine', 'Meubles pour la cuisine : tables, chaises, buffets, îlots', '🍳'],
        ['Décoration', 'Éléments de décoration : étagères, miroirs, accessoires', '🖼️'],
        ['placard', 'Meubles de rangement : placards, dressings, penderies', '🚪'],
        ['Salon', 'Meubles pour le salon : canapés, tables basses, fauteuils', '🛋️'],
        ['Salle à manger', 'Meubles pour la salle à manger : tables, chaises, buffets', '🍽️']
    ];
    
    $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=name");
    foreach ($categories as $cat) {
        $stmt->execute($cat);
    }
    echo "✅ Toutes les catégories sont prêtes.<br>";
    echo "</div>";
    
    // 2. Définir les produits
    $products = [
        // BUREAU (5 produits)
        ['Bureau en bois massif', 'Bureau moderne en bois massif avec tiroirs intégrés. Idéal pour le télétravail et le travail à domicile. Dimensions: 120x60x75 cm.', 549.99, 'images/placeholder.jpg', 'Bureau', 12],
        ['Chaise de bureau ergonomique', 'Chaise de bureau ergonomique avec support lombaire réglable. Accoudoirs inclinables et hauteur réglable. Tissu respirant.', 349.99, 'images/placeholder.jpg', 'Bureau', 15],
        ['Étagère murale bureau', 'Étagère murale design en métal et bois pour organiser votre espace de travail. 3 niveaux de rangement.', 179.99, 'images/placeholder.jpg', 'Bureau', 20],
        ['Bureau d\'angle moderne', 'Bureau d\'angle compact avec étagères intégrées. Parfait pour optimiser l\'espace dans les petits bureaux.', 429.99, 'images/placeholder.jpg', 'Bureau', 8],
        ['Lampe de bureau LED', 'Lampe de bureau LED moderne avec bras articulé et variateur d\'intensité. Éclairage chaud et froid réglable.', 89.99, 'images/placeholder.jpg', 'Bureau', 25],
        
        // CHAMBRE (5 produits)
        ['Lit double avec tête de lit', 'Lit double 160x200 cm avec tête de lit rembourrée en tissu. Style contemporain et élégant. Sommier inclus.', 699.99, 'images/placeholder.jpg', 'Chambre', 10],
        ['Armoire 3 portes avec miroir', 'Armoire 3 portes avec miroir intégré. Grande capacité de rangement. Finition blanche laquée. Dimensions: 180x60x220 cm.', 1199.99, 'images/placeholder.jpg', 'Chambre', 6],
        ['Commode 4 tiroirs scandinave', 'Commode 4 tiroirs en bois massif. Style scandinave avec poignées en métal. Parfait pour le rangement du linge.', 599.99, 'images/placeholder.jpg', 'Chambre', 14],
        ['Table de chevet design', 'Table de chevet moderne avec tiroir et tablette. Design épuré en bois et métal. Dimensions: 45x35x55 cm.', 149.99, 'images/placeholder.jpg', 'Chambre', 18],
        ['Coffre à linge en rotin', 'Coffre à linge élégant en rotin naturel. Capacité 100L. Parfait pour ranger couettes et couvertures.', 229.99, 'images/placeholder.jpg', 'Chambre', 12],
        
        // CUISINE (5 produits)
        ['Table de cuisine extensible', 'Table de cuisine extensible en bois massif. Passe de 4 à 6 personnes. Finition huilée naturelle.', 799.99, 'images/placeholder.jpg', 'Cuisine', 9],
        ['Chaises de cuisine design', 'Lot de 4 chaises de cuisine en bois et métal. Design moderne et confortable. Assise rembourrée.', 399.99, 'images/placeholder.jpg', 'Cuisine', 16],
        ['Buffet de cuisine 2 portes', 'Buffet de cuisine avec 2 portes et 2 tiroirs. Espace de rangement généreux. Finition chêne clair.', 899.99, 'images/placeholder.jpg', 'Cuisine', 7],
        ['Îlot de cuisine central', 'Îlot de cuisine central avec plan de travail en granit. 2 tiroirs et étagères ouvertes. Dimensions: 120x80x90 cm.', 1299.99, 'images/placeholder.jpg', 'Cuisine', 5],
        ['Tabouret de bar réglable', 'Tabouret de bar réglable en hauteur. Assise pivotante et dossier confortable. Lot de 2.', 249.99, 'images/placeholder.jpg', 'Cuisine', 20],
        
        // DÉCORATION (5 produits)
        ['Étagère murale design', 'Étagère murale moderne en métal et bois. Parfaite pour décorer et ranger. 3 niveaux.', 149.99, 'images/placeholder.jpg', 'Décoration', 22],
        ['Miroir décoratif ovale', 'Miroir décoratif ovale avec cadre en bois doré. Dimensions: 80x60 cm. Parfait pour l\'entrée ou le salon.', 199.99, 'images/placeholder.jpg', 'Décoration', 15],
        ['Vase en céramique moderne', 'Vase en céramique moderne de grande taille. Design épuré et élégant. Hauteur: 45 cm.', 79.99, 'images/placeholder.jpg', 'Décoration', 30],
        ['Table basse design', 'Table basse moderne avec plateau en verre trempé et structure en métal. Dimensions: 120x60x40 cm.', 449.99, 'images/placeholder.jpg', 'Décoration', 11],
        ['Panneau décoratif en bois', 'Panneau décoratif en bois massif avec motifs géométriques. Dimensions: 100x50 cm. Style scandinave.', 179.99, 'images/placeholder.jpg', 'Décoration', 18],
        
        // PLACARD (5 produits)
        ['Placard 2 portes coulissantes', 'Placard 2 portes coulissantes avec miroir. Grande capacité de rangement. Dimensions: 200x60x240 cm.', 899.99, 'images/placeholder.jpg', 'placard', 8],
        ['Dressing 3 portes', 'Dressing 3 portes avec étagères et penderie. Organisation optimale pour vêtements et accessoires.', 1099.99, 'images/placeholder.jpg', 'placard', 6],
        ['Penderie métallique', 'Penderie métallique démontable avec étagères. Idéale pour rangement temporaire ou complémentaire.', 129.99, 'images/placeholder.jpg', 'placard', 25],
        ['Placard bas 4 portes', 'Placard bas 4 portes pour rangement optimisé. Parfait pour chambre ou entrée. Finition blanche.', 549.99, 'images/placeholder.jpg', 'placard', 12],
        ['Armoire de rangement', 'Armoire de rangement avec 2 portes et étagères réglables. Grande capacité. Dimensions: 100x40x200 cm.', 699.99, 'images/placeholder.jpg', 'placard', 10],
        
        // SALON (5 produits)
        ['Canapé 3 places moderne', 'Canapé 3 places en tissu gris, confortable et élégant. Parfait pour votre salon moderne. Dimensions: 220x90x85 cm.', 899.99, 'images/placeholder.jpg', 'Salon', 7],
        ['Fauteuil relaxant', 'Fauteuil relaxant avec repose-pieds intégré. Tissu résistant et rembourrage généreux. Style contemporain.', 599.99, 'images/placeholder.jpg', 'Salon', 9],
        ['Table basse en bois', 'Table basse rectangulaire en bois massif. Design épuré avec tiroir de rangement. Dimensions: 140x70x45 cm.', 449.99, 'images/placeholder.jpg', 'Salon', 13],
        ['Canapé d\'angle', 'Canapé d\'angle confortable avec coussins déhoussables. Tissu beige élégant. Dimensions: 280x280x85 cm.', 1499.99, 'images/placeholder.jpg', 'Salon', 5],
        ['Pouf design', 'Pouf design en cuir synthétique. Multifonctionnel : siège, repose-pieds ou table basse. Diamètre: 50 cm.', 179.99, 'images/placeholder.jpg', 'Salon', 20],
        
        // SALLE À MANGER (5 produits)
        ['Table à manger rectangulaire', 'Table à manger rectangulaire en chêne massif, 6 places. Design classique et intemporel. Dimensions: 200x100x75 cm.', 1299.99, 'images/placeholder.jpg', 'Salle à manger', 8],
        ['Chaises de salle à manger', 'Lot de 6 chaises de salle à manger en bois massif. Dossier haut et assise rembourrée. Style classique.', 599.99, 'images/placeholder.jpg', 'Salle à manger', 10],
        ['Buffet de salle à manger', 'Buffet de salle à manger 3 portes avec tiroirs. Espace de rangement généreux pour vaisselle et linge.', 999.99, 'images/placeholder.jpg', 'Salle à manger', 6],
        ['Table à manger ronde', 'Table à manger ronde extensible. Passe de 4 à 6 personnes. Bois massif avec finition naturelle. Diamètre: 140 cm.', 1099.99, 'images/placeholder.jpg', 'Salle à manger', 7],
        ['Vaisselier vitré', 'Vaisselier vitré avec 2 portes et étagères. Parfait pour exposer et ranger votre vaisselle. Dimensions: 120x40x200 cm.', 799.99, 'images/placeholder.jpg', 'Salle à manger', 9]
    ];
    
    // 3. Vérifier si des produits existent déjà
    echo "<div class='info'><h3>2. Vérification des produits existants</h3>";
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM products");
    $count = $countStmt->fetch()['count'];
    echo "Nombre de produits actuels : <strong>$count</strong><br>";
    echo "</div>";
    
    // 4. Insérer les produits
    echo "<div class='info'><h3>3. Insertion des produits</h3>";
    $stmt = $pdo->prepare("INSERT INTO products (name, description, price, image, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
    
    $inserted = 0;
    $skipped = 0;
    $errors = [];
    
    foreach ($products as $product) {
        try {
            // Vérifier si le produit existe déjà
            $checkStmt = $pdo->prepare("SELECT id FROM products WHERE name = ? AND category = ?");
            $checkStmt->execute([$product[0], $product[4]]);
            
            if ($checkStmt->fetch()) {
                $skipped++;
                continue;
            }
            
            $stmt->execute($product);
            $inserted++;
        } catch (PDOException $e) {
            $errors[] = "Erreur pour '{$product[0]}': " . $e->getMessage();
        }
    }
    
    echo "✅ <strong>$inserted</strong> produits insérés avec succès.<br>";
    if ($skipped > 0) {
        echo "⚠️ <strong>$skipped</strong> produits déjà existants (ignorés).<br>";
    }
    if (count($errors) > 0) {
        echo "<div class='error'>";
        echo "<strong>Erreurs rencontrées :</strong><br>";
        foreach ($errors as $error) {
            echo "• $error<br>";
        }
        echo "</div>";
    }
    echo "</div>";
    
    // 5. Afficher le résumé par catégorie
    echo "<div class='success'><h3>4. Résumé par catégorie</h3>";
    $summaryStmt = $pdo->query("
        SELECT category, COUNT(*) as count 
        FROM products 
        GROUP BY category 
        ORDER BY category
    ");
    
    echo "<table>";
    echo "<tr><th>Catégorie</th><th>Nombre de produits</th></tr>";
    $total = 0;
    while ($row = $summaryStmt->fetch()) {
        echo "<tr><td>{$row['category']}</td><td><strong>{$row['count']}</strong></td></tr>";
        $total += $row['count'];
    }
    echo "<tr><td><strong>Total</strong></td><td><strong>$total</strong></td></tr>";
    echo "</table>";
    echo "</div>";
    
    echo "<div class='success' style='margin-top:20px;'>";
    echo "<h2>✅ Insertion terminée avec succès !</h2>";
    echo "<p>Les produits ont été ajoutés à la base de données.</p>";
    echo "<a href='add.php' class='btn'>Gérer les produits</a>";
    echo "<a href='dashboard.php' class='btn'>Retour au tableau de bord</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ Erreur</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "</div>";
echo "</body></html>";

