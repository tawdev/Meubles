<?php
/**
 * Script pour créer/vérifier la table contact_messages
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Installation Table Contact</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{background:#27ae60;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#e74c3c;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#3498db;color:white;padding:15px;border-radius:5px;margin:10px 0;}</style></head><body>";

echo "<h1>🔧 Installation de la Table Contact</h1>";

try {
    // Créer la table contact_messages
    echo "<div class='info'><h3>1. Création de la table 'contact_messages'</h3>";
    $pdo->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_status BOOLEAN DEFAULT FALSE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "✅ Table 'contact_messages' créée ou déjà existante.<br>";
    echo "</div>";
    
    // Vérifier la structure
    echo "<div class='info'><h3>2. Structure de la table</h3>";
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
    
    // Compter les messages
    $countStmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
    $count = $countStmt->fetch()['count'];
    
    echo "<div class='success' style='margin-top:20px;'>";
    echo "<h2>✅ Installation terminée !</h2>";
    echo "<p>La table 'contact_messages' est prête.</p>";
    echo "<p>Nombre de messages actuellement : <strong>$count</strong></p>";
    echo "<a href='contacts.php' style='display:inline-block;margin-top:10px;padding:10px 20px;background:#2c3e50;color:white;text-decoration:none;border-radius:5px;'>📧 Aller à la gestion des messages</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERREUR</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#e74c3c;'><strong>⚠️ SÉCURITÉ :</strong> Supprimez ce fichier (fix_contact_table.php) après utilisation !</p>";
echo "</body></html>";
?>

