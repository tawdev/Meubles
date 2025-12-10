-- Script SQL SIMPLIFIÉ pour corriger le mot de passe admin
-- Copiez et exécutez UNE SEULE ligne à la fois dans phpMyAdmin

USE meubles_db;

-- Ligne 1 : Mettre à jour le mot de passe (copiez uniquement cette ligne)
UPDATE admins SET password = "$2y$10$WYQDUEu.7vAzegtN2vXnvuVK7eJsl82i/BhRbhYRTUEJWlSzm3Flm" WHERE username = "admin";

-- Ligne 2 : Vérifier que ça a fonctionné (optionnel)
SELECT id, username, email FROM admins WHERE username = "admin";

