-- ZXTec Website Database Schema
-- Run this to create the database and tables.

CREATE DATABASE IF NOT EXISTS zxtec CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zxtec;

-- Site images: every image on the site is referenced by a unique `slot` key.
-- The frontend looks up images by slot, so the backend can add/replace them.
CREATE TABLE IF NOT EXISTS images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    slot VARCHAR(64) NOT NULL UNIQUE,   -- machine key, e.g. 'hero_scooter'
    label VARCHAR(128) NOT NULL,        -- human readable name for admin UI
    filename VARCHAR(255) DEFAULT NULL, -- stored file in /uploads
    alt_text VARCHAR(255) DEFAULT '',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin users for the backend
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed the image slots the template needs. filename is NULL until an admin uploads.
INSERT INTO images (slot, label, alt_text) VALUES
    ('logo_header',      'Header Logo',                'ZXTec logo'),
    ('logo_footer',      'Footer Logo',                'ZXTec logo'),
    ('hero_scooter',     'Hero Scooter (main banner)', 'White electric scooter'),
    ('lineup_vehicles',  'Product Lineup Row',         'Range of electric vehicles'),
    ('kunpeng_scooter',  'Kunpeng Feature Scooter',    'Kunpeng electric scooter'),
    ('world_map',        'World Wide Reach Map',       'World map with distribution points'),
    ('testimonial_avatar','Testimonial Avatar',        'Customer photo'),
    ('cta_rider',        'CTA Banner Rider',           'Rider on a motorcycle')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Seed a default admin (username: admin / password: admin123).
-- The password hash below is for 'admin123'. Change it after first login.
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$jdfcOjqEJWmmJhPZwxxLPOgpN29GqScNh6U7kG679YmTq8/L2GyQm')
ON DUPLICATE KEY UPDATE username = VALUES(username);
