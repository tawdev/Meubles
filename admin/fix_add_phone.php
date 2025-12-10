<?php
/**
 * Script pour ajouter le champ téléphone à la table contact_messages
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Ajout Champ Téléphone</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{background:#27ae60;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#e74c3c;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#3498db;color:white;padding:15px;border-radius:5px;margin:10px 0;}</style></head><body>";

echo "<h1>🔧 Ajout du Champ Téléphone</h1>";

try {
    // Vérifier si la colonne existe déjà
    echo "<div class='info'><h3>1. Vérification de la colonne 'phone'</h3>";
    $checkColumn = $pdo->query("SHOW COLUMNS FROM contact_messages LIKE 'phone'");
    $hasPhoneColumn = $checkColumn->rowCount() > 0;
    
    if ($hasPhoneColumn) {
        echo "✅ La colonne 'phone' existe déjà.<br>";
    } else {
        echo "❌ La colonne 'phone' n'existe pas. Ajout en cours...<br>";
        echo "</div>";
        
        // Ajouter la colonne téléphone
        try {
            $pdo->exec("ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(50) NULL AFTER email");
            echo "<div class='success'>";
            echo "✅ La colonne 'phone' a été ajoutée avec succès !<br>";
            echo "</div>";
        } catch (PDOException $e) {
            echo "<div class='error'>";
            echo "❌ Erreur lors de l'ajout de la colonne : " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "</div>";
        }
    }
    echo "</div>";
    
    // Vérifier la structure complète
    echo "<div class='info'><h3>2. Structure de la table 'contact_messages'</h3>";
    $columns = $pdo->query("SHOW COLUMNS FROM contact_messages");
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
    
    echo "<div class='success' style='margin-top:20px;'>";
    echo "<h2>✅ Mise à jour terminée !</h2>";
    echo "<p>Le champ téléphone est maintenant disponible.</p>";
    echo "<a href='contacts.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#2c3e50;color:white;text-decoration:none;border-radius:5px;'>📧 Aller à la gestion des messages</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERREUR</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#e74c3c;'><strong>⚠️ SÉCURITÉ :</strong> Supprimez ce fichier (fix_add_phone.php) après utilisation !</p>";
echo "</body></html>";
?>

