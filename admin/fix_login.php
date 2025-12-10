<?php
/**
 * Script de diagnostic et correction du problème de connexion
 * Ce script va vérifier et corriger la base de données
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

echo "<h2>🔧 Diagnostic et Correction de la Connexion Admin</h2>";
echo "<hr>";

// 1. Vérifier la connexion à la base de données
echo "<h3>1. Vérification de la connexion à la base de données</h3>";
try {
    $test = $pdo->query("SELECT 1");
    echo "✅ Connexion à la base de données : OK<br>";
} catch (PDOException $e) {
    echo "❌ Erreur de connexion : " . $e->getMessage() . "<br>";
    die();
}

// 2. Vérifier si la table admins existe
echo "<h3>2. Vérification de la table 'admins'</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() > 0) {
        echo "✅ La table 'admins' existe<br>";
    } else {
        echo "❌ La table 'admins' n'existe pas. Création en cours...<br>";
        $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "✅ Table 'admins' créée<br>";
    }
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage() . "<br>";
}

// 3. Vérifier si l'admin existe
echo "<h3>3. Vérification de l'utilisateur admin</h3>";
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute(['admin']);
$admin = $stmt->fetch();

if ($admin) {
    echo "✅ L'utilisateur 'admin' existe<br>";
    echo "📋 Informations actuelles :<br>";
    echo "   - ID: " . $admin['id'] . "<br>";
    echo "   - Username: " . htmlspecialchars($admin['username']) . "<br>";
    echo "   - Email: " . htmlspecialchars($admin['email'] ?? 'N/A') . "<br>";
    echo "   - Hash actuel: " . substr($admin['password'], 0, 20) . "...<br>";
    
    // Tester le hash actuel
    $testPassword = 'admin123';
    $hashWorks = password_verify($testPassword, $admin['password']);
    echo "   - Test du hash avec 'admin123': " . ($hashWorks ? "✅ OK" : "❌ ÉCHEC") . "<br>";
    
    if (!$hashWorks) {
        echo "<br>⚠️ Le hash ne fonctionne pas. Mise à jour en cours...<br>";
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $updateStmt->execute([$newHash, 'admin']);
        echo "✅ Mot de passe mis à jour avec succès !<br>";
        echo "   - Nouveau hash: " . substr($newHash, 0, 20) . "...<br>";
    }
} else {
    echo "❌ L'utilisateur 'admin' n'existe pas. Création en cours...<br>";
    $newPassword = 'admin123';
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $insertStmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
    $insertStmt->execute(['admin', $hashedPassword, 'admin@meublesmaison.com']);
    echo "✅ Utilisateur 'admin' créé avec succès !<br>";
    echo "   - Username: admin<br>";
    echo "   - Password: admin123<br>";
    echo "   - Hash: " . substr($hashedPassword, 0, 20) . "...<br>";
}

// 4. Test final de connexion
echo "<h3>4. Test final</h3>";
$stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->execute(['admin']);
$finalAdmin = $stmt->fetch();

if ($finalAdmin && password_verify('admin123', $finalAdmin['password'])) {
    echo "✅ <strong>TOUT EST CORRIGÉ !</strong><br>";
    echo "<br>";
    echo "<div style='background: #27ae60; color: white; padding: 1rem; border-radius: 5px; margin: 1rem 0;'>";
    echo "<strong>Vous pouvez maintenant vous connecter avec :</strong><br>";
    echo "Username: <strong>admin</strong><br>";
    echo "Password: <strong>admin123</strong><br>";
    echo "</div>";
    echo "<br>";
    echo "<a href='login.php' style='display: inline-block; padding: 0.75rem 2rem; background: #3498db; color: white; text-decoration: none; border-radius: 5px;'>🔐 Aller à la page de connexion</a>";
} else {
    echo "❌ Il y a encore un problème. Veuillez vérifier manuellement dans phpMyAdmin.";
}

echo "<hr>";
echo "<p style='color: #e74c3c;'><strong>⚠️ IMPORTANT :</strong> Supprimez ce fichier (fix_login.php) après utilisation pour des raisons de sécurité !</p>";
?>

