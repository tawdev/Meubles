<?php
/**
 * Script de réinitialisation du mot de passe admin
 * À exécuter une seule fois pour corriger le mot de passe
 * SUPPRIMEZ CE FICHIER après utilisation pour des raisons de sécurité
 */

require_once __DIR__ . '/../db.php';

// Nouveau mot de passe
$newPassword = 'admin123';
$username = 'admin';

// Hasher le nouveau mot de passe
$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

try {
    // Vérifier si l'admin existe
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        // Mettre à jour le mot de passe
        $stmt = $pdo->prepare("UPDATE admins SET password = ? WHERE username = ?");
        $stmt->execute([$hashedPassword, $username]);
        echo "✅ Mot de passe mis à jour avec succès !<br>";
        echo "Username: <strong>$username</strong><br>";
        echo "Password: <strong>$newPassword</strong><br><br>";
        echo "⚠️ <strong>IMPORTANT :</strong> Supprimez ce fichier (reset_password.php) maintenant pour des raisons de sécurité !";
    } else {
        // Créer l'admin s'il n'existe pas
        $stmt = $pdo->prepare("INSERT INTO admins (username, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, 'admin@meublesmaison.com']);
        echo "✅ Administrateur créé avec succès !<br>";
        echo "Username: <strong>$username</strong><br>";
        echo "Password: <strong>$newPassword</strong><br><br>";
        echo "⚠️ <strong>IMPORTANT :</strong> Supprimez ce fichier (reset_password.php) maintenant pour des raisons de sécurité !";
    }
} catch (PDOException $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
?>

