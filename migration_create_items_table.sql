USE meubles_db;

-- Création de la table types_categories_items
-- Cette table permet de lier des éléments spécifiques à chaque type de catégorie
CREATE TABLE IF NOT EXISTS types_categories_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    types_categories_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_types_categories_items
        FOREIGN KEY (types_categories_id)
        REFERENCES types_categories(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
