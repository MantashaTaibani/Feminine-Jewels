-- =====================================================================
-- FEMININE JEWELLS — DATABASE
-- Import this whole file in phpMyAdmin (Import tab) or run:
--   mysql -u root -p < feminine_jewels.sql
-- It creates the database, all tables, and sample jewellery products
-- so the site works immediately after import.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS feminine_jewels
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE feminine_jewels;

-- ---------------------------------------------------------------------
-- USERS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100)  NOT NULL,
  email      VARCHAR(150)  NOT NULL UNIQUE,
  password   VARCHAR(255)  NOT NULL,
  role       ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample logins (see bottom of this file for plain-text passwords).
-- Passwords below are already hashed with PHP's password_hash() (bcrypt).
-- Plain-text test passwords are listed at the bottom of this file.
INSERT INTO users (name, email, password, role) VALUES
('Admin User',    'admin@femininejewels.com',   '$2y$10$Z/hkMCEOlLAY92I5/cqfaeNAOClt5hG2AUhSAiRrr0.7oBkUGHQWy', 'admin'),
('Ananya Sharma', 'customer@femininejewels.com','$2y$10$FW9bb9Raj39q82n0d.WtWOespLFd512wRSkP.M3AbOTuI0Nrxwj3W', 'customer');

-- ---------------------------------------------------------------------
-- CATEGORIES
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO categories (name, description) VALUES
('Necklace',       'Pearl strands, chains and pendants for every neckline.'),
('Ring',           'Delicate rose-gold bands and statement stones.'),
('Earrings',       'Studs, drops and danglers from everyday to occasion.'),
('Bracelet',       'Charm, chain and pearl bracelets for the wrist.'),
('Bangles',        'Classic and kundan bangles in matching stacks.'),
('Pendant',        'Standalone pendants with matching chains.'),
('Jewellery Set',  'Coordinated sets, ready to wear together.');

-- ---------------------------------------------------------------------
-- PRODUCTS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  name        VARCHAR(150) NOT NULL,
  category    VARCHAR(100) NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL DEFAULT 0,
  image       VARCHAR(255) DEFAULT '',
  stock       INT NOT NULL DEFAULT 0,
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO products (name, category, description, price, image, stock) VALUES
('Golden Pearl Drop Necklace',  'Necklace',      'A graceful strand of lustrous faux pearls finished with a rose-gold clasp and a crystal drop pendant. Sits softly at the collarbone.', 1299.00, 'golden-pearl-drop-necklace.jpg', 10),
('Classic Gold Pendant Necklace','Necklace',     'A fine gold-tone chain carrying a single round crystal solitaire pendant. Timeless and easy to layer.', 1099.00, 'classic-gold-pendant-necklace.png', 15),
('Floral Gold Necklace',        'Necklace',      'A statement floral centrepiece in warm gold tone with a hanging pearl, on a delicate chain.', 1599.00, 'floral-gold-necklace.jpg', 8),

('Crystal Gold Ring',           'Ring',          'A slim gold-tone band topped with a brilliant-cut crystal duo. Adjustable for a comfortable fit.', 899.00, 'crystal-gold-ring.jpg', 30),
('Floral Stone Ring',           'Ring',          'A blooming floral ring in soft rose tones with a crystal centre stone. A pretty everyday accent.', 799.00, 'floral-stone-ring.jpg', 20),
('Elegant Pearl Ring',          'Ring',          'A single lustrous pearl set on a polished gold-tone band. Understated elegance for any occasion.', 749.00, 'elegant-pearl-ring.jpg', 4),

('Golden Jhumka Earrings',      'Earrings',      'Traditional gold-tone bell-shaped jhumkas with pearl drops. A festive favourite.', 999.00, 'golden-jhumka-earrings.jpg', 25),
('Pearl Drop Earrings',         'Earrings',      'Classic pearl drops with a small crystal accent and secure backs. Light enough to wear all day.', 799.00, 'pearl-drop-earrings.jpg', 22),
('Crystal Stud Earrings',       'Earrings',      'Sparkling crystal studs surrounded by a fine gold-tone halo. An easy everyday pair.', 549.00, 'crystal-stud-earrings.jpg', 40),

('Golden Charm Bracelet',       'Bracelet',      'A rose-gold chain bracelet with pearl and crystal charms and an adjustable clasp to fit most wrists.', 999.00, 'golden-charm-bracelet.jpg', 14),
('Pearl Bracelet',              'Bracelet',      'A dainty strand of small pearls with a polished gold-tone finish. Elegant and versatile.', 899.00, 'pearl-bracelet.jpg', 16),
('Minimal Gold Bracelet',       'Bracelet',      'A slim, minimal gold-tone chain bracelet with a single crystal accent. Made for stacking.', 699.00, 'minimal-gold-bracelet.jpg', 18),

('Traditional Gold Bangles',    'Bangles',       'A stack of six polished gold-tone bangles with a fine textured edge, grouped for a traditional look.', 1499.00, 'traditional-gold-bangles.jpg', 12),
('Floral Bangles',              'Bangles',       'Gold-tone bangles detailed with pearl and crystal florals, sold as a matching stack.', 1699.00, 'floral-bangles.jpg', 0),

('Royal Pearl Jewellery Set',   'Jewellery Set', 'Necklace and matching earrings in pearls with a gold-tone finish, plus a coordinating ring. Packed in a gift box.', 2499.00, 'royal-pearl-jewellery-set.jpg', 8),
('Golden Floral Jewellery Set', 'Jewellery Set', 'A floral necklace, earrings and ring set in warm gold tone. Made for weddings and festive evenings.', 2799.00, 'golden-floral-jewellery-set.jpg', 6);

-- ---------------------------------------------------------------------
-- CART  (persistent per logged-in user)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS cart (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  product_id INT NOT NULL,
  quantity   INT NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_user_product (user_id, product_id),
  CONSTRAINT fk_cart_user    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE,
  CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------
-- ORDERS + ORDER ITEMS
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  full_name   VARCHAR(100) NOT NULL,
  email       VARCHAR(150) NOT NULL,
  phone       VARCHAR(20)  DEFAULT '',
  address     VARCHAR(255) NOT NULL,
  city        VARCHAR(100) NOT NULL,
  state       VARCHAR(100) NOT NULL,
  pincode     VARCHAR(10)  NOT NULL,
  subtotal    DECIMAL(10,2) NOT NULL DEFAULT 0,
  shipping    DECIMAL(10,2) NOT NULL DEFAULT 0,
  total       DECIMAL(10,2) NOT NULL DEFAULT 0,
  status      ENUM('Pending','Confirmed','Shipped','Delivered','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS order_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  order_id    INT NOT NULL,
  product_id  INT NULL,
  product_name VARCHAR(150) NOT NULL,
  price       DECIMAL(10,2) NOT NULL,
  quantity    INT NOT NULL DEFAULT 1,
  CONSTRAINT fk_items_order   FOREIGN KEY (order_id)   REFERENCES orders(id)   ON DELETE CASCADE,
  CONSTRAINT fk_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- TEST LOGIN CREDENTIALS
-- =====================================================================
-- Admin login:
--   email:    admin@femininejewels.com
--   password: admin123
--
-- Customer login:
--   email:    customer@femininejewels.com
--   password: customer123
-- =====================================================================
