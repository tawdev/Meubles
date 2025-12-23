-- Migration de la table categories
-- Supprimer le champ 'icon' (emoji) et ajouter le champ 'image'

USE meubles_db;

-- Supprimer le champ icon (si la colonne existe, sinon une erreur sera générée mais ce n'est pas grave)
-- Note: Si vous obtenez une erreur "Unknown column 'icon'", cela signifie que la colonne n'existe pas déjà
ALTER TABLE categories DROP COLUMN icon;

-- Ajouter le champ image
ALTER TABLE categories ADD COLUMN image VARCHAR(255) NULL AFTER description;

