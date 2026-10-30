-- FocalPoint camera store database. Reg. No. 27578/2024.
-- Import this file in phpMyAdmin. Importing it again resets the shop to the original 13 products.

CREATE DATABASE IF NOT EXISTS camera_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE camera_store;
SET NAMES utf8mb4;

DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(60) NOT NULL,
  image VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  price INT UNSIGNED NOT NULL,
  stock INT UNSIGNED NOT NULL DEFAULT 0,
  description TEXT NOT NULL,
  image VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category (category_id),
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE messages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  body TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, name, image) VALUES
  (1, 'Cameras', 'assets/images/products/hero-camera.jpg'),
  (2, 'Lenses', 'assets/images/products/lens-85mm.jpg'),
  (3, 'Drones & Action', 'assets/images/products/camera-drone.jpg'),
  (4, 'Studio & Accessories', 'assets/images/products/camera-backpack.jpg');

INSERT INTO products (id, category_id, name, price, stock, description, image, created_at) VALUES
  (1, 1, 'Vista X7 Full-Frame Mirrorless', 3250000, 8, 'A full-frame mirrorless body with fast autofocus and strong low-light performance. A dependable all-rounder for weddings, portraits and travel work.', 'assets/images/products/vista-x7.jpg', '2026-10-30 00:00:00'),
  (2, 1, 'Vista M3 Compact Mirrorless', 1150000, 14, 'Small enough for a jacket pocket with its pancake lens fitted. A great everyday camera for street photography and first-time mirrorless buyers.', 'assets/images/products/vista-m3.jpg', '2026-10-30 00:00:00'),
  (3, 1, 'Titan D90 Professional DSLR', 2400000, 5, 'A weather-sealed professional DSLR with a deep grip and long battery life. Built for sports, wildlife and long days on location.', 'assets/images/products/titan-d90.jpg', '2026-10-30 00:00:00'),
  (4, 1, 'Heritage 35 Film Camera', 420000, 6, 'A classic 35mm rangefinder with a metal top plate and leather body. Fully manual controls for photographers who enjoy slowing down.', 'assets/images/products/heritage-35.jpg', '2026-10-30 00:00:00'),
  (5, 1, 'SnapJoy Instant Camera', 165000, 22, 'Point, shoot and hold a print within seconds. A cheerful, simple instant camera for parties, gifts and family moments.', 'assets/images/products/snapjoy.jpg', '2026-10-30 00:00:00'),
  (6, 3, 'TrailGo Action Camera', 480000, 12, 'A rugged action camera supplied with a clear waterproof housing. Ideal for cycling, hiking and water sports footage.', 'assets/images/products/trailgo.jpg', '2026-10-30 00:00:00'),
  (7, 3, 'SkyLens Mini Camera Drone', 1350000, 4, 'A folding camera drone that packs down small and flies steadily. Capture aerial photos and video of landscapes, events and property.', 'assets/images/products/skylens-mini.jpg', '2026-10-30 00:00:00'),
  (8, 2, 'ProZoom 24-70mm f/2.8 Lens', 1900000, 7, 'The standard zoom working photographers rely on, from wide scenes to tight portraits. The constant f/2.8 aperture stays bright across the whole range.', 'assets/images/products/prozoom-2470.jpg', '2026-10-30 00:00:00'),
  (9, 2, 'ClearView 50mm f/1.8 Lens', 280000, 18, 'A light, affordable prime with a natural field of view. Its wide aperture gives soft backgrounds and clean results in dim rooms.', 'assets/images/products/clearview-50.jpg', '2026-10-30 00:00:00'),
  (10, 2, 'FarReach 70-200mm f/2.8 Lens', 2650000, 3, 'A fast telephoto zoom with a removable tripod collar. Brings distant sports action and wildlife close while keeping subjects sharp.', 'assets/images/products/farreach-70200.jpg', '2026-10-30 00:00:00'),
  (11, 4, 'StableCarbon Travel Tripod', 310000, 10, 'A carbon-fibre tripod with a smooth ball head that is light enough to carry all day. Steady support for landscapes and long exposures.', 'assets/images/products/stablecarbon.jpg', '2026-10-30 00:00:00'),
  (12, 4, 'Nomad Camera Backpack', 145000, 16, 'A canvas backpack with leather straps and padded dividers you can rearrange. Holds a camera body, several lenses and your daily essentials.', 'assets/images/products/nomad-backpack.jpg', '2026-10-30 00:00:00'),
  (13, 4, 'StudioGlow Softbox Kit', 380000, 9, 'A softbox light on an adjustable stand that gives even, flattering light. A simple way to set up a home studio for portraits and product shots.', 'assets/images/products/studioglow.jpg', '2026-10-30 00:00:00');

-- Default admin login: username admin, password admin123 (stored only as a bcrypt hash)
INSERT INTO users (id, username, password_hash) VALUES
  (1, 'admin', '$2y$10$vFNwLpyxOLtF4Dg3dJZFwuLkti.03bKn14If6m7jmRZKEmHGLuVhO');
