<?php
/**
 * Script pour corriger la structure de la base de données
 * Ajoute la colonne 'stock' si elle n'existe pas
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Correction Base de Données</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{background:#27ae60;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#e74c3c;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#3498db;color:white;padding:15px;border-radius:5px;margin:10px 0;}</style></head><body>";

echo "<h1>🔧 Correction de la Structure de la Base de Données</h1>";

try {
    // Vérifier si la colonne stock existe
    echo "<div class='info'><h3>1. Vérification de la colonne 'stock'</h3>";
    $checkColumn = $pdo->query("SHOW COLUMNS FROM products LIKE 'stock'");
    $hasStockColumn = $checkColumn->rowCount() > 0;
    
    if ($hasStockColumn) {
        echo "✅ La colonne 'stock' existe déjà.<br>";
    } else {
        echo "❌ La colonne 'stock' n'existe pas. Ajout en cours...<br>";
        echo "</div>";
        
        // Ajouter la colonne stock
        try {
            $pdo->exec("ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER category");
            echo "<div class='success'>";
            echo "✅ La colonne 'stock' a été ajoutée avec succès !<br>";
            echo "</div>";
            
            // Mettre à jour le stock des produits existants
            $pdo->exec("UPDATE products SET stock = 10 WHERE stock IS NULL OR stock = 0");
            echo "<div class='info'>";
            echo "✅ Stock initialisé pour les produits existants.<br>";
            echo "</div>";
            
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "❌ Erreur lors de l'ajout de la colonne : " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Vérifier la structure complète de la table
    echo "<div class='info'><h3>2. Structure de la table 'products'</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM products");
    echo "<table border='1' cellpadding='5' style='border-collapse:collapse;width:100%;'>";
    echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Défaut</th></tr>";
    while ($col = $columns->fetch()) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Vérification finale
    echo "<div class='success' style='margin-top:20px;'>";
    echo "<h2>✅ Vérification terminée !</h2>";
    echo "<p>La base de données est maintenant correctement configurée.</p>";
    echo "<a href='dashboard.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#2c3e50;color:white;text-decoration:none;border-radius:5px;'>📊 Aller au tableau de bord</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERREUR</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#e74c3c;'><strong>⚠️ SÉCURITÉ :</strong> Supprimez ce fichier (fix_database.php) après utilisation !</p>";
echo "</body></html>";
?>

