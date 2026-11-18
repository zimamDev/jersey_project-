-- ==========================================================
-- Interactive Jersey Customization and Ordering System
-- Database Schema  (v2 - adds Ready-Made jerseys, jersey
-- design patterns, admin jersey/user management & reports)
-- ==========================================================

CREATE DATABASE IF NOT EXISTS jersey_system;
USE jersey_system;

-- ------------------------------------------------------
-- Table: users  (customers)
-- ------------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------
-- Table: admins
-- ------------------------------------------------------
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Default admin -> username: admin  password: admin123
INSERT INTO admins (username, password) VALUES
('admin', '$2y$10$hQ1i4UYNcojLln2NcFsE8OiV1x3yIQBYRvHD7lKASrX4.qy9likGW');

-- ------------------------------------------------------
-- Table: jerseys
-- Holds BOTH: (a) blank "Custom Template" jerseys used in the
-- customizer, and (b) finished "Ready-Made" jerseys the admin
-- lists with a real product photo for direct purchase.
-- ------------------------------------------------------
CREATE TABLE jerseys (
    jersey_id INT AUTO_INCREMENT PRIMARY KEY,
    jersey_name VARCHAR(100) NOT NULL,
    category ENUM('Custom Template','Ready-Made') NOT NULL DEFAULT 'Custom Template',
    base_price DECIMAL(10,2) NOT NULL DEFAULT 2500.00,
    description TEXT,
    image_path VARCHAR(255) DEFAULT NULL,
    base_color VARCHAR(20) NOT NULL DEFAULT '#e11d2e',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO jerseys (jersey_name, category, base_price, description, base_color) VALUES
('Classic Round Neck Jersey', 'Custom Template', 2500.00, 'Standard round neck sports jersey, breathable fabric. Fully customizable colors, name, number and logo.', '#e11d2e'),
('V-Neck Pro Jersey', 'Custom Template', 2800.00, 'Professional V-neck jersey used for football/rugby. Fully customizable colors, name, number and logo.', '#0b1f3a'),
('Basketball Jersey', 'Custom Template', 2600.00, 'Sleeveless basketball style jersey. Fully customizable colors, name, number and logo.', '#f77f00');

-- ------------------------------------------------------
-- Table: customizations (each customer design/order draft)
-- ------------------------------------------------------
CREATE TABLE customizations (
    customization_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jersey_id INT NOT NULL,
    purchase_type ENUM('Custom','Ready-Made') NOT NULL DEFAULT 'Custom',
    jersey_color VARCHAR(20) NOT NULL DEFAULT '#e11d2e',
    sleeve_color VARCHAR(20) NOT NULL DEFAULT '#FFFFFF',
    design_pattern ENUM('Plain','Sash','Hoops','Stripes') NOT NULL DEFAULT 'Plain',
    pattern_color VARCHAR(20) NOT NULL DEFAULT '#FFD60A',
    size ENUM('XS','S','M','L','XL','XXL') NOT NULL DEFAULT 'M',
    player_name VARCHAR(50) DEFAULT NULL,
    player_number VARCHAR(5) DEFAULT NULL,
    logo_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (jersey_id) REFERENCES jerseys(jersey_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------
-- Table: orders
-- ------------------------------------------------------
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    customization_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    total_price DECIMAL(10,2) NOT NULL,
    delivery_address VARCHAR(255) NOT NULL,
    delivery_phone VARCHAR(20) NOT NULL,
    order_status ENUM('Pending','Processing','Completed','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (customization_id) REFERENCES customizations(customization_id)
) ENGINE=InnoDB;

-- ==========================================================
-- UPGRADING AN EXISTING (v1) DATABASE?
-- Run these statements instead of the CREATE TABLEs above:
-- ==========================================================
-- ALTER TABLE jerseys
--   ADD COLUMN category ENUM('Custom Template','Ready-Made') NOT NULL DEFAULT 'Custom Template' AFTER jersey_name,
--   ADD COLUMN base_color VARCHAR(20) NOT NULL DEFAULT '#e11d2e' AFTER image_path,
--   ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER base_color;
--
-- ALTER TABLE customizations
--   ADD COLUMN purchase_type ENUM('Custom','Ready-Made') NOT NULL DEFAULT 'Custom' AFTER jersey_id,
--   ADD COLUMN design_pattern ENUM('Plain','Sash','Hoops','Stripes') NOT NULL DEFAULT 'Plain' AFTER sleeve_color,
--   ADD COLUMN pattern_color VARCHAR(20) NOT NULL DEFAULT '#FFD60A' AFTER design_pattern;
