-- Script pour mettre à jour tous les enregistrements de types_categories
-- pour fixer types_id = "En stock"
-- Exécutez ce script dans phpMyAdmin

USE meubles_db;

-- Mettre à jour tous les enregistrements de types_categories
-- pour définir types_id à l'id du type "En stock"
UPDATE types_categories tc
INNER JOIN types t ON t.name = 'En stock'
SET tc.types_id = t.id;

