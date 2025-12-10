-- Base de données pour le site "Meubles de maison"
-- Créer la base de données
CREATE DATABASE IF NOT EXISTS meubles_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE meubles_db;

-- Table des produits
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255),
    category VARCHAR(100) NOT NULL,
    stock INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des administrateurs
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des commandes
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(50),
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('En attente', 'Confirmée', 'Livrée', 'Annulée') DEFAULT 'En attente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des articles de commande
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages de contact
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    read_status BOOLEAN DEFAULT FALSE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insérer un administrateur par défaut (username: admin, password: admin123)
-- Le mot de passe est hashé avec password_hash()
-- Hash pour "admin123" : $2y$10$0AFZf4AaZfiwJ1vvJaXOguGUuuzeUBtZS6IBBigSp7uVLuL/BQ47e
INSERT INTO admins (username, password, email) VALUES 
('admin', '$2y$10$0AFZf4AaZfiwJ1vvJaXOguGUuuzeUBtZS6IBBigSp7uVLuL/BQ47e', 'admin@meublesmaison.com');

-- Insérer des produits d'exemple
INSERT INTO products (name, description, price, image, category, stock) VALUES
('Canapé moderne gris', 'Canapé 3 places en tissu gris, confortable et élégant. Parfait pour votre salon moderne.', 899.99, 'images/sofa1.jpg', 'Salon', 15),
('Table à manger en bois massif', 'Table à manger rectangulaire en chêne massif, 6 places. Design classique et intemporel.', 1299.99, 'images/table1.jpg', 'Salle à manger', 8),
('Lit double avec tête de lit', 'Lit double 160x200 cm avec tête de lit rembourrée. Style contemporain.', 699.99, 'images/bed1.jpg', 'Chambre', 12),
('Armoire 3 portes', 'Armoire 3 portes avec miroir, grande capacité de rangement. Finition blanche.', 1199.99, 'images/wardrobe1.jpg', 'Chambre', 6),
('Chaise de bureau ergonomique', 'Chaise de bureau ergonomique avec support lombaire. Réglable en hauteur.', 349.99, 'images/chair1.jpg', 'Bureau', 20),
('Étagère murale design', 'Étagère murale moderne en métal et bois. Parfaite pour décorer et ranger.', 149.99, 'images/shelf1.jpg', 'Décoration', 25),
('Bureau en bois', 'Bureau moderne en bois avec tiroirs. Idéal pour le télétravail.', 499.99, 'images/desk1.jpg', 'Bureau', 10),
('Commode 4 tiroirs', 'Commode 4 tiroirs en bois massif. Style scandinave.', 599.99, 'images/dresser1.jpg', 'Chambre', 9);

