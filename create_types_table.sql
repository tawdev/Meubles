-- Script pour créer la table types et modifier types_categories
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Créer la table des types
CREATE TABLE IF NOT EXISTS types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer les types par défaut
INSERT INTO types (name) VALUES
('En stock'),
('Sur mesure')
ON DUPLICATE KEY UPDATE name=name;

-- Ajouter la colonne types_id à la table types_categories
-- Note: Cette commande peut échouer si la colonne existe déjà
ALTER TABLE types_categories 
ADD COLUMN types_id INT NULL AFTER category_id;

-- Ajouter l'index pour la colonne types_id
ALTER TABLE types_categories 
ADD INDEX idx_types_id (types_id);

-- Ajouter la clé étrangère (après création de la table types)
ALTER TABLE types_categories 
ADD FOREIGN KEY (types_id) REFERENCES types(id) ON DELETE SET NULL;

