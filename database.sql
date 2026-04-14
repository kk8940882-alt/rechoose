-- RA Beauty & Aesthetic Products Database
-- Run this SQL in your Hostinger MySQL panel

CREATE DATABASE IF NOT EXISTS rabeauty_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rabeauty_db;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description TEXT,
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    short_description TEXT,
    description TEXT,
    features TEXT,
    specifications TEXT,
    price DECIMAL(10,2) DEFAULT NULL,
    image VARCHAR(255),
    image_gallery TEXT,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Admin users table
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(150),
    is_active TINYINT(1) DEFAULT 1,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Contact inquiries
CREATE TABLE IF NOT EXISTS inquiries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(255),
    message TEXT NOT NULL,
    product_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Site settings
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Banners
CREATE TABLE IF NOT EXISTS banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    subtitle TEXT,
    button_text VARCHAR(100),
    button_link VARCHAR(255),
    image VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===== SEED DATA =====

-- Default admin (password: admin@123)
INSERT INTO admin_users (username, email, password, full_name) VALUES
('admin', 'admin@rabeauty.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'RA Beauty Admin');
-- Note: Default password is 'password' - CHANGE IMMEDIATELY after first login

-- Categories
INSERT INTO categories (name, slug, description, sort_order) VALUES
('Hydrafacial Machines', 'hydrafacial-machines', 'Professional HydraFacial devices designed for skin cleansing, hydration and rejuvenation', 1),
('Laser Hair Removal', 'laser-hair-removal', 'Advanced diode laser devices for permanent hair removal with medical grade results', 2),
('Q Switch Laser', 'q-switch-laser', 'Q Switch ND Yag laser systems for tattoo removal, pigmentation and carbon facial', 3),
('Skin Care Devices', 'skin-care-devices', 'Professional skin care and aesthetic treatment devices', 4),
('Hair Care Devices', 'hair-care-devices', 'Advanced hair care and scalp treatment devices', 5),
('Body Slimming', 'body-slimming', 'Body contouring and slimming devices for professional use', 6),
('Beauty Products', 'beauty-products', 'Professional skin and hair care consumable products', 7);

-- Sample Products
INSERT INTO products (category_id, name, slug, short_description, description, features, specifications, is_featured, sort_order) VALUES
(1, 'Alice Bubble Max', 'alice-bubble-max', 'Advanced HydraFacial device with multiple treatment heads for professional skin care', 
'The Alice Bubble Max is a premium HydraFacial machine with comprehensive treatment capabilities including Oxygeno, ultrasound head, high pressure spray, diamond dermabrasion, Hot & cold hammer, Tripolar RF, Bipolar RF and Aqua peel.',
'Treatment heads: Oxygeno, Ultrasound head, High pressure spray, Diamond dermabrasion, Hot & cold hammer, Microlift, Oxygeno, Ultrasound head, Bipolar RF, Cleansing shovel, Skin analyser||Display 10.4 inch||Solution bottle with 4 functions: Deep layer cleansing, Whitening and moisturizing, Deep cleansing, Instrument piping cleansing||Voltage 100-240V, 50 Hz 60Hz||Power 100W',
'Voltage: 100-240V, 50Hz/60Hz||Power: 100W||Display: 10.4 inch touch screen||Solution Bottle: 4 function system',
1, 1),

(1, 'Hydraluxe Aquastar', 'hydraluxe-aquastar', 'Premium skin rejuvenation system with aquapeel and EMS stamp face technology',
'The Hydraluxe Aquastar combines cutting-edge Aquapeel, Oxygen jet, EMS stamp face, Hot & cold hammer, Microlift, Oxygeno, Ultrasound head, Bipolar RF and Cleansing shovel technologies for comprehensive skin treatment.',
'Treatment heads: Aqua peel, Oxygen jet, EMS stamp face, Hot & cold hammer, Microlift, Oxygeno, Ultrasound head, Bipolar RF, Cleansing shovel, Skin analyser||Aquapeel handle pressure 90KPA||Solution bottle with deep layer cleansing||Voltage 100-240V, 50 Hz 60Hz||Power 350W',
'Voltage: 100-240V||Power: 350W||Aquapeel Pressure: 90KPA||Treatment modes: 10+',
1, 2),

(1, 'Oxyrich PDT+', 'oxyrich-pdt', 'Full face skin care system with oxygen infusion and PDT light therapy',
'The Oxyrich PDT+ provides full face skin care including deep cleansing, nutrient import, lifting and tightening, water supplementation and oxygen infusion, soothes skin cell regeneration and restores softness of skin.',
'Treatment heads: Aquapeel, RF roller, Ultrasound head, Skin detection head, Oxygen injector, High pressure water oxygen handle, Oxygen facial mask, Nanospray, Bipolar RF, Cold & Hot hammer||Plasma handle, Oxygen Nasal mask, LED Head (5 different colours)||Working Voltage: 220V/50HZ||Power: 250W',
'Voltage: 220V/50Hz||Power: 250W||LED Head: 5 different colours||Cooling: Yes',
0, 3),

(2, '1200W Diode 4 Wavelength Laser', 'diode-1200w-4-wavelength', 'Professional laser hair removal with 4 wavelengths for all skin types',
'Advanced 1200W Diode laser system featuring 4 wavelengths (755+808+980+1064nm) with 10 laser bars for full body hair removal with superior results.',
'4 wavelengths 755+808+980+1064nm||10 laser bars, 1200W diode handle||Spot size: 4 different spot size including eyebrow tip||Cooling: air + water + semi-conductor||Treatment for full body hair removal',
'Power: 1200W||Wavelengths: 755+808+980+1064nm||Laser Bars: 10||Cooling: Air + Water + Semi-conductor',
1, 1),

(2, 'Epiglow 1200', 'epiglow-1200', 'Advanced 4 wavelength diode laser for complete hair removal',
'The Epiglow 1200 delivers professional grade hair removal with 4 wavelengths, advanced cooling system and various spot size options for all body areas.',
'4 wavelengths 755+808+980+1064nm||10 laser bars, 1200W diode handle||Spot size: 4 different spot sizes including eyebrow tip||Cooling: air + water + semi-conductor||Treatment for full body hair removal',
'Power: 1200W||Wavelengths: 4||Laser Bars: 10||Cooling: Triple cooling system',
0, 2),

(3, 'Portable Q Switch Laser', 'portable-q-switch-laser', 'Compact Q Switch ND Yag laser for tattoo removal and pigmentation treatment',
'The Portable Q Switch Laser is a compact yet powerful device using ND Yag system for effective tattoo removal, carbon facial and freckles removal.',
'Mode: Q switch ND Yag system||Power 500W||Wavelength: 532nm, 755nm, 1064nm||Cooling: air cooled, water cooled||Treatment for all type of tattoo removal, carbon facial, freckles removal',
'Mode: Q Switch ND Yag||Power: 500W||Wavelengths: 532nm, 755nm, 1064nm||Cooling: Air + Water',
0, 1),

(3, 'Elite Pico + Diode Laser System', 'elite-pico-diode-laser', 'High power picosecond laser with diode combination for advanced treatments',
'The Elite Pico + Diode laser system combines 1200W high power with picosecond technology and sapphire cooling for treatment of tattoos, freckles, birthmarks, carbon facial and hair removal.',
'High power: 1200W||Longer life spans up to 20 million shots||Sapphire cooling system in diode handle||Pulse duration is 5-100MS||Picosecond handle has 532nm, 755nm, 1064nm, 532/1064nm combination||Treatment for all type of tattoo, freckles, birthmarks, carbon facial, hair removal',
'Power: 1200W||Shots: 20 million+||Wavelengths: 532nm, 755nm, 1064nm||Pulse Duration: 5-100MS',
1, 2);

-- Site settings
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'RA Beauty & Aesthetic Products'),
('site_tagline', 'Premium Beauty & Aesthetic Equipment'),
('contact_email', 'jakharbhiyaram22@gmail.com'),
('contact_phone', '8079021482, 7726935291'),
('contact_address', 'Shop No. 3-4-16, Opp: Tiwari Clinic, Dr. Bhoomanna Lane, Kachiguda - 500 027'),
('contact_whatsapp', '918079021482'),
('site_about', 'Ramdev Beauty & Aesthetic Products is an importer and marketer of a wide range of Aesthetic Lasers, Massager, Dermatology, Slimming, Skin Laser, Hair Care & Beauty Care Products.'),
('meta_description', 'RA Beauty & Aesthetic Products - Professional beauty and aesthetic equipment for skin care, hair care and body treatment. Importers of Aesthetic Lasers, HydraFacial machines and more.'),
('facebook_url', ''),
('instagram_url', ''),
('whatsapp_url', '');

-- Sample banners
INSERT INTO banners (title, subtitle, button_text, button_link, sort_order) VALUES
('Professional Beauty & Aesthetic Equipment', 'Advanced solutions for skin care, hair removal and body treatments', 'Explore Products', 'products.php', 1),
('HydraFacial Machines', 'Designed for professionals. Delivering exceptional results.', 'View Machines', 'products.php?category=hydrafacial-machines', 2),
('Laser Hair Removal Systems', 'Medical grade diode lasers for permanent hair removal', 'Learn More', 'products.php?category=laser-hair-removal', 3);
