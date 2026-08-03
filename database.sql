-- ZXTec Website Database Schema
-- Run this to create the tables inside your existing database.
-- On shared hosting, create the database via your hosting control panel first
-- (you likely already have one, e.g. tcplckfa_ewautodb), then select it in
-- phpMyAdmin before running this script. Do NOT run CREATE DATABASE/USE here
-- unless your DB user has privileges to create databases.

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
    ('cta_rider',        'CTA Banner Rider',           'Rider on a motorcycle'),
    ('favicon',           'Favicon',                    'ZXTec favicon')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- Seed a default admin (username: admin / password: admin123).
-- The password hash below is for 'admin123'. Change it after first login.
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$jdfcOjqEJWmmJhPZwxxLPOgpN29GqScNh6U7kG679YmTq8/L2GyQm')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- ---------------------------------------------------------------------------
-- Site settings: simple key/value store (maintenance mode, contact info, etc)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key   VARCHAR(64) PRIMARY KEY,
    setting_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO settings (setting_key, setting_value) VALUES
    ('maintenance_mode', '0'),
    ('maintenance_message', 'We''re making some improvements. Please check back soon.'),
    ('site_title', 'ZXTec')
ON DUPLICATE KEY UPDATE setting_key = setting_key;

-- ---------------------------------------------------------------------------
-- Page content: every editable text block on the homepage, keyed by name.
-- Defaults below match the text that shipped hardcoded in the template.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS content (
    content_key   VARCHAR(64) PRIMARY KEY,
    content_value TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO content (content_key, content_value) VALUES
    ('hero_eyebrow', 'LET''S RIDE THE'),
    ('hero_title', 'DREAM'),
    ('hero_copy', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.'),
    ('hero_cta_label', 'Learn more'),
    ('spec1_label', 'Battery'), ('spec1_value', 'Lead-acid/Lithiut'),
    ('spec2_label', 'Max Speed'), ('spec2_value', '50/80km/h'),
    ('spec3_label', 'Range'), ('spec3_value', '80/120km'),
    ('spec4_label', 'Weight allow'), ('spec4_value', '150kg'),
    ('spec5_label', 'Motor'), ('spec5_value', '1500/3000w'),

    ('intro_para1', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla. Varius nec id egestas arcu pretium elit egestas in amet. Elementum accumsan blandit purus duis lorem tincidunt at.'),
    ('intro_para2', 'Vel quam placerat nunc sed. Arcu porta pretium consequat id vestibulum nullam. Sit sit faucibus sodales aliquet enim pharetra urna imperdiet. Scelerisque enim in sed commodo odio. Non nisl vestibulum convallis non sapien mattis. Viverra congue et viverra.'),
    ('intro_heading', 'LOREM IPSUM DOLOR SIT AMET CONSECTETUR.'),

    ('feature_title', 'KUNPENG'),
    ('feature_sub', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem.'),
    ('kfeature1_label', 'Max Speed'), ('kfeature1_value', '50/80km/h'),
    ('kfeature2_label', 'Range'), ('kfeature2_value', '80/120km'),
    ('kfeature3_label', 'Weight allow'), ('kfeature3_value', '150kg'),
    ('kfeature4_label', 'Motor'), ('kfeature4_value', '1500/3000w'),
    ('kfeature5_label', 'Battery'), ('kfeature5_value', 'Lead-acid/Lithiut'),

    ('world_heading_prefix', 'WE ARE'),
    ('world_heading_highlight', 'WORLD WIDE REACH'),
    ('world_copy', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.'),
    ('testimonial_name', 'Lorem ipsum dolor'),
    ('testimonial_role', 'Lorem ipsum'),
    ('testimonial_quote', 'Lorem ipsum dolor sit amet consectetur. Nisl proin volutpat leo sed. Enim a rhoncus faucibus proin risus tincidunt. Proin mi nisl donec eu sociis nullam cursus rhoncus elit. Est eu ac iaculis iaculis consequat risus et. Ac molestie netus varius praesent.'),

    ('cta_heading', 'LOREM IPSUM DOLOR SITUR.'),
    ('cta_copy', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.'),
    ('cta_button_label', 'Contact us'),
    ('stat1_num', '100+'), ('stat1_cap', 'Countries and regions exports'),
    ('stat2_num', '500+'), ('stat2_cap', 'Global distributors'),
    ('stat3_num', '5'), ('stat3_cap', 'Production bases: Wuxi, Tianjin, Dongguan, Thailand, Indonesia'),
    ('stat4_num', '3'), ('stat4_cap', 'Three branch offices: Shenzhen, Poland, United States'),

    ('footer_about', 'Lorem ipsum dolor sit amet consectetur. Pharetra at pretium fringilla nisl feugiat. Purus vel lectus faucibus non porttitor sit magna tincidunt tellus. Ut odio in vitae mollis tortor ultrices.'),
    ('contact_email', 'companyname@gamil.com'),
    ('contact_phone', '(+391) 1234 8492'),
    ('footer_bottom', 'ZXTec @2026, All Right reserved by Creativelements'),

    ('contact_eyebrow', 'GET IN TOUCH'),
    ('contact_title', 'CONTACT US'),
    ('contact_intro', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.'),
    ('address_label', 'London Eye, UK'),
    ('address_card_text', 'Lorem ipsum dolor sit amet consectetur. Laoreet id lorem ut velit aliquam facilisi ut fermentum elit.'),
    ('contact_address', 'London Eye, London, UK'),
    ('form_heading', 'Send us a message'),
    ('form_subtext', 'Do you have a question? A complaint? Or need any help? Feel free to contact us.'),
    ('form_button_label', 'Send a Message'),
    ('form_success_message', 'Thanks for reaching out! We''ll get back to you soon.'),
    ('contact_side_heading', 'Hi! We are always here to help you.'),
    ('whatsapp_phone', '(+391) 1234 8492'),

    ('about_eyebrow', 'WHO WE ARE'),
    ('about_title', 'ABOUT US'),
    ('about_intro', 'Lorem ipsum dolor sit amet consectetur. Erat dui rhoncus consectetur tincidunt. Mi felis odio consectetur est.'),

    ('story_heading', 'OUR STORY'),
    ('story_para1', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla. Varius nec id egestas arcu pretium elit egestas in amet. Elementum accumsan blandit purus duis lorem tincidunt at.'),
    ('story_para2', 'Vel quam placerat nunc sed. Arcu porta pretium consequat id vestibulum nullam. Sit sit faucibus sodales aliquet enim pharetra urna imperdiet. Scelerisque enim in sed commodo odio. Non nisl vestibulum convallis non sapien mattis. Viverra congue et viverra.'),

    ('about_stat1_num', '100+'), ('about_stat1_cap', 'Countries and regions exports'),
    ('about_stat2_num', '500+'), ('about_stat2_cap', 'Global distributors'),
    ('about_stat3_num', '5'), ('about_stat3_cap', 'Production bases: Wuxi, Tianjin, Dongguan, Thailand, Indonesia'),
    ('about_stat4_num', '3'), ('about_stat4_cap', 'Three branch offices: Shenzhen, Poland, United States'),

    ('whatwedo_heading', 'WHAT WE DO'),
    ('whatwedo_item1_title', 'Motorcycles'),
    ('whatwedo_item1_text', 'Reliable and performance-driven motorcycles designed for everyday mobility.'),
    ('whatwedo_item2_title', 'Electric Vehicles'),
    ('whatwedo_item2_text', 'Smart electric mobility solutions built for efficient, economical, and environmentally conscious travel.'),
    ('whatwedo_item3_title', 'E-Bikes'),
    ('whatwedo_item3_text', 'Modern electric bikes combining practical design, comfort, and effortless everyday riding.'),
    ('whatwedo_item4_title', 'Three-Wheelers'),
    ('whatwedo_item4_text', 'Versatile mobility solutions engineered for both passenger and commercial transportation.'),
    ('whatwedo_item5_title', 'Global Mobility Solutions'),
    ('whatwedo_item5_text', 'A growing international network delivering ZXTec products and mobility solutions to markets around the world.'),

    ('vision_heading', 'VISION'),
    ('vision_text', 'Lorem ipsum dolor sit amet consectetur. Massa donec congue vitae nulla nisi tellus gravida. Eget dolor vulputate malesuada sed morbi sed. Ipsum massa quam elit at ultricies vestibulum. Sagittis etiam risus sagittis sed morbi aliquet integer nunc nibh.'),
    ('mission_heading', 'MISSION'),
    ('mission_text', 'Lorem ipsum dolor sit amet consectetur. Massa donec congue vitae nulla nisi tellus gravida. Eget dolor vulputate malesuada sed morbi sed. Ipsum massa quam elit at ultricies vestibulum. Sagittis etiam risus sagittis sed morbi aliquet integer nunc nibh.'),

    ('about_banner_heading', 'Driven By Innovation'),
    ('about_banner_text', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus.'),

    ('about_cta_heading', 'LOREM IPSUM DOLOR SITUR.'),
    ('about_cta_text', 'Lorem ipsum dolor sit amet consectetur. Vel eget a sem amet leo sollicitudin tellus. Amet nunc urna sed sociis viverra urna hendrerit fringilla.'),
    ('about_cta_button_label', 'Contact us')
ON DUPLICATE KEY UPDATE content_key = content_key;

INSERT INTO images (slot, label, alt_text) VALUES
    ('about_story_image',    'About: Story Image',            'Our story'),
    ('about_stats_bg',       'About: Stats Band Background',  'Stats background'),
    ('about_whatwedo_image', 'About: What We Do Image',       'What we do'),
    ('about_vision_image_1', 'About: Vision/Mission Image 1', 'Vision vehicle'),
    ('about_vision_image_2', 'About: Vision/Mission Image 2', 'Mission vehicle'),
    ('about_banner_image',   'About: Full-width Banner',      'Banner'),
    ('about_world_map',      'About: World Reach Map',        'World map')
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- ---------------------------------------------------------------------------
-- Hero slider slides. If this table is empty, the front end falls back to
-- the single 'hero_scooter' image slot for backward compatibility.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS slides (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    filename    VARCHAR(255) DEFAULT NULL,
    alt_text    VARCHAR(255) DEFAULT '',
    heading     VARCHAR(255) DEFAULT '',
    subheading  VARCHAR(255) DEFAULT '',
    link_url    VARCHAR(255) DEFAULT '',
    spec1_value VARCHAR(64) DEFAULT '',
    spec2_value VARCHAR(64) DEFAULT '',
    spec3_value VARCHAR(64) DEFAULT '',
    spec4_value VARCHAR(64) DEFAULT '',
    spec5_value VARCHAR(64) DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- If the `slides` table already existed before this update, add the new
-- per-slide spec columns (safe to re-run; requires MySQL 8.0.29+ / MariaDB
-- 10.0.2+ for "ADD COLUMN IF NOT EXISTS" — if that errors on your host, run
-- the plain ALTER TABLE ADD COLUMN version once instead).
ALTER TABLE slides
    ADD COLUMN IF NOT EXISTS spec1_value VARCHAR(64) DEFAULT '',
    ADD COLUMN IF NOT EXISTS spec2_value VARCHAR(64) DEFAULT '',
    ADD COLUMN IF NOT EXISTS spec3_value VARCHAR(64) DEFAULT '',
    ADD COLUMN IF NOT EXISTS spec4_value VARCHAR(64) DEFAULT '',
    ADD COLUMN IF NOT EXISTS spec5_value VARCHAR(64) DEFAULT '';

-- ---------------------------------------------------------------------------
-- Product catalog. If this table is empty, the front end falls back to the
-- single 'lineup_vehicles' image slot for backward compatibility.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS products (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(128) NOT NULL,
    description TEXT,
    filename    VARCHAR(255) DEFAULT NULL,
    alt_text    VARCHAR(255) DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Bike models, shown one at a time in the homepage bike carousel. Each bike
-- can have multiple colour variants (see bike_colors below) — clicking a
-- colour swatch swaps the shown image; left/right arrows switch bikes.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bikes (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    slug        VARCHAR(80) NOT NULL UNIQUE,
    name        VARCHAR(128) NOT NULL,
    tagline     VARCHAR(255) DEFAULT '',
    description TEXT,
    spec1_label VARCHAR(64) DEFAULT 'Max Speed',   spec1_value VARCHAR(64) DEFAULT '',
    spec2_label VARCHAR(64) DEFAULT 'Range',        spec2_value VARCHAR(64) DEFAULT '',
    spec3_label VARCHAR(64) DEFAULT 'Weight allow', spec3_value VARCHAR(64) DEFAULT '',
    spec4_label VARCHAR(64) DEFAULT 'Motor',        spec4_value VARCHAR(64) DEFAULT '',
    spec5_label VARCHAR(64) DEFAULT 'Battery',      spec5_value VARCHAR(64) DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------------------------------------------------------------------------
-- Colour variants for each bike. The first colour (lowest sort_order) is
-- used as the bike's default/cover image on the homepage and product page.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS bike_colors (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    bike_id     INT NOT NULL,
    color_name  VARCHAR(64) NOT NULL,
    color_hex   VARCHAR(7) NOT NULL DEFAULT '#161616',
    filename    VARCHAR(255) DEFAULT NULL,
    alt_text    VARCHAR(255) DEFAULT '',
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bike_colors_bike FOREIGN KEY (bike_id) REFERENCES bikes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
