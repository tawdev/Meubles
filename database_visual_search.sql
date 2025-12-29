-- Database Schema for Visual Search System
-- جدول لتخزين معلومات الـ vectors (اختياري - للتحسينات المستقبلية)

USE meubles_db;

-- Table for storing product embeddings metadata
CREATE TABLE IF NOT EXISTS product_embeddings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    vector_path VARCHAR(255) NOT NULL,
    vector_dim INT DEFAULT 1536,
    model_version VARCHAR(50) DEFAULT 'efficientnet-b3',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_product (product_id),
    INDEX idx_product_id (product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for search history (optional - for analytics)
CREATE TABLE IF NOT EXISTS visual_search_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_ip VARCHAR(45),
    detected_category VARCHAR(100),
    detected_objects TEXT,
    results_count INT DEFAULT 0,
    search_time_ms INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_created_at (created_at),
    INDEX idx_category (detected_category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table for search performance metrics (optional)
CREATE TABLE IF NOT EXISTS visual_search_metrics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date DATE NOT NULL,
    total_searches INT DEFAULT 0,
    successful_searches INT DEFAULT 0,
    avg_search_time_ms DECIMAL(10,2),
    avg_results_count DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

