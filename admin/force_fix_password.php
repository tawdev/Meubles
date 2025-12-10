<?php
/**
 * Script de FORCE pour corriger le mot de passe admin
 * Ce script va FORCER la mise à jour du mot de passe
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../db.php';

$username = 'admin';
$password = 'admin123';

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Correction Mot de Passe</title>";
echo "<style>body{font-family:Arial;max-width:800px;margin:50px auto;padding:20px;}";
echo ".success{background:#27ae60;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".error{background:#e74c3c;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo ".info{background:#3498db;color:white;padding:15px;border-radius:5px;margin:10px 0;}";
echo "a{display:inline-block;margin-top:20px;padding:10px 20px;background:#2c3e50;color:white;text-decoration:none;border-radius:5px;}</style></head><body>";

echo "<h1>🔧 Correction Forcée du Mot de Passe Admin</h1>";

try {
    // Générer un nouveau hash
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    echo "<div class='info'>";
    echo "<strong>Action en cours :</strong><br>";
    echo "Username: <strong>$username</strong><br>";
    echo "Password: <strong>$password</strong><br>";
    echo "Nouveau hash généré: " . substr($hashedPassword, 0, 30) . "...<br>";
    echo "</div>";
    
    // Vérifier si l'admin existe
    $checkStmt = $pdo->prepare("SELECT id, password FROM admins WHERE username = ?");
    $checkStmt->execute([$username]);
    $existing = $checkStmt->fetch();
    
    if ($existing) {
        // Mettre à jour le mot de passe existant
        $updateStmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $result = $updateStmt->execute([$hashedPassword, $username]);
        
        if ($result) {
            echo "<div class='success'>";
            echo "<h2>✅ SUCCÈS !</h2>";
            echo "<p>Le mot de passe a été mis à jour avec succès !</p>";
            echo "<p><strong>Ancien hash:</strong> " . substr($existing['password'], 0, 30) . "...</p>";
            echo "<p><strong>Nouveau hash:</strong> " . substr($hashedPassword, 0, 30) . "...</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h2>❌ ERREUR</h2>";
            echo "<p>Impossible de mettre à jour le mot de passe.</p>";
            echo "</div>";
        }
    } else {
        // Créer l'admin s'il n'existe pas
        $insertStmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $result = $insertStmt->execute([$username, $hashedPassword, 'admin@meublesmaison.com']);
        
        if ($result) {
            echo "<div class='success'>";
            echo "<h2>✅ SUCCÈS !</h2>";
            echo "<p>L'administrateur a été créé avec succès !</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h2>❌ ERREUR</h2>";
            echo "<p>Impossible de créer l'administrateur.</p>";
            echo "</div>";
        }
    }
    
    // Vérification finale
    echo "<div class='info'>";
    echo "<h3>🔍 Vérification finale :</h3>";
    $verifyStmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
    $verifyStmt->execute([$username]);
    $admin = $verifyStmt->fetch();
    
    if ($admin) {
        $testVerify = password_verify($password, $admin['password']);
        echo "Utilisateur trouvé : ✅<br>";
        echo "Test du mot de passe : " . ($testVerify ? "✅ OK" : "❌ ÉCHEC") . "<br>";
        
        if ($testVerify) {
            echo "<div class='success' style='margin-top:20px;'>";
            echo "<h2>🎉 TOUT FONCTIONNE !</h2>";
            echo "<p><strong>Vous pouvez maintenant vous connecter avec :</strong></p>";
            echo "<ul>";
            echo "<li>Username: <strong>admin</strong></li>";
            echo "<li>Password: <strong>admin123</strong></li>";
            echo "</ul>";
            echo "</div>";
            echo "<a href='login.php'>🔐 Aller à la page de connexion</a>";
        } else {
            echo "<div class='error' style='margin-top:20px;'>";
            echo "<p>⚠️ Le hash ne fonctionne toujours pas. Il y a peut-être un problème avec la fonction password_verify().</p>";
            echo "<p>Essayez de réimporter le fichier database.sql dans phpMyAdmin.</p>";
            echo "</div>";
        }
    } else {
        echo "<div class='error'>";
        echo "<p>❌ L'utilisateur n'a pas été trouvé après la création/mise à jour.</p>";
        echo "</div>";
    }
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div class='error'>";
    echo "<h2>❌ ERREUR DE BASE DE DONNÉES</h2>";
    echo "<p><strong>Message:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p><strong>Vérifiez :</strong></p>";
    echo "<ul>";
    echo "<li>Que MySQL est démarré dans XAMPP</li>";
    echo "<li>Que la base de données 'meubles_db' existe</li>";
    echo "<li>Que la table 'admins' existe</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color:#e74c3c;'><strong>⚠️ SÉCURITÉ :</strong> Supprimez ce fichier (force_fix_password.php) après utilisation !</p>";
echo "</body></html>";
?>

