-- =======================================================
-- SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT
-- Database: sapsada_db
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- Sri Lanka Registration No: G A 00282904
-- =======================================================

CREATE DATABASE IF NOT EXISTS `sapsada_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sapsada_db`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ADMIN ROLES
DROP TABLE IF EXISTS `admin_roles`;
CREATE TABLE `admin_roles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE,
    `description` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `admin_roles` (`id`, `role_name`, `description`) VALUES
(1, 'Super Admin', 'Full administrative access to all modules and configurations'),
(2, 'Editor', 'Can create, edit and publish content, activities, and news'),
(3, 'Moderator', 'Can review volunteer applications and contact messages');

-- 2. ADMINS
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL DEFAULT 1,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `full_name` VARCHAR(100) NOT NULL,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `failed_attempts` INT DEFAULT 0,
    `lockout_until` DATETIME NULL,
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `admin_roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default Admin Account: admin / Admin@SapSada2026!
INSERT INTO `admins` (`id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `status`) VALUES
(1, 1, 'admin', 'sapsadaunit@gmail.com', '$2y$10$kTzo2Fw4ZhTPvil8qsgLbOPkMKTnhkoIq.8SRZFv2DxB7PR0WXSdG', 'Sap Sada Executive Administrator', 'active');

-- 3. PERMISSIONS
DROP TABLE IF EXISTS `permissions`;
CREATE TABLE `permissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_id` INT NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `can_view` TINYINT(1) DEFAULT 1,
    `can_create` TINYINT(1) DEFAULT 0,
    `can_edit` TINYINT(1) DEFAULT 0,
    `can_delete` TINYINT(1) DEFAULT 0,
    FOREIGN KEY (`role_id`) REFERENCES `admin_roles`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`role_id`, `module`, `can_view`, `can_create`, `can_edit`, `can_delete`) VALUES
(1, 'all', 1, 1, 1, 1),
(2, 'content', 1, 1, 1, 0),
(2, 'volunteers', 1, 0, 1, 0),
(3, 'volunteers', 1, 0, 1, 0),
(3, 'messages', 1, 0, 1, 0);

-- 4. WEBSITE SETTINGS
DROP TABLE IF EXISTS `website_settings`;
CREATE TABLE `website_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
    `setting_value` TEXT NULL,
    `setting_group` VARCHAR(50) DEFAULT 'general',
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `website_settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT', 'general'),
('site_name_si', 'සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකය', 'general'),
('site_name_ta', 'சப் சத சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்புப் பிரிவு', 'general'),
('reg_number', 'G A 00282904', 'general'),
('slogan', 'Together We Care. Together We Protect Life.', 'general'),
('slogan_si', 'එක්ව රැකවරණය දෙමු. එක්ව ජීවිත සුරකිමු.', 'general'),
('slogan_ta', 'ஒன்றாக அக்கறை கொள்வோம். ஒன்றாக உயிரைப் பாதுகாப்போம்.', 'general'),
('phone_primary', '070 7261978', 'contact'),
('phone_secondary', '072 253 2493', 'contact'),
('phone_hotline', '0777 26 19 78', 'contact'),
('email_official', 'sapsadaunit@gmail.com', 'contact'),
('address_physical', 'Jayabodhi Viharaya, Nalluruwa, Panadura, Sri Lanka', 'contact'),
('address_physical_si', 'ජයබෝධි විහාරය, නල්ලූරුව, පානදුර, ශ්‍රී ලංකාව', 'contact'),
('address_physical_ta', 'ஜயபோதி விகாரை, நல்லூருவ, பாணந்துறை, இலங்கை', 'contact'),
('whatsapp_number', '+94707261978', 'contact'),
('facebook_url', 'https://facebook.com', 'social'),
('youtube_url', 'https://youtube.com', 'social'),
('instagram_url', 'https://instagram.com', 'social'),
('stat_people_supported', '12,500+', 'impact'),
('stat_community_projects', '48+', 'impact'),
('stat_active_volunteers', '350+', 'impact'),
('stat_awareness_campaigns', '85+', 'impact'),
('bank_name', 'Commercial Bank of Ceylon / People\'s Bank', 'bank'),
('bank_account_name', 'SAP SADA SOCIAL WELFARE & LIFE PROTECTION UNIT', 'bank'),
('bank_account_no', 'Pending verification - Please contact office prior to direct deposit', 'bank'),
('bank_branch', 'Panadura Branch', 'bank'),
('bank_swift', 'CCEYLKJA', 'bank'),
('bank_notice', 'Official physical receipt will be issued upon transaction verification.', 'bank'),
('google_map_embed', 'https://maps.google.com/maps?q=Nalluruwa,Panadura,Sri+Lanka&t=&z=14&ie=UTF8&iwloc=&output=embed', 'contact'),
('seo_meta_description', 'Official portal of SAP SADA Social Welfare & Life Protection Unit (Reg: G A 00282904). Dedicated to life protection, community development, emergency welfare, and environmental conservation in Sri Lanka.', 'seo'),
('seo_meta_keywords', 'Sap Sada, Social Welfare, Life Protection, Sri Lanka NGO, Panadura, Community Development, Water Safety, Wetland Protection, Environmental Charity', 'seo');

-- 5. PAGES & PAGE TRANSLATIONS
DROP TABLE IF EXISTS `pages`;
CREATE TABLE `pages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(100) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pages` (`id`, `slug`, `title`) VALUES
(1, 'about-us', 'About Sap Sada Unit'),
(2, 'mission-vision', 'Our Mission & Vision'),
(3, 'registration-compliance', 'Legal Status & Registration');

DROP TABLE IF EXISTS `page_translations`;
CREATE TABLE `page_translations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `page_id` INT NOT NULL,
    `lang` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `content` LONGTEXT NOT NULL,
    `meta_description` TEXT NULL,
    UNIQUE KEY `page_lang` (`page_id`, `lang`),
    FOREIGN KEY (`page_id`) REFERENCES `pages`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `page_translations` (`page_id`, `lang`, `title`, `content`) VALUES
(1, 'en', 'About SAP SADA Social Welfare & Life Protection Unit', '<h3>Who We Are</h3><p>SAP SADA SOCIAL WELFARE &amp; LIFE PROTECTION UNIT (Registration No: G A 00282904) is a non-governmental humanitarian organization established in Sri Lanka with a foundational commitment to public welfare, life preservation, community upliftment, and environmental responsibility.</p><p>Headquartered at Jayabodhi Viharaya, Nalluruwa, our unit works tirelessly on the ground with dedicated volunteers, public rescue responders, local communities, and municipal stakeholders to build a secure, resilient, and compassionate society.</p><h3>Our Strategic Objectives</h3><ul><li>Enhance community safety and aquatic life-saving readiness across coastal and inland waterways.</li><li>Promote public health, environmental hygiene, and wetland restoration through direct collective intervention.</li><li>Empower vulnerable families, youth, and women through skill development, self-employment assistance, and welfare support.</li><li>Provide prompt humanitarian assistance during natural disasters, floods, and personal emergencies.</li><li>Advocate for human rights, civic welfare, and transparent community leadership.</li></ul>'),
(1, 'si', 'සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකය පිළිබඳව', '<h3>අප කවුරුන්ද?</h3><p>සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකය (ලියාපදිංචි අංකය: G A 00282904) යනු මානුෂීය මෙහෙවර, ප්‍රජා සංවර්ධනය, ජීවිතාරක්ෂාව සහ පරිසර සංරක්ෂණය වෙනුවෙන් කැපවී ක්‍රියාත්මක වන ශ්‍රී ලාංකික ස්වේච්ඡා සංවිධානයකි.</p><p>නල්ලූරුව ජයබෝධි විහාරස්ථානය කේන්ද්‍ර කරගනිමින්, අප ඒකකය ග්‍රාමීය හා නාගරික ජනතාවගේ සුභසිද්ධිය, ගංවතුර හා ස්වභාවික ආපදා අවස්ථාවන්හිදී ජීවිත බේරාගැනීම, සහ පරිසර සනීපාරක්ෂාව වෙනුවෙන් අඛණ්ඩව කැපවී සිටී.</p><h3>අපගේ ප්‍රධාන අරමුණු</h3><ul><li>වෙරළබඩ සහ අභ්‍යන්තර ජලාශ්‍රිත ප්‍රදේශවල ජීවිතාරක්ෂණ පුහුණුව හා ජල ආරක්ෂණ සේවා සැපයීම.</li><li>ඇළ මාර්ග පිරිසිදු කිරීම සහ තෙත්බිම් සංරක්ෂණය මගින් පරිසරය රැකගැනීම.</li><li>කාන්තා සහ තරුණ ප්‍රජාව වෙනුවෙන් ස්වයං රැකියා හා අත්වැඩ පුහුණු අවස්ථා සම්පාදනය.</li><li>අවදානමට ලක්වූ පවුල් සහ වැඩිහිටියන් වෙනුවෙන් සමාජ සුභසාධන පහසුකම් සැලසීම.</li></ul>'),
(1, 'ta', 'சப் சத சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்புப் பிரிவு பற்றி', '<h3>நாம் யார்?</h3><p>சப் சத சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்புப் பிரிவு (பதிவு இலக்கம்: G A 00282904) இலங்கையில் மனிதாபிமான பணிகள், உயிர்ப் பாதுகாப்பு, சமூக மேம்பாடு மற்றும் சுற்றாடல் பாதுகாப்புக்காக நிறுவப்பட்ட ஒரு தன்னார்வ அமைப்பாகும்.</p><p>நல்லூருவ ஜயபோதி விகாரையை மையமாகக் கொண்டு இயங்கும் எமது அமைப்பு, இயற்கை அனர்த்தங்களின் போது உடனடி நிவாரணம் வழங்குதல், நீர்நிலைகளில் உயிர்ப் பாதுகாப்பு விழிப்புணர்வு மற்றும் எளிய மக்களின் வாழ்வாதார மேம்பாட்டிற்காக அர்ப்பணிப்புடன் உழைத்து வருகின்றது.</p><h3>எமது நோக்கங்கள்</h3><ul><li>நீர்நிலைகள் மற்றும் கரையோரப் பகுதிகளில் உயிர்ப் பாதுகாப்பு நடவடிக்கைகளை பலப்படுத்துதல்.</li><li>சுற்றாடல் சுகாதாரம் மற்றும் வடிகால் சீரமைப்பு பணிகளை முன்னெடுத்தல்.</li><li>பெண்கள் மற்றும் இளைஞர்களுக்கான சுயதொழில் பயிற்சிகள் மற்றும் வாழ்வாதார உதவி வழங்குதல்.</li><li>அவசர அனர்த்த காலங்களில் மக்களுக்கு துரித மனிதாபிமான உதவிகளை வழங்குதல்.</li></ul>'),
(2, 'en', 'Mission & Vision', '<div class=\"row g-4\"><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-primary h-100\"><h4 class=\"text-primary fw-bold\">Our Mission</h4><p>To foster a humane, resilient, and empowered society by delivering dedicated life protection services, emergency support, environmental preservation, and social welfare programs rooted in transparency and community cooperation.</p></div></div><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-teal h-100\"><h4 class=\"text-teal fw-bold\">Our Vision</h4><p>A thriving Sri Lanka where every human life is cherished and safeguarded, natural aquatic ecosystems are preserved, and every community thrives with dignity, solidarity, and mutual responsibility.</p></div></div></div>'),
(2, 'si', 'අපගේ දැක්ම සහ මෙහෙවර', '<div class=\"row g-4\"><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-primary h-100\"><h4 class=\"text-primary fw-bold\">අපගේ මෙහෙවර</h4><p>විනිවිදභාවය සහ සාමූහික ප්‍රජා දායකත්වය පෙරදැරි කරගනිමින්, ජීවිතාරක්ෂණ සේවා, ආපදා සහන, පරිසර රැකවරණය සහ සමාජ සුභසාධන වැඩසටහන් මගින් ජනතා ජීවිත සුරක්ෂිත කිරීම සහ ශක්තිමත් කිරීම.</p></div></div><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-teal h-100\"><h4 class=\"text-teal fw-bold\">අපගේ දැක්ම</h4><p>සෑම මිනිස් ජීවිතයක්ම ආරක්ෂා වන, සොබාදහම සුරැකෙන, සහ සියලු ප්‍රජාවන් ගෞරවයෙන් සහ සහයෝගයෙන් සපිරි ශ්‍රී ලාංකික අනාගතයක් බිහිකිරීම.</p></div></div></div>'),
(2, 'ta', 'எமது நோக்கும் பணியும்', '<div class=\"row g-4\"><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-primary h-100\"><h4 class=\"text-primary fw-bold\">எமது பணி</h4><p>வெளிப்படைத்தன்மை மற்றும் கூட்டுப் பங்கேற்புடன் உயிர்ப் பாதுகாப்பு, அவசர நிவாரணம், சுற்றாடல் பாதுகாப்பு மற்றும் சமூக நலன்புரி திட்டங்கள் மூலம் எமது சமூகத்தை வலுவூட்டுதல்.</p></div></div><div class=\"col-md-6\"><div class=\"p-4 bg-light rounded-4 border-start border-4 border-teal h-100\"><h4 class=\"text-teal fw-bold\">எமது நோக்கு</h4><p>மனித உயிர்கள் பாதுகாக்கப்பட்டு, இயற்கை நீர்நிலைகள் பாதுகாக்கப்பட்டு, கண்ணியமும் ஒற்றுமையும் கொண்ட ஒரு சிறந்த நாட்டை உருவாக்குதல்.</p></div></div></div>'),
(3, 'en', 'Registration & Legal Compliance', '<div class=\"alert alert-info border-0 rounded-4 p-4\"><h5 class=\"fw-bold\"><i class=\"fas fa-certificate me-2\"></i>Official Registration</h5><p class=\"mb-1\"><strong>Registered Organization Name:</strong> SAP SADA SOCIAL WELFARE &amp; LIFE PROTECTION UNIT</p><p class=\"mb-1\"><strong>Official Registration Number:</strong> G A 00282904</p><p class=\"mb-1\"><strong>Country of Incorporation:</strong> Democratic Socialist Republic of Sri Lanka</p><p class=\"mb-0\"><strong>Administrative Address:</strong> Jayabodhi Viharaya, Nalluruwa, Panadura</p></div><p>We comply fully with national non-profit regulatory principles. Our financial integrity, volunteer safety protocols, and field operations follow strict standards of public accountability.</p>'),
(3, 'si', 'ලියාපදිංචිය සහ නීතිමය පිළිගැනීම', '<div class=\"alert alert-info border-0 rounded-4 p-4\"><h5 class=\"fw-bold\"><i class=\"fas fa-certificate me-2\"></i>නිල ලියාපදිංචි තොරතුරු</h5><p class=\"mb-1\"><strong>නිල නාමය:</strong> සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකය</p><p class=\"mb-1\"><strong>ලියාපදිංචි අංකය:</strong> G A 00282904</p><p class=\"mb-1\"><strong>ලියාපදිංචි රට:</strong> ශ්‍රී ලංකා ප්‍රජාතාන්ත්‍රික සමාජවාදී ජනරජය</p><p class=\"mb-0\"><strong>මූලස්ථාන ලිපිනය:</strong> ජයබෝධි විහාරය, නල්ලූරුව, පානදුර</p></div><p>අප සංවිධානයේ සියලුම ක්‍රියාකාරකම් ජාතික නීති රීති හා මාර්ගෝපදේශයන්ට අනුකූලව ඉතා විනිවිදභාවයකින් යුතුව පවත්වාගෙන යනු ලැබේ.</p>'),
(3, 'ta', 'பதிவு மற்றும் சட்டரீதியான தகவல்', '<div class=\"alert alert-info border-0 rounded-4 p-4\"><h5 class=\"fw-bold\"><i class=\"fas fa-certificate me-2\"></i>அதிகாரப்பூர்வ பதிவு விவரங்கள்</h5><p class=\"mb-1\"><strong>அமைப்பின் பெயர்:</strong> சப் சத சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்புப் பிரிவு</p><p class=\"mb-1\"><strong>பதிவு இலக்கம்:</strong> G A 00282904</p><p class=\"mb-1\"><strong>நாடு:</strong> இலங்கை</p><p class=\"mb-0\"><strong>முகவரி:</strong> ஜயபோதி விகாரை, நல்லூருவ, பாணந்துறை</p></div>');

-- 6. BANNERS
DROP TABLE IF EXISTS `banners`;
CREATE TABLE `banners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `subtitle` TEXT NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `primary_btn_text` VARCHAR(50) DEFAULT 'Discover Our Mission',
    `primary_btn_url` VARCHAR(255) DEFAULT 'about.php',
    `secondary_btn_text` VARCHAR(50) DEFAULT 'Become a Volunteer',
    `secondary_btn_url` VARCHAR(255) DEFAULT 'volunteers.php',
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `banners` (`id`, `title`, `subtitle`, `image_path`, `primary_btn_text`, `primary_btn_url`, `secondary_btn_text`, `secondary_btn_url`, `sort_order`, `is_active`) VALUES
(1, 'Together We Care. Together We Protect Life.', 'Working together to create a caring, responsible and protected society through social welfare and community initiatives in Sri Lanka.', 'assets/images/branding/banner-en.png', 'Discover Our Mission', 'about.php', 'Become a Volunteer', 'volunteers.php', 1, 1),
(2, 'Lifesaving & Water Safety Protection Unit', 'Empowering communities through expert swimming safety, emergency coastal response, and active life protection.', 'assets/images/activities/swim-protection.jpg', 'View Our Activities', 'activities.php', 'Join As Volunteer', 'volunteers.php', 2, 1),
(3, 'Ecological Defense & Wetland Cleaning Missions', 'Restoring vital drainage canals, protecting ecosystems, and advancing plastic-free community stewardship.', 'assets/images/activities/canal-cleanup-1.jpg', 'Explore Projects', 'projects.php', 'Support Our Work', 'donate.php', 3, 1);

-- 7. ACTIVITIES & TRANSLATIONS
DROP TABLE IF EXISTS `activities`;
CREATE TABLE `activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `featured_image` VARCHAR(255) NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `activity_date` DATE NOT NULL,
    `status` ENUM('active', 'upcoming', 'completed') DEFAULT 'active',
    `is_featured` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `activity_translations`;
CREATE TABLE `activity_translations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `activity_id` INT NOT NULL,
    `lang` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    `meta_keywords` VARCHAR(255) NULL,
    UNIQUE KEY `act_lang` (`activity_id`, `lang`),
    FOREIGN KEY (`activity_id`) REFERENCES `activities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `activities` (`id`, `category`, `slug`, `featured_image`, `location`, `activity_date`, `status`, `is_featured`) VALUES
(1, 'Life Protection Awareness', 'swim-for-your-life-protection-challenge', 'assets/images/activities/swim-protection.jpg', 'Lighthouse Beach & Coastal Waters', '2026-03-15', 'completed', 1),
(2, 'Environmental Activities', 'panadura-canal-dredging-and-wetland-cleanup', 'assets/images/activities/canal-cleanup-1.jpg', 'Panadura Canal & Waterways', '2026-04-10', 'active', 1),
(3, 'Community Development', 'women-handicraft-self-employment-empowerment', 'assets/images/activities/community-crafts-1.jpg', 'Jayabodhi Community Hall, Nalluruwa', '2026-05-02', 'active', 1),
(4, 'Public Awareness Campaigns', 'human-rights-and-public-service-excellence-forum', 'assets/images/activities/awards-ceremony.jpg', 'National Conference Hall', '2026-06-20', 'completed', 1),
(5, 'Social Welfare Programs', 'flood-relief-and-vulnerable-family-aid', 'assets/images/activities/canal-cleanup-2.jpg', 'Kalutara & Western Province', '2026-07-14', 'active', 1),
(6, 'Emergency Assistance', 'rapid-water-rescue-volunteer-training', 'assets/images/activities/swim-protection.jpg', 'Panadura Estuary', '2026-08-01', 'upcoming', 1);

INSERT INTO `activity_translations` (`activity_id`, `lang`, `title`, `description`, `meta_keywords`) VALUES
(1, 'en', '\"Swim For Your Life\" Water Safety & Life Protection Challenge', '<p>Our premier aquatic life-saving initiative brought together professional swimmers, community volunteers, and emergency first responders at Lighthouse Beach to demonstrate vital open-water survival techniques, safe swimming guidelines, and drowning prevention skills.</p><p>Participants engaged in long-distance swimming endurance drills and open-sea safety protocols to spread life protection awareness among youth and fishermen.</p>', 'swim safety, life protection, coastal rescue'),
(1, 'si', 'ජීවිතාරක්ෂණ හා ජල ආරක්ෂණ පිහිනුම් වැඩසටහන', '<p>වෙරළබඩ හා ජලාශ්‍රිත ප්‍රදේශවල ජනතාවගේ සහ තරුණ පරපුරේ ජල ආරක්ෂාව තහවුරු කරනු වස් සංවිධානය කරන ලද සුවිශේෂී ජීවිතාරක්ෂණ පුහුණු හා පිහිනුම් දැනුවත් කිරීමේ වැඩසටහනකි.</p>', 'ජල ආරක්ෂණය, ජීවිතාරක්ෂක, සප් සඳ'),
(1, 'ta', '\"உங்கள் உயிருக்காக நீந்துங்கள்\" உயிர்ப் பாதுகாப்பு விழிப்புணர்வு', '<p>கரையோர பகுதிகளில் நீர் பாதுகாப்பு மற்றும் உயிர்ப் பாதுகாப்பு விழிப்புணர்வை ஏற்படுத்தும் நோக்கில் நடாத்தப்பட்ட விசேட நீச்சல் மற்றும் அவசர பாதுகாப்பு நிகழ்ச்சித்திட்டம்.</p>', 'உயிர்ப் பாதுகாப்பு, நீச்சல் விழிப்புணர்வு'),

(2, 'en', 'Canal Ecosystem Restoration & Heavy Dredging Initiative', '<p>To combat localized flooding and protect public health, SAP SADA mobilized heavy machinery (excavators and dumpers) alongside community volunteers to clear clogged aquatic vegetation, weeds, and discarded plastic from regional drainage canals.</p><p>This initiative directly alleviated stagnant water risks and restored ecological water flow across agricultural and residential basins.</p>', 'canal cleaning, heavy dredging, flood prevention'),
(2, 'si', 'ඇළ මාර්ග සහ තෙත්බිම් සංරක්ෂණ මහා පරිසර වැඩසටහන', '<p>ප්‍රදේශයේ ගංවතුර අවදානම අවම කිරීම සහ ඩෙංගු ආදී රෝග ව්‍යාප්තිය පාලනය කිරීම උදෙසා යන්ත්‍රෝපකරණ යොදාගනිමින් ඇළ මාර්ග වල රොන්මඩ සහ ජලජ පැලෑටි ඉවත් කිරීමේ පුළුල් පරිසර සත්කාරක මෙහෙයුමකි.</p>', 'ඇළ මාර්ග පිරිසිදු කිරීම, පරිසර සංරක්ෂණය'),
(2, 'ta', 'வடிகால் சீரமைப்பு மற்றும் சுற்றாடல் பாதுகாப்பு பணித்திட்டம்', '<p>வெள்ளப் பெருக்கு அபாயத்தை குறைக்கவும், கழிவுப் பொருட்களை அகற்றவும் கனரக இயந்திரங்கள் மற்றும் தன்னார்வலர்களின் உதவியுடன் முன்னெடுக்கப்பட்ட வடிகால் தூய்மையாக்கல் நடவடிக்கை.</p>', 'வடிகால் சீரமைப்பு, சுற்றாடல்'),

(3, 'en', 'Women Community Enterprise & Eco-Craft Empowerment', '<p>Providing sustainable livelihoods through indigenous handicraft manufacturing, woven natural fiber bags, and eco-friendly products. SAP SADA equips local women with entrepreneurship training, toolsets, and commercial showcase opportunities.</p>', 'women empowerment, handicrafts, sustainable livelihood'),
(3, 'si', 'කාන්තා ප්‍රජා සවිබලගැන්වීම සහ පරිසර හිතකාමී අත්කම් නිර්මාණ පුහුණුව', '<p>ස්වභාවික අමුද්‍රව්‍ය ආශ්‍රිත අත්කම් බෑග් සහ නිපැයුම් හරහා ග්‍රාමීය කාන්තාවන්ගේ ආර්ථිකය ශක්තිමත් කිරීමේ ස්වයං රැකියා ව්‍යාපෘතිය.</p>', 'කාන්තා සවිබලගැන්වීම, අත්කම්'),
(3, 'ta', 'பெண்கள் சமூக தொழில்முயற்சி மற்றும் கைவினைப் பயிற்சி திட்டம்', '<p>சுயதொழில் மற்றும் சுற்றாடல் நட்பு கைவினைப் பொருட்கள் தயாரிப்பு மூலம் எமது பகுதி தாய்மார்கள் மற்றும் யுவதிகளின் பொருளாதாரத்தை வலுப்படுத்தும் திட்டம்.</p>', 'பெண்கள் முன்னேற்றம், சுயதொழில்'),

(4, 'en', 'Human Rights & Public Welfare Excellence Recognition', '<p>SAP SADA leadership and field volunteers were honored at the National Human Rights Conference for steadfast dedication to civic welfare, water rescue readiness, and unyielding humanitarian service to underprivileged communities.</p>', 'human rights, awards, NGO recognition'),
(4, 'si', 'මානව හිමිකම් හා සමාජ සුභසාධන විශිෂ්ටතා ඇගයීම් සම්මාන උළෙල', '<p>සමාජ සුභසාධනය සහ ප්‍රජා සේවාව උදෙසා සප් සඳ ඒකකයේ නායකත්වය සහ ස්වේච්ඡා කණ්ඩායම් දැක්වූ කැපවීම ජාතික තලයේදී ඇගයීමට ලක්වීම.</p>', 'මානව හිමිකම්, සම්මාන'),
(4, 'ta', 'மனித உரிமைகள் மற்றும் பொது நலன்புரி மாநாடு மற்றும் விருது வழங்கல்', '<p>சமூக நலன்புரி மற்றும் உயிர்ப் பாதுகாப்பு பணிகளுக்காக சப் சத அமைப்பின் உழைப்பைப் பாராட்டி வழங்கப்பட்ட கௌரவ விருது நிகழ்வு.</p>', 'மனித உரிமைகள், விருதுகள்'),

(5, 'en', 'Rapid Emergency Relief & Vulnerable Family Food Aid', '<p>Mobilizing vital rations, drinking water, and dry provisions to low-income families and elderly community members affected by severe seasonal weather and economic hardships.</p>', 'emergency relief, food aid, community welfare'),
(5, 'si', 'හදිසි ආපදා සහන සහ අවදානමට ලක්වූ පවුල් සඳහා වියලි ආහාර සලාක බෙදාදීම', '<p>අයහපත් කාලගුණයෙන් සහ ආර්ථික දුෂ්කරතාවලින් පීඩාවට පත් පවුල් වෙනුවෙන් අත්‍යවශ්‍ය ද්‍රව්‍ය බෙදාදීමේ මානුෂීය වැඩසටහන.</p>', 'ආපදා සහන, සමාජ සේවා'),
(5, 'ta', 'அவசர நிவாரணம் மற்றும் எளிய குடும்பங்களுக்கான உலர் உணவு உதவி', '<p>கடினமான சூழல்களில் பாதிக்கப்பட்ட வறிய குடும்பங்களுக்கு உணவு மற்றும் அத்தியாவசிய பொருட்கள் வழங்கும் திட்டம்.</p>', 'அவசர நிவாரணம், உலர் உணவு'),

(6, 'en', 'Certified Life Guard & Coastal Rescue Volunteer Training', '<p>Specialized physical drills, cardiopulmonary resuscitation (CPR) education, and water rescue equipment handling for youth volunteers seeking to safeguard local beaches.</p>', 'lifeguard training, rescue volunteer, CPR'),
(6, 'si', 'ජීවිතාරක්ෂක හා ප්‍රථමාධාර පුහුණු වැඩමුළුව', '<p>තරුණ ස්වේච්ඡා සාමාජිකයින් සඳහා නොමිලේ පවත්වනු ලබන ජල මුදවාගැනීම් සහ ප්‍රථමාධාර සහතිකපත්‍ර පාඨමාලාව.</p>', 'ජීවිතාරක්ෂක පුහුණුව, ප්‍රථමාධාර'),
(6, 'ta', 'உயிர்ப் பாதுகாப்பு மற்றும் முதலுதவி பயிற்சிப் பட்டறை', '<p>இளைஞர்களுக்கான உயிர்ப் பாதுகாப்பு, அவசர முதலுதவி மற்றும் நீர் மீட்பு நுட்பங்கள் குறித்த விசேட பயிற்சி.</p>', 'உயிர்ப் பாதுகாப்பு பயிற்சி');

-- 8. PROJECTS & TRANSLATIONS
DROP TABLE IF EXISTS `projects`;
CREATE TABLE `projects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `category` VARCHAR(100) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `featured_image` VARCHAR(255) NOT NULL,
    `location` VARCHAR(150) NOT NULL,
    `start_date` DATE NOT NULL,
    `end_date` DATE NULL,
    `status` ENUM('Planned', 'Ongoing', 'Completed', 'Archived') DEFAULT 'Ongoing',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `project_translations`;
CREATE TABLE `project_translations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `project_id` INT NOT NULL,
    `lang` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` LONGTEXT NOT NULL,
    UNIQUE KEY `proj_lang` (`project_id`, `lang`),
    FOREIGN KEY (`project_id`) REFERENCES `projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `projects` (`id`, `category`, `slug`, `featured_image`, `location`, `start_date`, `end_date`, `status`) VALUES
(1, 'Life Protection', 'coastal-and-lagoon-aquatic-safety-initiative', 'assets/images/activities/swim-protection.jpg', 'Western Province Coastline', '2026-01-10', NULL, 'Ongoing'),
(2, 'Environmental Conservation', 'wetland-waterways-and-canal-ecological-cleaning', 'assets/images/activities/canal-cleanup-1.jpg', 'Panadura & Nalluruwa Waterways', '2026-02-01', NULL, 'Ongoing'),
(3, 'Livelihood Empowerment', 'women-handicrafts-and-home-industry-incubation', 'assets/images/activities/community-crafts-1.jpg', 'Nalluruwa Community Center', '2025-11-01', NULL, 'Ongoing'),
(4, 'Disaster Readiness', 'emergency-flood-response-boat-and-rescue-unit', 'assets/images/activities/canal-cleanup-3.jpg', 'Low-Lying Riverine Areas', '2026-07-01', '2026-12-31', 'Planned'),
(5, 'Community Health', 'clean-water-wells-and-dengue-eradication-drive', 'assets/images/activities/canal-cleanup-2.jpg', 'Urban Settlements & Schools', '2025-08-01', '2025-12-15', 'Completed');

INSERT INTO `project_translations` (`project_id`, `lang`, `title`, `description`) VALUES
(1, 'en', 'Coastal & Lagoon Aquatic Safety Initiative', '<p>A multi-phase life protection initiative deploying warning signage, lifebuoy rings, and trained volunteer patrol units along high-risk swimming and bathing spots to eliminate preventable drownings.</p>'),
(1, 'si', 'වෙරළබඩ සහ කලපු ජල ආරක්ෂණ ව්‍යාපෘතිය', '<p>අනතුරුදායක ජලාශ්‍රිත ස්ථානවල අනතුරු ඇඟවීමේ පුවරු, ජීවිතාරක්ෂක වළලු සවිකිරීම සහ මුර සංචාර පැවැත්වීම මගින් ජලයේ ගිලීම් වැළැක්වීමේ දීර්ඝකාලීන ව්‍යාපෘතිය.</p>'),
(1, 'ta', 'கரையோர மற்றும் களப்பு நீர் பாதுகாப்பு திட்டம்', '<p>ஆபத்தான நீர்நிலைகளில் எச்சரிக்கைப் பலகைகள், உயிர்காக்கும் மிதவைகள் மற்றும் கண்காணிப்பு பணிகளை மேற்கொண்டு உயிர்களைப் பாதுகாக்கும் திட்டம்.</p>'),

(2, 'en', 'Wetland Waterways & Canal Ecological Revival', '<p>Deploying heavy hydraulic excavators and agricultural tractor units to dredge silted canals, clean dense water-hyacinth growth, and eliminate blocked waterways causing monsoon urban flooding.</p>'),
(2, 'si', 'තෙත්බිම් සහ ඇළ මාර්ග පාරිසරික පුනර්ජීවන ව්‍යාපෘතිය', '<p>භූමි ගිලීයාම් සහ නාගරික ගංවතුර පාලනය කිරීම වෙනුවෙන් දිගුකාලීනව ක්‍රියාත්මක වන ඇළ මාර්ග ප්‍රතිසංස්කරණ ව්‍යාපෘතිය.</p>'),
(2, 'ta', 'ஈரநில வடிகால் மற்றும் சுற்றாடல் புனரமைப்பு திட்டம்', '<p>நகர்ப்புற வெள்ள அபாயங்களை தடுத்து சுற்றாடல் சமநிலையை பேண வடிகால்களை ஆழப்படுத்தி சீரமைக்கும் தொடர் திட்டம்.</p>'),

(3, 'en', 'Women Community Crafts & Self-Employment Support', '<p>Equipping women with traditional handloom, rush-and-reed weaving skills, and market access to manufacture plastic-free eco bags and consumer goods, generating independent household income.</p>'),
(3, 'si', 'කාන්තා අත්කම් සහ ස්වයං රැකියා ප්‍රවර්ධන ව්‍යාපෘතිය', '<p>පරිසර හිතකාමී නිපැයුම් හරහා කාන්තා ව්‍යවසායකත්වය නගාසිටුවීම සහ පවුලේ ආර්ථිකය ස්ථාවර කිරීමේ වැඩසටහන.</p>'),
(3, 'ta', 'பெண்கள் கைவினை மற்றும் சுயதொழில் மேம்பாட்டு திட்டம்', '<p>பாரம்பரிய கைவினைப் பொருட்கள் தயாரிப்பு மூலம் தாய்மார்களின் வாழ்வாதாரத்தை உயர்த்தும் திட்டம்.</p>'),

(4, 'en', 'Emergency Rapid Response Life Protection Unit', '<p>Procuring specialized inflatable rescue boats, emergency radios, and all-weather medical kits to assist municipal disaster response teams in heavy monsoon flood inundations.</p>'),
(4, 'si', 'හදිසි ගංවතුර මුදවාගැනීමේ සහ ජීවිතාරක්ෂණ යාත්‍රා ඒකකය', '<p>ආපදා අවස්ථාවලදී ක්ෂණිකව ක්‍රියාත්මක විය හැකි මුදවාගැනීමේ බෝට්ටු සහ උපකරණ සහිත ස්වේච්ඡා ඒකකයක් ස්ථාපිත කිරීමේ සැලසුම්ගත ව්‍යාපෘතිය.</p>'),
(4, 'ta', 'அவசர வெள்ள நிவாரணம் மற்றும் உயிர்ப் பாதுகாப்பு படகுப் பிரிவு', '<p>அனர்த்த காலங்களில் மக்களுக்கு உடனடி உதவி வழங்கும் வகையில் படகுகள் மற்றும் உபகரணங்களுடன் கூடிய பிரிவை அமைக்கும் திட்டம்.</p>'),

(5, 'en', 'Clean Water & Vector-Borne Disease Prevention Drive', '<p>Successfully cleaned and disinfected community wells, cleared mosquito breeding puddles across schools, and distributed mosquito prevention netting to low-income elders.</p>'),
(5, 'si', 'පිරිසිදු පානීය ජල ළිං සහ ඩෙංගු මර්දන ප්‍රජා ව්‍යාපෘතිය', '<p>ප්‍රජා ළිං පිරිසිදු කර ක්ලෝරීනකරණය කිරීම සහ ඩෙංගු අවදානම් කලාප පිරිසිදු කිරීමේ සාර්ථකව අවසන් කළ ව්‍යාපෘතිය.</p>'),
(5, 'ta', 'சுத்தமான குடிநீர் மற்றும் தொற்றுநோய் தடுப்பு திட்டம்', '<p>பாடசாலைகள் மற்றும் கிராமப்புற கிணறுகளை தூய்மைப்படுத்தி நோய்த்தடுப்பு நடவடிக்கைகளை முன்னெடுத்த நிறைவடைந்த திட்டம்.</p>');

-- 9. VOLUNTEERS & NOTES
DROP TABLE IF EXISTS `volunteers`;
CREATE TABLE `volunteers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `reference_no` VARCHAR(30) NOT NULL UNIQUE,
    `full_name` VARCHAR(150) NOT NULL,
    `dob` DATE NULL,
    `nic_passport` VARCHAR(50) NULL,
    `gender` ENUM('Male', 'Female', 'Other', 'Prefer not to say') DEFAULT 'Male',
    `phone` VARCHAR(30) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `address` TEXT NOT NULL,
    `district` VARCHAR(50) NOT NULL,
    `occupation` VARCHAR(100) NULL,
    `skills` TEXT NULL,
    `areas_of_interest` VARCHAR(255) NULL,
    `availability` VARCHAR(100) NULL,
    `previous_experience` TEXT NULL,
    `message` TEXT NULL,
    `privacy_accepted` TINYINT(1) DEFAULT 1,
    `status` ENUM('Pending', 'Approved', 'Rejected', 'Contacted') DEFAULT 'Pending',
    `admin_remarks` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `volunteers` (`reference_no`, `full_name`, `dob`, `nic_passport`, `gender`, `phone`, `email`, `address`, `district`, `occupation`, `skills`, `areas_of_interest`, `availability`, `previous_experience`, `status`) VALUES
('SAP-VOL-2026-0001', 'Kasun Bandara Perera', '1998-05-14', '199813500214', 'Male', '0771234567', 'kasun.p@gmail.com', 'No 45, Galle Road, Panadura', 'Kalutara', 'Lifeguard / Sports Instructor', 'Swimming, First Aid, CPR, Water Safety', 'Life Protection, Emergency Support', 'Weekends and Emergency On-call', '3 years certified coastal rescue', 'Approved'),
('SAP-VOL-2026-0002', 'Nilanthi Kumari Jayasuriya', '1992-11-20', '927541200V', 'Female', '0719876543', 'nilanthi.j@yahoo.com', 'Temple Road, Nalluruwa', 'Kalutara', 'Community Teacher', 'Handicraft training, event coordination', 'Community Development, Education Support', 'Evenings & Weekends', 'School welfare club volunteer', 'Contacted');

DROP TABLE IF EXISTS `volunteer_notes`;
CREATE TABLE `volunteer_notes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `volunteer_id` INT NOT NULL,
    `admin_id` INT NOT NULL,
    `note` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `volunteer_notes` (`volunteer_id`, `admin_id`, `note`) VALUES
(1, 1, 'Verified national lifeguard credentials. Excellent candidate for coastal team lead.'),
(2, 1, 'Spoke via telephone on Nov 2nd. She is interested in organizing weekend handicrafts workshops.');

-- 10. DONATION CAMPAIGNS & ENQUIRIES
DROP TABLE IF EXISTS `donation_campaigns`;
CREATE TABLE `donation_campaigns` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `target_amount` DECIMAL(12,2) DEFAULT 0.00,
    `raised_amount` DECIMAL(12,2) DEFAULT 0.00,
    `image_path` VARCHAR(255) NOT NULL,
    `status` ENUM('active', 'completed', 'paused') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `donation_campaigns` (`id`, `title`, `slug`, `description`, `target_amount`, `raised_amount`, `image_path`, `status`) VALUES
(1, 'Coastal Life Protection Equipment & Safety Stations', 'coastal-life-protection-safety-stations', 'Support the procurement of certified lifebuoy rings, safety towers, rescue paddle boards, and high-visibility water signage to safeguard citizens along Sri Lankan beaches.', 500000.00, 185000.00, 'assets/images/activities/swim-protection.jpg', 'active'),
(2, 'Canal Heavy Dredging & Flood Mitigation Fund', 'canal-heavy-dredging-flood-mitigation', 'Fund machine operating hours, fuel, and ecological waste disposal for clearing critical storm drainage canals to prevent monsoonal flood damage to low-income settlements.', 750000.00, 320000.00, 'assets/images/activities/canal-cleanup-1.jpg', 'active'),
(3, 'Women Community Handicraft Tools & Raw Materials', 'women-community-handicraft-tools', 'Provide hand-weaving looms, eco-friendly dyed natural fibers, and sewing kits to low-income mothers starting home craft enterprises.', 300000.00, 140000.00, 'assets/images/activities/community-crafts-1.jpg', 'active');

DROP TABLE IF EXISTS `donation_enquiries`;
CREATE TABLE `donation_enquiries` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `donor_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NOT NULL,
    `campaign_id` INT NULL,
    `amount_declared` DECIMAL(12,2) NULL,
    `transfer_reference` VARCHAR(100) NULL,
    `payment_method` VARCHAR(50) DEFAULT 'Bank Transfer',
    `message` TEXT NULL,
    `status` ENUM('Pending', 'Verified', 'Cancelled') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`campaign_id`) REFERENCES `donation_campaigns`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `donation_enquiries` (`donor_name`, `email`, `phone`, `campaign_id`, `amount_declared`, `transfer_reference`, `message`, `status`) VALUES
('Sampath Wickramasinghe', 'sampath.w@gmail.com', '0773344556', 1, 15000.00, 'REF-BOC-998822', 'Transfer made to BOC account for water safety equipment. Please confirm receipt.', 'Verified');

-- 11. NEWS & NEWS TRANSLATIONS
DROP TABLE IF EXISTS `news`;
CREATE TABLE `news` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `category` VARCHAR(100) DEFAULT 'General News',
    `featured_image` VARCHAR(255) NOT NULL,
    `published_at` DATE NOT NULL,
    `status` ENUM('Draft', 'Published') DEFAULT 'Published',
    `views` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `news_translations`;
CREATE TABLE `news_translations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `news_id` INT NOT NULL,
    `lang` VARCHAR(5) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `summary` TEXT NOT NULL,
    `content` LONGTEXT NOT NULL,
    UNIQUE KEY `news_lang` (`news_id`, `lang`),
    FOREIGN KEY (`news_id`) REFERENCES `news`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `news` (`id`, `slug`, `category`, `featured_image`, `published_at`, `status`, `views`) VALUES
(1, 'sap-sada-honored-at-national-human-rights-conference', 'Awards & Recognition', 'assets/images/activities/awards-ceremony.jpg', '2026-06-25', 'Published', 142),
(2, 'major-canal-clearance-operation-safeguards-500-families', 'Community Impact', 'assets/images/activities/canal-cleanup-1.jpg', '2026-04-18', 'Published', 215),
(3, 'swim-for-your-life-campaign-expands-along-coastal-districts', 'Life Protection', 'assets/images/activities/swim-protection.jpg', '2026-03-22', 'Published', 188);

INSERT INTO `news_translations` (`news_id`, `lang`, `title`, `summary`, `content`) VALUES
(1, 'en', 'SAP SADA Honored at National Human Rights & Public Service Conference', 'Our organization was awarded prestigious national recognition for steadfast dedication to community welfare and water safety.', '<p>At the prestigious Human Rights Conference and Excellence Awards, SAP SADA Social Welfare &amp; Life Protection Unit was honored with a Certificate of Honor and Excellence Trophy. The award celebrates the relentless commitment of our ground rescue teams, welfare officers, and volunteers who dedicate their days to protecting lives and uplifting vulnerable families.</p>'),
(1, 'si', 'ජාතික මානව හිමිකම් සහ සමාජ සේවා විශිෂ්ටතා සම්මානයෙන් සප් සඳ ඒකකය පිදුම් ලබයි', 'සමාජ සුභසාධනය සහ ප්‍රජා සේවාව උදෙසා අප ඒකකය දැක්වූ සුවිශේෂී දායකත්වය ජාතික සම්මානයෙන් ඇගයීමට ලක්විය.', '<p>ජාතික මානව හිමිකම් සම්මන්ත්‍රණයේදී සප් සඳ සමාජ සුභ සාධන හා ජීවිතාරක්ෂණ ඒකකයේ සේවාවන් ඇගයීමට ලක් කරමින් විශිෂ්ටතා සම්මානය පිරිනමන ලදී.</p>'),
(1, 'ta', 'தேசிய மனித உரிமைகள் மற்றும் சமூக சேவைக்கான கௌரவ விருதை வென்றது சப் சத', 'சமூக மேம்பாடு மற்றும் உயிர்ப் பாதுகாப்பு பணிகளுக்காக எமது அமைப்புக்கு தேசிய ரீதியிலான கௌரவ விருது வழங்கப்பட்டது.', '<p>தேசிய மனித உரிமைகள் மாநாட்டில் சப் சத அமைப்பின் களப்பணியாளர்களின் தன்னலமற்ற சேவையைப் பாராட்டி கௌரவ விருது மற்றும் சான்றிதழ் வழங்கி கௌரவிக்கப்பட்டது.</p>'),

(2, 'en', 'Major Canal Clearance Operation Safeguards Over 500 Families from Floods', 'Heavy hydraulic excavators and community volunteers clear critical waterway chokepoints in Panadura.', '<p>With the onset of seasonal downpours, our unit coordinated an intensive drainage clearing mission. By removing dozens of truckloads of wild aquatic vegetation and silt, natural storm runoff was successfully restored, safeguarding over 500 homes from destructive floods.</p>'),
(2, 'si', 'ඇළ මාර්ග පිරිසිදු කිරීමෙන් පවුල් 500කට අධික සංඛ්‍යාවක් ගංවතුර අවදානමෙන් මුදාගැනේ', 'පානදුර අවට ඇළ මාර්ග වල අවහිරතා ඉවත් කර ජල ගැලීම් අවම කිරීමේ විශේෂ මෙහෙයුම සාර්ථකව අවසන් වේ.', '<p>යන්ත්‍රෝපකරණ සහ ස්වේච්ඡා සාමාජිකයින්ගේ දායකත්වයෙන් ජල මාර්ග පිරිසිදු කර නිවාස 500කට අධික සංඛ්‍යාවකට ආරක්ෂාව සලසා දෙන ලදී.</p>'),
(2, 'ta', 'பாரிய வடிகால் சீரமைப்பு: 500 க்கும் மேற்பட்ட குடும்பங்கள் வெள்ளத்தில் இருந்து பாதுகாப்பு', 'கனரக இயந்திரங்கள் மூலம் கழிவுகள் அகற்றப்பட்டு குடியிருப்பு பகுதிகளுக்கு வெள்ள அபாயம் நீக்கப்பட்டது.', '<p>எமது அமைப்பின் ஏற்பாட்டில் மேற்கொள்ளப்பட்ட பாரிய தூய்மைப்படுத்தல் பணிகள் மூலம் 500 க்கும் மேற்பட்ட குடும்பங்களின் வீடுகள் வெள்ள நீரில் மூழ்குவதில் இருந்து காப்பாற்றப்பட்டுள்ளன.</p>'),

(3, 'en', '\"Swim For Your Life\" Open-Water Safety Challenge Inspires Youth', 'Over 100 participants join coastal lifesaving drills and endurance swimming challenge.', '<p>The \"Swim For Your Life\" challenge concluded with widespread praise from maritime stakeholders. Experienced rescue divers coached local youth in water safety, current identification, and emergency survival techniques.</p>'),
(3, 'si', '\"ජීවිතය වෙනුවෙන් පිහිනන්න\" ජල ආරක්ෂණ වැඩමුළුව සාර්ථකව නිමාවට පත්වේ', 'තරුණ ප්‍රජාව සහ පිහිනුම් ශිල්පීන් රැසකගේ සහභාගීත්වයෙන් වෙරළබඩ ජීවිතාරක්ෂණ පෙරහුරුවක්.', '<p>ජලයේ සිදුවන අනතුරු වළක්වාගැනීම සඳහා තරුණ තරුණියන් 100කට අධික පිරිසකට පිහිනුම් හා ජීවිතාරක්ෂණ පුහුණුව ලබාදෙන ලදී.</p>'),
(3, 'ta', '\"உயிரைக் காக்க நீந்துங்கள்\" இளைஞர்களுக்கான விசேட உயிர்ப் பாதுகாப்பு பயிற்சி', '100க்கும் மேற்பட்டோர் பங்கேற்ற கரையோர உயிர்ப் பாதுகாப்பு மற்றும் அவசர முதலுதவி விழிப்புணர்வு நிகழ்வு.', '<p>கடல் மற்றும் ஆறுகளில் ஏற்படும் விபத்துக்களைத் தடுக்கும் நோக்கில் இளைஞர்களுக்கு விரிவான பயிற்சிகள் வழங்கப்பட்டன.</p>');

-- 12. EVENTS
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `description` LONGTEXT NOT NULL,
    `event_date` DATE NOT NULL,
    `start_time` TIME NOT NULL,
    `end_time` TIME NOT NULL,
    `venue` VARCHAR(255) NOT NULL,
    `featured_image` VARCHAR(255) NOT NULL,
    `registration_open` TINYINT(1) DEFAULT 1,
    `status` ENUM('Upcoming', 'Completed', 'Cancelled') DEFAULT 'Upcoming',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `events` (`id`, `title`, `slug`, `description`, `event_date`, `start_time`, `end_time`, `venue`, `featured_image`, `registration_open`, `status`) VALUES
(1, 'Community Life Protection & CPR Certification Workshop', 'community-life-protection-cpr-workshop', 'Hands-on practical training covering drowning intervention, CPR resuscitation, water rescue throw-bag operation, and first responder triage. Free registration for volunteers and youth.', '2026-11-28', '08:30:00', '13:30:00', 'Jayabodhi Community Hall, Nalluruwa', 'assets/images/activities/swim-protection.jpg', 1, 'Upcoming'),
(2, 'Mega Coastal Plastic-Free Cleanup & Mangrove Tree Planting', 'mega-coastal-plastic-free-cleanup', 'Join 200+ volunteers in our \"Face the Way - Plastic Free\" ecological beach sweep and native tree planting campaign to safeguard coastal marine biodiversity.', '2026-12-12', '07:00:00', '12:00:00', 'Panadura Coastal Beachfront', 'assets/images/activities/canal-cleanup-2.jpg', 1, 'Upcoming'),
(3, 'Annual Social Welfare & Family Support Aid Distribution', 'annual-social-welfare-aid-distribution', 'Annual ceremonial distribution of educational stationery packs to underprivileged students and nutritional care packages for senior citizens.', '2026-12-22', '09:00:00', '14:00:00', 'Jayabodhi Viharaya Grounds', 'assets/images/activities/community-crafts-2.jpg', 1, 'Upcoming');

-- 13. GALLERY ALBUMS & IMAGES
DROP TABLE IF EXISTS `gallery_albums`;
CREATE TABLE `gallery_albums` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(150) NOT NULL,
    `slug` VARCHAR(150) NOT NULL UNIQUE,
    `category` VARCHAR(100) NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery_albums` (`id`, `title`, `slug`, `category`, `description`) VALUES
(1, 'Swim For Your Life Protection Challenge', 'swim-for-your-life-album', 'Life Protection', 'Official photos from the open water lifesaving and coastal swimming challenge.'),
(2, 'Wetland Canal Cleanup & Flood Prevention', 'wetland-canal-cleanup-album', 'Environmental Protection', 'Heavy machinery and community dredging operations along drainage canals.'),
(3, 'Women Community Handicraft Enterprise', 'women-handicrafts-album', 'Community Welfare', 'Self-employment bag weaving and handcraft exhibitions in Nalluruwa.'),
(4, 'Human Rights Award & Excellence Ceremony', 'human-rights-award-album', 'Achievements', 'Official leadership and delegation receiving honors at the national conference.');

DROP TABLE IF EXISTS `gallery_images`;
CREATE TABLE `gallery_images` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `album_id` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `image_path` VARCHAR(255) NOT NULL,
    `video_url` VARCHAR(255) NULL,
    `caption` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`album_id`) REFERENCES `gallery_albums`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `gallery_images` (`album_id`, `title`, `image_path`, `caption`) VALUES
(1, 'Team Swim For Your Life at Coastal Beach', 'assets/images/activities/swim-protection.jpg', 'SAP SADA life protection team at the coastal lighthouse beach challenge'),
(2, 'Doosan Heavy Hydraulic Excavator Clearing Canal', 'assets/images/activities/canal-cleanup-1.jpg', 'Excavator dredging thick aquatic weed growth from urban drainage canal'),
(2, 'Tractor and Field Volunteers Hauling Debris', 'assets/images/activities/canal-cleanup-2.jpg', 'Field teams loading excavated mud and wild vegetation onto transport tractor'),
(2, 'Dumper Machine Working on Embankment', 'assets/images/activities/canal-cleanup-3.jpg', 'Dumper transporting waste for ecological disposal'),
(3, 'Exhibition of Handwoven Natural Fiber Bag', 'assets/images/activities/community-crafts-1.jpg', 'Empowered artisan showcasing beautifully crafted natural reed bag'),
(3, 'Handcrafted Community Basketry Showcase', 'assets/images/activities/community-crafts-2.jpg', 'Locally produced woven accessory promoting sustainable self-employment'),
(4, 'Receiving Certificate of Honor and Excellence Trophy', 'assets/images/activities/awards-ceremony.jpg', 'SAP SADA executives honored at the National Human Rights Conference');

-- 14. TESTIMONIALS
DROP TABLE IF EXISTS `testimonials`;
CREATE TABLE `testimonials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `role` VARCHAR(100) NOT NULL,
    `location` VARCHAR(100) NOT NULL,
    `content` TEXT NOT NULL,
    `photo` VARCHAR(255) NULL,
    `rating` INT DEFAULT 5,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `testimonials` (`name`, `role`, `location`, `content`, `rating`, `is_active`) VALUES
('Venerable Resident Monk', 'Community Patron', 'Jayabodhi Viharaya, Nalluruwa', 'The SAP SADA Unit has stood by our people with genuine selflessness. From saving lives during floods to cleaning our public canals, their integrity is visible to everyone.', 5, 1),
('Sunil Shantha', 'Local Resident & Farmer', 'Panadura', 'Before the canal dredging project, every heavy rain flooded our homes and ruined our plots. SAP SADA brought heavy machines and cleared it completely in days.', 5, 1),
('Malini Fernando', 'Handicraft Artisan & Mother', 'Kalutara', 'Through their craft training, I learned how to weave export-quality bags. Today I can support my children’s school education independently.', 5, 1);

-- 15. LEADERSHIP
DROP TABLE IF EXISTS `leadership`;
CREATE TABLE `leadership` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `position` VARCHAR(150) NOT NULL,
    `photo` VARCHAR(255) NOT NULL,
    `bio` TEXT NOT NULL,
    `phone` VARCHAR(30) NULL,
    `email` VARCHAR(100) NULL,
    `facebook` VARCHAR(255) NULL,
    `sort_order` INT DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `leadership` (`name`, `position`, `photo`, `bio`, `phone`, `email`, `sort_order`, `is_active`) VALUES
('Executive Leadership Team', 'Chief Operating Director & Patron', 'assets/images/leadership/leader-award.jpg', 'Dedicated community leader with over two decades of public service, human rights advocacy, and life preservation work across Sri Lanka.', '070 7261978', 'sapsadaunit@gmail.com', 1, 1),
('Director of Life Protection & Aquatic Rescue', 'Head of Marine & Water Safety Division', 'assets/images/leadership/leader-swim.jpg', 'Certified lifesaving coordinator heading the \"Swim For Your Life\" open water training drills and coastal emergency volunteer corps.', '072 253 2493', 'sapsadaunit@gmail.com', 2, 1);

-- 16. CONTACT MESSAGES
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    `phone` VARCHAR(30) NULL,
    `subject` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `replied_at` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `contact_messages` (`name`, `email`, `phone`, `subject`, `message`, `is_read`) VALUES
('Chaminda Silva', 'chaminda.s@gmail.com', '0778899112', 'Inquiry regarding volunteer medical camps', 'Warm greetings. We have a team of youth in Kalutara who would like to join the next life protection and CPR training. Please let us know the registration steps.', 1);

-- 17. NOTIFICATIONS
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `link` VARCHAR(255) NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `notifications` (`type`, `title`, `message`, `link`, `is_read`) VALUES
('volunteer', 'New Volunteer Application', 'Kasun Bandara Perera submitted an application from Kalutara.', 'volunteers.php', 1),
('contact', 'New Contact Message', 'Chaminda Silva sent an inquiry regarding training.', 'messages.php', 1),
('donation', 'New Donation Slip Notification', 'Sampath Wickramasinghe declared a bank transfer of LKR 15,000.', 'donations.php', 0);

-- 18. AUDIT LOGS
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `admin_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `module` VARCHAR(50) NOT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`admin_id`) REFERENCES `admins`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `audit_logs` (`admin_id`, `action`, `module`, `details`, `ip_address`) VALUES
(1, 'System Initialization', 'System', 'SAP SADA database schema initialized successfully with official branding, activities, and multilingual seeds.', '127.0.0.1');

SET FOREIGN_KEY_CHECKS = 1;
