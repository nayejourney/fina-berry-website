-- =====================================================================
--  DATABASE : fina_berry
--  Sistem   : Website Warung Makan Fina Berry (Laravel + MySQL)
--  Isi      : 11 tabel aplikasi (DDL) + data awal (users, kategori, menu, pengaturan)
--  Mesin    : MySQL 8 / MariaDB 10.4+  (InnoDB, utf8mb4)
--  Catatan  : Uang disimpan sebagai bilangan bulat rupiah (tanpa desimal).
--             Skema ini disalin dari migration Laravel (database/migrations).
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `fina_berry`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `fina_berry`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`, `expenses`, `settings`, `gallery_photos`, `promos`,
  `order_status_logs`, `order_items`, `orders`, `products`, `categories`, `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. users : akun admin (super_admin | owner | staff)
-- ---------------------------------------------------------------------
CREATE TABLE `users` (
  `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`          VARCHAR(255) NOT NULL,
  `email`         VARCHAR(255) NOT NULL,
  `password`      VARCHAR(255) NOT NULL COMMENT 'hash bcrypt',
  `role`          VARCHAR(20)  NOT NULL DEFAULT 'staff' COMMENT 'super_admin | owner | staff',
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `remember_token` VARCHAR(100) NULL DEFAULT NULL,
  `created_at`    TIMESTAMP NULL DEFAULT NULL,
  `updated_at`    TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. categories : kategori menu
-- ---------------------------------------------------------------------
CREATE TABLE `categories` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(255) NOT NULL,
  `slug`       VARCHAR(255) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categories_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. products : menu makanan/minuman
-- ---------------------------------------------------------------------
CREATE TABLE `products` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id`  BIGINT UNSIGNED NOT NULL,
  `name`         VARCHAR(255) NOT NULL,
  `slug`         VARCHAR(255) NOT NULL,
  `description`  TEXT NULL,
  `price`        INT UNSIGNED NOT NULL COMMENT 'harga jual (rupiah)',
  `cost_price`   INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'HPP per porsi, untuk laba/rugi',
  `image`        VARCHAR(255) NULL,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = ditandai habis',
  `is_active`    TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = dinonaktifkan (tidak dihapus permanen)',
  `is_featured`  TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_slug_unique` (`slug`),
  KEY `products_category_id_is_active_index` (`category_id`,`is_active`),
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. orders : pesanan (online via WhatsApp dan offline/POS warung)
-- ---------------------------------------------------------------------
CREATE TABLE `orders` (
  `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code`              VARCHAR(30) NOT NULL COMMENT 'contoh: FB-20261013-014',
  `public_token`      VARCHAR(64) NOT NULL COMMENT 'token halaman konfirmasi publik',
  `idempotency_key`   VARCHAR(64) NULL COMMENT 'anti-duplikat checkout',
  `channel`           VARCHAR(10) NOT NULL DEFAULT 'online' COMMENT 'online | offline',
  `customer_name`     VARCHAR(255) NULL,
  `customer_phone`    VARCHAR(20)  NULL,
  `fulfillment`       VARCHAR(20)  NOT NULL DEFAULT 'ambil' COMMENT 'ambil | antar | makan_ditempat',
  `address`           TEXT NULL,
  `notes`             TEXT NULL,
  `subtotal`          INT UNSIGNED NOT NULL DEFAULT 0,
  `delivery_fee`      INT UNSIGNED NOT NULL DEFAULT 0,
  `total`             INT UNSIGNED NOT NULL DEFAULT 0,
  `payment_method`    VARCHAR(20) NOT NULL DEFAULT 'tunai' COMMENT 'tunai | transfer | qris',
  `payment_status`    VARCHAR(20) NOT NULL DEFAULT 'belum_bayar' COMMENT 'belum_bayar | lunas',
  `paid_at`           TIMESTAMP NULL DEFAULT NULL,
  `paid_validated_by` BIGINT UNSIGNED NULL,
  `status`            VARCHAR(20) NOT NULL DEFAULT 'menunggu' COMMENT 'menunggu | diproses | selesai | dibatalkan',
  `cancel_reason`     VARCHAR(255) NULL,
  `completed_at`      TIMESTAMP NULL DEFAULT NULL,
  `consent_at`        TIMESTAMP NULL DEFAULT NULL COMMENT 'persetujuan privasi (UU PDP)',
  `created_by`        BIGINT UNSIGNED NULL,
  `created_at`        TIMESTAMP NULL DEFAULT NULL,
  `updated_at`        TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_code_unique` (`code`),
  UNIQUE KEY `orders_public_token_unique` (`public_token`),
  UNIQUE KEY `orders_idempotency_key_unique` (`idempotency_key`),
  KEY `orders_created_at_status_index` (`created_at`,`status`),
  KEY `orders_channel_status_index` (`channel`,`status`),
  CONSTRAINT `orders_paid_validated_by_foreign` FOREIGN KEY (`paid_validated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. order_items : rincian item per pesanan (dengan snapshot harga)
-- ---------------------------------------------------------------------
CREATE TABLE `order_items` (
  `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`     BIGINT UNSIGNED NOT NULL,
  `product_id`   BIGINT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL COMMENT 'snapshot nama',
  `unit_price`   INT UNSIGNED NOT NULL COMMENT 'snapshot harga jual',
  `unit_cost`    INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'snapshot HPP (untuk margin)',
  `quantity`     SMALLINT UNSIGNED NOT NULL,
  `line_total`   INT UNSIGNED NOT NULL,
  `created_at`   TIMESTAMP NULL DEFAULT NULL,
  `updated_at`   TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. order_status_logs : riwayat perubahan status pesanan
-- ---------------------------------------------------------------------
CREATE TABLE `order_status_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id`    BIGINT UNSIGNED NOT NULL,
  `from_status` VARCHAR(20) NULL,
  `to_status`   VARCHAR(20) NOT NULL,
  `reason`      VARCHAR(255) NULL,
  `user_id`     BIGINT UNSIGNED NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_logs_order_id_foreign` (`order_id`),
  KEY `order_status_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `order_status_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_status_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 7. promos : promo dengan masa berlaku
-- ---------------------------------------------------------------------
CREATE TABLE `promos` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `title`       VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `image`       VARCHAR(255) NULL,
  `starts_on`   DATE NOT NULL,
  `ends_on`     DATE NOT NULL,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 8. gallery_photos : galeri foto
-- ---------------------------------------------------------------------
CREATE TABLE `gallery_photos` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `image`      VARCHAR(255) NOT NULL,
  `caption`    VARCHAR(255) NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 9. settings : pengaturan toko (key-value)
-- ---------------------------------------------------------------------
CREATE TABLE `settings` (
  `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `key`        VARCHAR(255) NOT NULL,
  `value`      TEXT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 10. expenses : biaya operasional di luar HPP (untuk laba/rugi)
--     category: bahan_baku | gaji | operasional | sewa | pemasaran | lainnya
-- ---------------------------------------------------------------------
CREATE TABLE `expenses` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `spent_on`    DATE NOT NULL,
  `category`    VARCHAR(30) NOT NULL,
  `description` VARCHAR(255) NOT NULL,
  `amount`      INT UNSIGNED NOT NULL,
  `user_id`     BIGINT UNSIGNED NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_spent_on_index` (`spent_on`),
  KEY `expenses_user_id_foreign` (`user_id`),
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 11. audit_logs : log aktivitas admin
-- ---------------------------------------------------------------------
CREATE TABLE `audit_logs` (
  `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     BIGINT UNSIGNED NULL,
  `action`      VARCHAR(60) NOT NULL,
  `entity_type` VARCHAR(40) NULL,
  `entity_id`   BIGINT UNSIGNED NULL,
  `details`     JSON NULL,
  `created_at`  TIMESTAMP NULL DEFAULT NULL,
  `updated_at`  TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_created_at_index` (`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  DATA AWAL
-- =====================================================================

-- Akun awal. Password semua akun: GantiSaya#2026  (WAJIB diganti setelah login pertama)
INSERT INTO `users` (`name`,`email`,`password`,`role`,`is_active`,`created_at`,`updated_at`) VALUES
('Bos Fina Berry',     'bos@finaberry.test',     '$2y$10$JG8mbVp9HjWUqq6sd1MNvOl3LIpvRJvdOTWXV5FyGP0n/iJXSCWOW', 'super_admin', 1, NOW(), NOW()),
('Pemilik Fina Berry', 'pemilik@finaberry.test', '$2y$10$JG8mbVp9HjWUqq6sd1MNvOl3LIpvRJvdOTWXV5FyGP0n/iJXSCWOW', 'owner',       1, NOW(), NOW()),
('Staf Warung',        'staf@finaberry.test',    '$2y$10$JG8mbVp9HjWUqq6sd1MNvOl3LIpvRJvdOTWXV5FyGP0n/iJXSCWOW', 'staff',       1, NOW(), NOW());

INSERT INTO `categories` (`name`,`slug`,`sort_order`,`created_at`,`updated_at`) VALUES
('Makanan','makanan',1,NOW(),NOW()),
('Minuman','minuman',2,NOW(),NOW()),
('Cemilan','cemilan',3,NOW(),NOW());

-- Menu (44 item). cost_price (HPP) = PERKIRAAN +-55% harga jual, ganti dengan HPP asli.
INSERT INTO `products` (`category_id`,`name`,`slug`,`description`,`price`,`cost_price`,`image`,`is_available`,`is_active`,`is_featured`,`created_at`,`updated_at`) VALUES
(1, 'Bakso Daging Sapi', 'bakso-daging-sapi', 'Bakso sapi kenyal dengan kuah kaldu gurih, mie, dan topping lengkap.', 15000, 8500, 'images/menu/bakso-daging-sapi.webp', 1, 1, 1, NOW(), NOW()),
(1, 'Bakso Krewedan', 'bakso-krewedan', NULL, 22000, 12000, 'images/menu/bakso-krewedan.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Bakso Keju', 'bakso-keju', NULL, 17000, 9500, 'images/menu/bakso-keju.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Soto Ayam', 'soto-ayam', NULL, 10000, 5500, 'images/menu/soto-ayam.webp', 1, 1, 1, NOW(), NOW()),
(1, 'Soto Ayam Komplit', 'soto-ayam-komplit', NULL, 20000, 11000, 'images/menu/soto-ayam-komplit.webp', 1, 1, 1, NOW(), NOW()),
(1, 'Soto Daging Sapi', 'soto-daging-sapi', NULL, 23000, 12500, 'images/menu/soto-daging-sapi.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Soto Babat', 'soto-babat', NULL, 20000, 11000, 'images/menu/soto-babat.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Nasi Goreng', 'nasi-goreng', NULL, 15000, 8500, 'images/menu/nasi-goreng.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Sop Daging Sapi + Nasi', 'sop-daging-sapi-nasi', NULL, 25000, 14000, NULL, 1, 1, 0, NOW(), NOW()),
(1, 'Ca Kangkung', 'ca-kangkung', NULL, 8000, 4500, 'images/menu/ca-kangkung.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Goreng', 'paket-ayam-goreng', 'Termasuk nasi, lauk, dan lalapan sambal.', 15000, 8500, 'images/menu/paket-ayam-goreng.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Bakar', 'paket-ayam-bakar', 'Termasuk nasi, lauk, dan lalapan sambal.', 15000, 8500, 'images/menu/paket-ayam-bakar.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Goreng Penyet', 'paket-ayam-goreng-penyet', 'Termasuk nasi, lauk, dan lalapan sambal.', 16000, 9000, 'images/menu/paket-ayam-penyet.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Bakar Penyet', 'paket-ayam-bakar-penyet', 'Termasuk nasi, lauk, dan lalapan sambal.', 16000, 9000, 'images/menu/paket-ayam-penyet.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Goreng Kampung', 'paket-ayam-goreng-kampung', 'Termasuk nasi, lauk, dan lalapan sambal.', 25000, 14000, 'images/menu/paket-ayam-kampung.webp', 1, 1, 1, NOW(), NOW()),
(1, 'Paket Ayam Bakar Kampung', 'paket-ayam-bakar-kampung', 'Termasuk nasi, lauk, dan lalapan sambal.', 27000, 15000, 'images/menu/paket-ayam-bakar-kampung.webp', 1, 1, 1, NOW(), NOW()),
(1, 'Paket Ayam Goreng Kampung Penyet', 'paket-ayam-goreng-kampung-penyet', 'Termasuk nasi, lauk, dan lalapan sambal.', 27000, 15000, 'images/menu/paket-ayam-kampung-penyet.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ayam Bakar Kampung Penyet', 'paket-ayam-bakar-kampung-penyet', 'Termasuk nasi, lauk, dan lalapan sambal.', 27000, 15000, 'images/menu/paket-ayam-bakar-kampung-penyet.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ikan Mujair Goreng', 'paket-ikan-mujair-goreng', 'Termasuk nasi, lauk, dan lalapan sambal.', 20000, 11000, 'images/menu/paket-ikan-goreng.webp', 1, 1, 0, NOW(), NOW()),
(1, 'Paket Ikan Mujair Bakar', 'paket-ikan-mujair-bakar', 'Termasuk nasi, lauk, dan lalapan sambal.', 20000, 11000, 'images/menu/paket-ikan-bakar.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Teh Tawar', 'teh-tawar', NULL, 2000, 1000, 'images/menu/teh-tawar.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Es Teh Tawar', 'es-teh-tawar', NULL, 3000, 1500, 'images/menu/es-teh-tawar.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Teh Manis Anget', 'teh-manis-anget', NULL, 3000, 1500, 'images/menu/teh-manis-anget.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Es Teh Manis', 'es-teh-manis', NULL, 4000, 2000, 'images/menu/es-teh-manis.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Lemon Tea Anget', 'lemon-tea-anget', NULL, 9000, 5000, 'images/menu/lemon-tea-anget.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Es Lemon Tea', 'es-lemon-tea', NULL, 10000, 5500, 'images/menu/es-lemon-tea.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jeruk Anget', 'jeruk-anget', NULL, 4000, 2000, 'images/menu/jeruk-anget.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Es Jeruk', 'es-jeruk', NULL, 5000, 3000, 'images/menu/es-jeruk.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Aneka Kopi', 'aneka-kopi', NULL, 3000, 1500, 'images/menu/aneka-kopi.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Kopi Cappucino', 'kopi-cappucino', NULL, 5000, 3000, 'images/menu/kopi-cappucino.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Es Kopi Cappucino', 'es-kopi-cappucino', NULL, 5000, 3000, 'images/menu/es-kopi-cappucino.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jahe Susu', 'jahe-susu', NULL, 3000, 1500, 'images/menu/jahe-susu.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Soda Susu', 'soda-susu', NULL, 10000, 5500, 'images/menu/soda-susu.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Wedang Jahe', 'wedang-jahe', NULL, 7000, 4000, 'images/menu/wedang-jahe.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jus Strawberry', 'jus-strawberry', NULL, 12000, 6500, 'images/menu/jus-strawberry.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jus Melon', 'jus-melon', NULL, 10000, 5500, NULL, 1, 1, 0, NOW(), NOW()),
(2, 'Jus Jambu', 'jus-jambu', NULL, 10000, 5500, 'images/menu/jus-jambu.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jus Mangga', 'jus-mangga', NULL, 12000, 6500, NULL, 1, 1, 0, NOW(), NOW()),
(2, 'Jus Jeruk', 'jus-jeruk', NULL, 10000, 5500, NULL, 1, 1, 0, NOW(), NOW()),
(2, 'Jus Buah Naga', 'jus-buah-naga', NULL, 10000, 5500, 'images/menu/jus-buah-naga.webp', 1, 1, 0, NOW(), NOW()),
(2, 'Jus Alpukat', 'jus-alpukat', NULL, 12000, 6500, NULL, 1, 1, 0, NOW(), NOW()),
(3, 'Mendoan', 'mendoan', '1 porsi.', 10000, 5500, 'images/menu/mendoan.webp', 1, 1, 1, NOW(), NOW()),
(3, 'Tahu Brontak', 'tahu-brontak', '1 porsi.', 10000, 5500, 'images/menu/tahu-brontak.webp', 1, 1, 0, NOW(), NOW()),
(3, 'Pisang Goreng', 'pisang-goreng', '1 porsi.', 10000, 5500, 'images/menu/pisang-goreng.webp', 1, 1, 0, NOW(), NOW());

-- Pengaturan toko (nilai awal; ubah lewat Admin > Pengaturan)
INSERT INTO `settings` (`key`,`value`,`created_at`,`updated_at`) VALUES
('store_name', 'Warung Makan Fina Berry', NOW(), NOW()),
('tagline', 'Rasa rumahan yang tak terlupakan, sejak 2012', NOW(), NOW()),
('about', 'Warung Fina Berry berdiri sejak 2012 dan hadir dengan berbagai pilihan menu yang lezat, berkualitas, dan terjangkau. Setiap hidangan dimasak dengan penuh dedikasi, supaya setiap suapan membawa kehangatan masakan rumah.', NOW(), NOW()),
('wa_number', '6285647731631', NOW(), NOW()),
('address', 'Kawasan D''LAS (Desa Wisata Lembah Asri), dekat pintu keluar, Desa Serang, Kecamatan Karangreja, Kabupaten Purbalingga, Jawa Tengah.', NOW(), NOW()),
('open_hours', '08.00 - 17.00 WIB', NOW(), NOW()),
('maps_query', 'Warung Makan Fina Berry D''LAS Desa Serang Karangreja Purbalingga', NOW(), NOW()),
('instagram', 'https://www.instagram.com/fina.berry', NOW(), NOW()),
('facebook', '', NOW(), NOW()),
('tiktok', '', NOW(), NOW()),
('halal_cert_number', '', NOW(), NOW()),
('wa_greeting', 'Halo Warung Makan Fina Berry, saya ingin memesan:', NOW(), NOW());
