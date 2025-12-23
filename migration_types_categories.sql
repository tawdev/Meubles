-- Migration de la table types_categories
-- Ajouter le champ 'image' (chemin vers l'image du type de catégorie)

USE meubles_db;

-- Ajouter le champ image après le nom du type
ALTER TABLE types_categories
    ADD COLUMN image VARCHAR(255) NULL AFTER name;


