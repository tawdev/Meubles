-- Script pour corriger la structure de la base de données
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Ajouter la colonne 'stock' si elle n'existe pas
ALTER TABLE products 
ADD COLUMN IF NOT EXISTS stock INT DEFAULT 0 AFTER category;

-- Si la commande ci-dessus ne fonctionne pas (MySQL < 8.0), utilisez celle-ci :
-- ALTER TABLE products ADD COLUMN stock INT DEFAULT 0 AFTER category;

-- Mettre à jour le stock des produits existants si nécessaire
UPDATE products SET stock = 10 WHERE stock IS NULL OR stock = 0;

-- Vérifier la structure de la table
DESCRIBE products;

