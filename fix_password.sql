-- Script SQL pour corriger le mot de passe admin
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Option 1 : Mettre à jour le mot de passe existant
-- Hash pour le mot de passe "admin123"
-- Note: Utilisez des guillemets doubles pour éviter les problèmes d'échappement
UPDATE admins SET password = "$2y$10$WYQDUEu.7vAzegtN2vXnvuVK7eJsl82i/BhRbhYRTUEJWlSzm3Flm" WHERE username = "admin";

-- Option 2 : Si l'admin n'existe pas, créez-le
INSERT INTO admins (username, password, email) 
VALUES ("admin", "$2y$10$WYQDUEu.7vAzegtN2vXnvuVK7eJsl82i/BhRbhYRTUEJWlSzm3Flm", "admin@meublesmaison.com")
ON DUPLICATE KEY UPDATE password = "$2y$10$WYQDUEu.7vAzegtN2vXnvuVK7eJsl82i/BhRbhYRTUEJWlSzm3Flm";

-- Vérification
SELECT id, username, email, LEFT(password, 30) as password_hash FROM admins WHERE username = "admin";

