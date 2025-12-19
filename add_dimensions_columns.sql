-- Script pour ajouter les colonnes de dimensions pour les produits sur mesure
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Ajouter les colonnes max_length et max_width à la table products
ALTER TABLE products 
ADD COLUMN max_length DECIMAL(10,2) NULL COMMENT 'Longueur maximale en cm (pour produits sur mesure)',
ADD COLUMN max_width DECIMAL(10,2) NULL COMMENT 'Largeur maximale en cm (pour produits sur mesure)';

-- Vérifier la structure
DESCRIBE products;

