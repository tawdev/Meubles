-- Script pour ajouter le champ téléphone à la table contact_messages
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Ajouter la colonne téléphone
ALTER TABLE contact_messages 
ADD COLUMN phone VARCHAR(50) NULL AFTER email;

-- Vérifier la structure
DESCRIBE contact_messages;

