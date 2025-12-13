<?php
/**
 * Fichier de configuration d'exemple
 * Copiez ce fichier en config.php et modifiez les valeurs selon votre environnement
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'meubles_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// // Connexion à la base de données MySQL
// $host = 'localhost';
// $dbname = 'u627894251_frachdark';
// $username = 'u627894251_u627894251_fra';
// $password = 'yWI};>6umPfrachdark';

// Configuration du site
define('SITE_NAME', 'Meubles de Maison');
define('SITE_URL', 'http://localhost/MeublesMaison');

// Configuration email (pour le formulaire de contact)
define('CONTACT_EMAIL', 'contact@meublesmaison.com');

// Configuration des uploads
define('UPLOAD_DIR', 'images/');
define('MAX_FILE_SIZE', 5242880); // 5MB en octets
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif']);

// Configuration de session
define('SESSION_LIFETIME', 3600); // 1 heure en secondes
?>

