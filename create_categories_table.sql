-- Script pour créer la table des catégories
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Créer la table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer les catégories par défaut
INSERT INTO categories (name, description, icon) VALUES
('Salon', 'Meubles pour le salon : canapés, tables basses, fauteuils', '🛋️'),
('Chambre', 'Meubles pour la chambre : lits, armoires, commodes', '🛏️'),
('Salle à manger', 'Meubles pour la salle à manger : tables, chaises, buffets', '🍽️'),
('Bureau', 'Meubles de bureau : bureaux, chaises, étagères', '💼'),
('Décoration', 'Éléments de décoration : étagères, miroirs, accessoires', '🖼️')
ON DUPLICATE KEY UPDATE name=name;

