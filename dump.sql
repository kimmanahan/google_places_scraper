-- ============================================================
-- MyAttorneyList — Scraper Schema (3 tables only)
-- Required by: admin/scraper.php, admin/email_harvester.php
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. practice_areas  (lookup — seed data included)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `practice_areas` (
  `id`         smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `name`       varchar(80)          NOT NULL,
  `slug`       varchar(80)          NOT NULL,
  `category`   varchar(60)          DEFAULT 'family-law',
  `sort_order` tinyint(3) unsigned  DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `practice_areas` VALUES
  (1,  'Divorce',                   'divorce',           'family-law', 1),
  (2,  'Child Custody',             'child-custody',     'family-law', 2),
  (3,  'Child Support',             'child-support',     'family-law', 3),
  (4,  'Property Division',         'property-division', 'family-law', 4),
  (5,  'Alimony & Spousal Support', 'alimony',           'family-law', 5),
  (6,  'Prenuptial Agreements',     'prenuptial',        'family-law', 6),
  (7,  'Postnuptial Agreements',    'postnuptial',       'family-law', 7),
  (8,  'Legal Separation',          'legal-separation',  'family-law', 8),
  (9,  'Mediation',                 'mediation',         'family-law', 9),
  (10, 'Collaborative Divorce',     'collaborative',     'family-law', 10),
  (11, 'High Net Worth Divorce',    'high-net-worth',    'family-law', 11),
  (12, 'Military Divorce',          'military-divorce',  'family-law', 12),
  (13, 'International Divorce',     'international',     'family-law', 13),
  (14, 'Domestic Violence',         'domestic-violence', 'family-law', 14),
  (15, 'Restraining Orders',        'restraining-orders','family-law', 15),
  (16, 'Guardianship',              'guardianship',      'family-law', 16),
  (17, 'Adoption',                  'adoption',          'family-law', 17),
  (18, 'Paternity',                 'paternity',         'family-law', 18);

-- ------------------------------------------------------------
-- 2. attorneys  (main table)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attorneys` (
  `id`                int(10) unsigned     NOT NULL AUTO_INCREMENT,
  `first_name`        varchar(80)          DEFAULT NULL,
  `last_name`         varchar(80)          DEFAULT NULL,
  `full_name`         varchar(160) GENERATED ALWAYS AS (CONCAT(`first_name`,' ',`last_name`)) STORED,
  `slug`              varchar(180)         NOT NULL,
  `bar_number`        varchar(40)          DEFAULT NULL,
  `bar_state`         char(2)              NOT NULL,
  `bar_status`        enum('active','inactive','suspended','disbarred','unknown') DEFAULT 'active',
  `admitted_date`     date                 DEFAULT NULL,
  `email`             varchar(160)         DEFAULT NULL,
  `phone`             varchar(30)          DEFAULT NULL,
  `website`           varchar(255)         DEFAULT NULL,
  `address_line1`     varchar(160)         DEFAULT NULL,
  `address_line2`     varchar(80)          DEFAULT NULL,
  `city`              varchar(80)          DEFAULT NULL,
  `state`             char(2)              DEFAULT NULL,
  `zip`               varchar(10)          DEFAULT NULL,
  `county`            varchar(80)          DEFAULT NULL,
  `latitude`          decimal(10,7)        DEFAULT NULL,
  `longitude`         decimal(10,7)        DEFAULT NULL,
  `bio`               text                 DEFAULT NULL,
  `photo_url`         varchar(255)         DEFAULT NULL,
  `languages`         varchar(255)         DEFAULT NULL,
  `law_school`        varchar(160)         DEFAULT NULL,
  `google_place_id`   varchar(100)         DEFAULT NULL,
  `google_rating`     decimal(2,1)         DEFAULT NULL,
  `google_reviews`    smallint(5) unsigned DEFAULT 0,
  `google_photo`      varchar(255)         DEFAULT NULL,
  `listing_tier`      enum('free','enhanced','featured') DEFAULT 'free',
  `featured_until`    date                 DEFAULT NULL,
  `stripe_customer_id` varchar(60)         DEFAULT NULL,
  `claimed`           tinyint(1)           DEFAULT 0,
  `claim_token`       varchar(64)          DEFAULT NULL,
  `claimed_at`        datetime             DEFAULT NULL,
  `profile_views`     int(10) unsigned     DEFAULT 0,
  `contact_clicks`    int(10) unsigned     DEFAULT 0,
  `data_source`       enum('bar_scrape','manual','claimed') DEFAULT 'bar_scrape',
  `scrape_source`     varchar(80)          DEFAULT NULL,
  `last_scraped`      datetime             DEFAULT NULL,
  `verified`          tinyint(1)           DEFAULT 0,
  `created_at`        datetime             DEFAULT current_timestamp(),
  `updated_at`        datetime             DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug`            (`slug`),
  KEY `idx_state`              (`bar_state`),
  KEY `idx_city_state`         (`city`,`state`),
  KEY `idx_zip`                (`zip`),
  KEY `idx_tier`               (`listing_tier`),
  KEY `idx_featured`           (`listing_tier`,`featured_until`),
  KEY `idx_bar`                (`bar_number`,`bar_state`),
  KEY `idx_location`           (`latitude`,`longitude`),
  FULLTEXT KEY `idx_search`    (`full_name`,`city`,`bio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. attorney_practices  (junction table)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `attorney_practices` (
  `attorney_id` int(10) unsigned     NOT NULL,
  `practice_id` smallint(5) unsigned NOT NULL,
  `is_primary`  tinyint(1)           DEFAULT 0,
  PRIMARY KEY (`attorney_id`,`practice_id`),
  KEY `idx_practice` (`practice_id`),
  CONSTRAINT `ap_attorney_fk` FOREIGN KEY (`attorney_id`) REFERENCES `attorneys` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ap_practice_fk` FOREIGN KEY (`practice_id`) REFERENCES `practice_areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
