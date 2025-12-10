<?php
/**
 * Script pour créer la table categories et migrer les données existantes
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Installation Catégories</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{background:#27ae60;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#e74c3c;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#3498db;color:white;padding:15px;border-radius:5px;margin:10px 0;}</style></head><body>";

echo "<h1>🔧 Installation de la Gestion des Catégories</h1>";

try {
    // Créer la table categories
    echo "<div class='info'><h3>1. Création de la table 'categories'</h3>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table 'categories' créée ou déjà existante.<br>";
    echo "</div>";
    
    // Insérer les catégories par défaut
    echo "<div class='info'><h3>2. Insertion des catégories par défaut</h3>";
    $defaultCategories = [
        ['Salon', 'Meubles pour le salon : canapés, tables basses, fauteuils', '🛋️'],
        ['Chambre', 'Meubles pour la chambre : lits, armoires, commodes', '🛏️'],
        ['Salle à manger', 'Meubles pour la salle à manger : tables, chaises, buffets', '🍽️'],
        ['Bureau', 'Meubles de bureau : bureaux, chaises, étagères', '💼'],
        ['Décoration', 'Éléments de décoration : étagères, miroirs, accessoires', '🖼️']
    ];
    
    $insertStmt = $pdo->prepare("INSERT INTO categories (name, description, icon) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE name=name");
    $inserted = 0;
    foreach ($defaultCategories as $cat) {
        try {
            $insertStmt->execute($cat);
            $inserted++;
        } catch (PDOException $e) {
            // Catégorie déjà existante, ignorer
        }
    }
    echo "✅ $inserted catégorie(s) insérée(s).<br>";
    echo "</div>";
    
    // Vérifier les catégories existantes
    echo "<div class='success' style='margin-top:20px;'>";
    echo "<h2>✅ Installation terminée !</h2>";
    echo "<p>La gestion des catégories est maintenant disponible.</p>";
    echo "<a href='categories.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#2c3e50;color:white;text-decoration:none;border-radius:5px;'>📁 Aller à la gestion des catégories</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERREUR</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#e74c3c;'><strong>⚠️ SÉCURITÉ :</strong> Supprimez ce fichier (fix_categories.php) après utilisation !</p>";
echo "</body></html>";
?>

