-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2026 at 12:53 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.3.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ralphmania`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `activity_type` varchar(50) NOT NULL COMMENT 'Darbības veids: login, logout, order_created / Activity type',
  `description` text DEFAULT NULL COMMENT 'Apraksts / Description',
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Sistēmas aktivitāšu žurnāls / System activity log';

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `activity_type`, `description`, `ip_address`, `created_at`) VALUES
(20, 2, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.100', '2026-01-15 19:07:51'),
(21, 2, 'profile_updated', 'Atjaunināja profila informāciju', '192.168.1.100', '2026-01-15 18:07:51'),
(22, 3, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.101', '2026-01-15 17:07:51'),
(23, 3, 'order_created', 'Izveidots pasūtījums #1001', '192.168.1.101', '2026-01-15 17:07:51'),
(24, 5, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.102', '2026-01-15 16:07:51'),
(25, 5, 'cart_updated', 'Pievienoja produktu grozam', '192.168.1.102', '2026-01-15 16:07:51'),
(26, 2, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.100', '2026-01-14 19:07:51'),
(27, 2, 'logout', 'Lietotājs atslēdzās no sistēmas', '192.168.1.100', '2026-01-14 19:07:51'),
(28, 3, 'review_created', 'Pievienoja atsauksmi produktam', '192.168.1.101', '2026-01-14 19:07:51'),
(29, 3, 'comment_created', 'Pievienoja komentāru rakstam', '192.168.1.101', '2026-01-14 19:07:51'),
(30, 5, 'password_changed', 'Nomainīja konta paroli', '192.168.1.102', '2026-01-14 19:07:51'),
(31, 2, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.100', '2026-01-12 19:07:51'),
(32, 2, 'order_created', 'Izveidots pasūtījums #1000', '192.168.1.100', '2026-01-12 19:07:51'),
(33, 3, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.101', '2026-01-10 19:07:51'),
(34, 3, 'contact_sent', 'Nosūtīja ziņojumu administrācijai', '192.168.1.101', '2026-01-10 19:07:51'),
(35, 5, 'login', 'Lietotājs pieslēdzās sistēmai', '192.168.1.102', '2026-01-08 19:07:51'),
(36, 5, 'product_viewed', 'Apskatīja produktu \"Premium Headphones\"', '192.168.1.102', '2026-01-08 19:07:51'),
(37, NULL, 'order_updated', 'Sistēma automātiski atjaunināja pasūtījuma statusu', '127.0.0.1', '2026-01-13 19:07:51'),
(38, 2, 'order_cancelled', 'Atcēla pasūtījumu #999', '192.168.1.100', '2026-01-05 19:07:51'),
(39, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:22:43'),
(40, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:22:50'),
(41, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:23:09'),
(42, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:23:54'),
(43, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:24:13'),
(44, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:25:11'),
(45, 5, 'test_email_sent', 'Testa e-pasts nosūtīts uz ralphmania.roltonslv@gmail.lv', '127.0.0.1', '2026-01-15 19:25:19'),
(46, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:25:44'),
(47, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:26:46'),
(48, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:27:03'),
(49, 5, 'test_email_sent', 'Testa e-pasts nosūtīts uz ralphmania.roltonslv@gmail.lv', '127.0.0.1', '2026-01-15 19:28:23'),
(50, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:29:08'),
(51, 5, 'test_email_sent', 'Testa e-pasts nosūtīts uz ralphmania.roltonslv@gmail.com', '127.0.0.1', '2026-01-15 19:29:20'),
(52, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:30:04'),
(53, 5, 'test_email_sent', 'Testa e-pasts nosūtīts uz ralphmania.roltonslv@gmail.com', '127.0.0.1', '2026-01-15 19:30:07'),
(54, 5, 'cache_cleared', 'Kešatmiņa notīrīta', '127.0.0.1', '2026-01-15 19:48:56'),
(55, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-01-15 19:49:07'),
(56, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-02-24 17:29:28'),
(57, 5, 'settings_updated', 'Iestatījumi atjaunināti', '127.0.0.1', '2026-02-25 16:34:06'),
(58, 5, 'newsletter_subscribed', 'Pieteicās jaunumu saņemšanai: ralphmania.roltonslv@gmail.com', '127.0.0.1', '2026-03-26 14:26:18'),
(59, 2, 'newsletter_subscribed', 'Pieteicās jaunumu saņemšanai: ralph.migals@gmail.com', '127.0.0.1', '2026-04-09 12:37:24'),
(60, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 66)', '127.0.0.1', '2026-04-22 23:28:01'),
(61, 5, 'content_liked', 'Lietotājs RealRoltonsLV atzīmēja \"patīk\" (saturs: Kad LupoDZN pievienojas Australind.. un dabūja to gatavu (GP no manis))', '127.0.0.1', '2026-04-22 23:42:34'),
(62, 5, 'content_unliked', 'Lietotājs RealRoltonsLV noņēma \"patīk\" (saturs: Mans fragments \"UNFORTUNE\" (ft. @qd1m) // Geometry Dash 2.2)', '127.0.0.1', '2026-04-22 23:42:50'),
(63, 5, 'content_liked', 'Lietotājs RealRoltonsLV atzīmēja \"patīk\" (saturs: Mans fragments \"UNFORTUNE\" (ft. @qd1m) // Geometry Dash 2.2)', '127.0.0.1', '2026-04-22 23:42:53'),
(64, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 2)', '127.0.0.1', '2026-04-22 23:48:51'),
(65, 5, 'content_liked', 'Lietotājs RealRoltonsLV atzīmēja \"patīk\" (saturs: [SOLO] \"That One Trip\" autors RealRoltonsLV (es) || GD 2.207)', '127.0.0.1', '2026-04-23 09:42:49'),
(66, 2, 'comment_reply', 'Lietotājs Ralfs M. atbildēja uz komentāru (satura ID: 2)', '127.0.0.1', '2026-04-23 10:15:08'),
(67, 2, 'content_liked', 'Lietotājs Ralfs M. atzīmēja \"patīk\" (saturs: [SOLO] \"That One Trip\" autors RealRoltonsLV (es) || GD 2.207)', '127.0.0.1', '2026-04-23 10:15:25'),
(68, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 66)', '127.0.0.1', '2026-04-23 18:38:38'),
(69, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 66)', '127.0.0.1', '2026-04-23 18:41:08'),
(70, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 66)', '127.0.0.1', '2026-04-23 18:41:40'),
(71, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:40:47'),
(72, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (saturs ID 88, vērtējums: 4/5)', '127.0.0.1', '2026-04-24 20:44:20'),
(73, 5, 'content_liked', 'Lietotājs RealRoltonsLV atzīmēja \"patīk\" (saturs: Truongs bija šeit)', '127.0.0.1', '2026-04-24 20:48:46'),
(74, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:50:31'),
(75, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:51:00'),
(76, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:51:22'),
(77, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:51:58'),
(78, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:54:02'),
(79, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:54:33'),
(80, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 20:56:08'),
(81, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (saturs ID 88, vērtējums: 5/5)', '127.0.0.1', '2026-04-24 20:58:53'),
(82, 5, 'comment_added', 'Lietotājs RealRoltonsLV pievienoja komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 21:00:55'),
(83, 5, 'content_unliked', 'Lietotājs RealRoltonsLV noņēma \"patīk\" (saturs: Truongs bija šeit)', '127.0.0.1', '2026-04-24 21:02:05'),
(84, 5, 'content_liked', 'Lietotājs RealRoltonsLV atzīmēja \"patīk\" (saturs: Truongs bija šeit)', '127.0.0.1', '2026-04-24 21:02:06'),
(85, 5, 'comment_reply', 'Lietotājs RealRoltonsLV atbildēja uz komentāru (satura ID: 88)', '127.0.0.1', '2026-04-24 21:04:44'),
(86, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (saturs ID 88, vērtējums: 4/5)', '127.0.0.1', '2026-04-24 21:05:55'),
(87, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (produkts ID 2, vērtējums: 5/5)', '127.0.0.1', '2026-04-24 21:08:41'),
(88, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (produkts ID 2, vērtējums: 5/5)', '127.0.0.1', '2026-04-24 21:14:57'),
(89, 5, 'review_added', 'Lietotājs RealRoltonsLV pievienoja atsauksmi (saturs ID 88, vērtējums: 5/5)', '127.0.0.1', '2026-04-24 21:16:43');

-- --------------------------------------------------------

--
-- Table structure for table `administrators`
--

CREATE TABLE `administrators` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to users (admin account)',
  `full_name` varchar(100) NOT NULL COMMENT 'Administratora vārds / Administrator name',
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Piekļuves tiesības / Access permissions' CHECK (json_valid(`permissions`)),
  `is_super_admin` tinyint(1) DEFAULT 0 COMMENT 'Vai ir super admin / Is super admin',
  `last_login_at` timestamp NULL DEFAULT NULL COMMENT 'Pēdējā pieslēgšanās / Last login',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Administratoru informācija ar paplašinātām tiesībām / Administrator information with extended permissions';

--
-- Dumping data for table `administrators`
--

INSERT INTO `administrators` (`id`, `user_id`, `full_name`, `permissions`, `is_super_admin`, `last_login_at`, `created_at`, `updated_at`) VALUES
(1, 5, 'RealRoltonsLV', NULL, 1, '2026-04-25 10:46:22', NULL, '2026-04-25 10:46:22'),
(6, 2, 'Ralfs Migals', '[\"products.view\",\"products.create\",\"products.edit\",\"products.delete\",\"categories.view\",\"categories.create\",\"categories.edit\",\"categories.delete\",\"orders.view\",\"orders.edit\",\"orders.delete\",\"orders.export\",\"users.view\",\"users.create\",\"users.edit\",\"users.delete\",\"users.ban\",\"content.view\",\"content.create\",\"content.edit\",\"content.delete\",\"content.publish\",\"reviews.view\",\"reviews.moderate\",\"reviews.delete\",\"comments.view\",\"comments.moderate\",\"comments.delete\",\"contacts.view\",\"contacts.reply\",\"contacts.delete\",\"couriers.view\",\"couriers.create\",\"couriers.edit\",\"couriers.delete\",\"couriers.assign\",\"settings.view\",\"settings.edit\",\"logs.view\",\"logs.export\",\"admins.view\",\"admins.create\",\"admins.edit\",\"admins.delete\"]', 0, '2026-04-25 10:37:32', '2026-04-25 10:36:45', '2026-04-25 10:54:30');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('0ade7c2cf97f75d009975f4d720d1fa6c19f4897', 'i:1;', 1776595279),
('0ade7c2cf97f75d009975f4d720d1fa6c19f4897:timer', 'i:1776595279;', 1776595279),
('da4b9237bacccdf19c0760cab7aec4a8359010b0', 'i:2;', 1775163081),
('da4b9237bacccdf19c0760cab7aec4a8359010b0:timer', 'i:1775163081;', 1775163081),
('ralfs.migals@haha|127.0.0.1', 'i:1;', 1776597934),
('ralfs.migals@haha|127.0.0.1:timer', 'i:1776597934;', 1776597934),
('ralfs.migals@icloud.com|127.0.0.1', 'i:1;', 1776602056),
('ralfs.migals@icloud.com|127.0.0.1:timer', 'i:1776602056;', 1776602056),
('ralphmaniaroltonslv@gmail.com|127.0.0.1', 'i:1;', 1774532168),
('ralphmaniaroltonslv@gmail.com|127.0.0.1:timer', 'i:1774532168;', 1774532168),
('roltonslv@gmail.com|127.0.0.1', 'i:1;', 1776597485),
('roltonslv@gmail.com|127.0.0.1:timer', 'i:1776597485;', 1776597485),
('settings', 'a:29:{s:9:\"site_name\";s:10:\"RalphMania\";s:19:\"site_description_lv\";s:36:\"Labākais interneta veikals Latvijā\";s:19:\"site_description_en\";s:30:\"The best online shop in Latvia\";s:11:\"admin_email\";s:30:\"ralphmania.roltonslv@gmail.com\";s:8:\"timezone\";s:11:\"Europe/Riga\";s:11:\"date_format\";s:5:\"d.m.Y\";s:8:\"currency\";s:3:\"EUR\";s:15:\"currency_symbol\";s:3:\"€\";s:8:\"tax_rate\";s:2:\"21\";s:23:\"free_shipping_threshold\";s:2:\"50\";s:19:\"low_stock_threshold\";s:1:\"5\";s:14:\"items_per_page\";s:2:\"20\";s:14:\"mail_from_name\";s:10:\"RalphMania\";s:17:\"mail_from_address\";s:22:\"noreply@ralphmania.com\";s:9:\"smtp_host\";s:14:\"smtp.gmail.com\";s:9:\"smtp_port\";s:3:\"587\";s:13:\"smtp_username\";s:30:\"ralphmania.roltonslv@gmail.com\";s:13:\"smtp_password\";N;s:12:\"facebook_url\";s:40:\"https://www.facebook.com/ralfs.migals.3/\";s:13:\"instagram_url\";s:38:\"https://www.instagram.com/ralfsmigals/\";s:11:\"twitter_url\";s:27:\"https://x.com/RealRoltonsLV\";s:11:\"youtube_url\";s:34:\"https://www.youtube.com/@RoltonsLV\";s:10:\"tiktok_url\";s:37:\"https://www.tiktok.com/@realroltonslv\";s:13:\"meta_title_lv\";s:30:\"RalphMania - Interneta Veikals\";s:13:\"meta_title_en\";s:24:\"RalphMania - Online Shop\";s:19:\"meta_description_lv\";s:83:\"Plašs preču klāsts ar ātrāko piegādi Latvijā. Iepērcies ērti un izdevīgi!\";s:19:\"meta_description_en\";s:78:\"Wide range of products with the fastest delivery in Latvia. Shop conveniently!\";s:19:\"google_analytics_id\";N;s:17:\"facebook_pixel_id\";N;}', 1777068437);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to users (NULL for guest carts)',
  `session_id` varchar(255) DEFAULT NULL COMMENT 'Session ID for guest carts',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lietotāju grozi / User shopping carts';

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, '2025-12-14 22:05:18', '2025-12-14 22:05:18'),
(2, 3, NULL, '2026-01-07 12:25:31', '2026-01-07 12:25:31'),
(3, 5, NULL, '2026-01-10 00:52:44', '2026-01-10 00:52:44');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to carts',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to users (NULL = guest cart)',
  `session_id` varchar(255) DEFAULT NULL COMMENT 'Sesijas ID viesiem / Session ID for guests',
  `product_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to products',
  `quantity` int(10) UNSIGNED NOT NULL DEFAULT 1 COMMENT 'Produkta daudzums / Product quantity',
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Cena pirkšanas brīdī / Price at purchase time',
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED COMMENT 'Kopējā summa (auto) / Total (auto)',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Izveidošanas datums / Created at',
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lietotāja iepirkumu grozs / User shopping cart';

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `user_id`, `session_id`, `product_id`, `quantity`, `size`, `price`, `created_at`, `updated_at`) VALUES
(42, 1, 2, 'zsmcnCKbEKsng9MjiiKvI21GfipZScHUHFF4PrCb', 8, 1, NULL, 5.99, '2026-03-23 14:54:59', '2026-03-23 14:54:59'),
(43, 1, 2, 'zsmcnCKbEKsng9MjiiKvI21GfipZScHUHFF4PrCb', 5, 1, 'L', 29.99, '2026-03-23 14:55:14', '2026-03-23 14:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name_lv` varchar(100) NOT NULL COMMENT 'Nosaukums latviski / Name in Latvian',
  `name_en` varchar(100) NOT NULL COMMENT 'Nosaukums angliski / Name in English',
  `slug` varchar(100) NOT NULL COMMENT 'URL-friendly slug',
  `description_lv` text DEFAULT NULL COMMENT 'Apraksts latviski / Description in Latvian',
  `description_en` text DEFAULT NULL COMMENT 'Apraksts angliski / Description in English',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to categories - nested categories',
  `sort_order` int(10) UNSIGNED DEFAULT 0 COMMENT 'Kārtošanas secība / Sort order',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Vai ir aktīva / Is active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Produktu kategorijas: Apģērbi, Aksesuāri, Suvenīri / Product categories: Clothing, Accessories, Souvenirs';

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name_lv`, `name_en`, `slug`, `description_lv`, `description_en`, `parent_id`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Apģērbi', 'Clothing', 'clothing', 'RalphMania zīmola apģērbi', 'RalphMania branded clothing', NULL, 1, 1, '2025-12-06 14:53:35', '2026-04-01 12:29:23'),
(2, 'Aksesuāri', 'Accessories', 'accessories', 'RalphMania aksesuāri un papildus preces', 'RalphMania accessories and extras', NULL, 2, 1, '2025-12-06 14:53:35', '2025-12-07 14:00:21'),
(3, 'Suvenīri', 'Souvenirs', 'souvenirs', 'RalphMania suvenīri un kolekcionējamie', 'RalphMania souvenirs and collectibles', NULL, 3, 1, '2025-12-06 14:53:35', '2025-12-07 14:00:21'),
(4, 'Dāvanu Kartes', 'Gift Cards', 'gift-cards', 'RalphMania dāvanu kartes', 'RalphMania gift cards', NULL, 4, 1, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(10, 'Krekli', 'Shirts', 'shirts', 'Krekli ar RalphMania dizainu', 'Shirts with RalphMania design', 1, 4, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(11, 'Džemperi', 'Sweatshirts', 'sweatshirts', 'Džemperi un jaki', 'Sweatshirts and jackets', 1, 2, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(12, 'Kapuces', 'Hoodies', 'hoodies', 'Hoodies ar logotipu', 'Hoodies with logo', 1, 3, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(13, 'Cepures', 'Caps', 'caps', 'Snapback un cepures', 'Snapbacks and caps', 1, 1, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(20, 'Somas', 'Bags', 'bags', 'Mugursomas un somiņas', 'Backpacks and bags', 2, 4, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(21, 'Piespraudes', 'Pin Badges', 'pin-badges', 'Aksesuāri un rotaslietas', 'Accessories and jewelry', 2, 3, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(22, 'Tech aksesuāri', 'Tech Accessories', 'tech-accessories', 'Telefona vāciņi, uzlīmes', 'Phone cases, stickers', 2, 5, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(30, 'Krūzes un pudeles', 'Mugs & Bottles', 'mugs-and-bottles', 'Keramikas un termo krūzes/pudeles', 'Ceramic and thermal mugs/bottles', 3, 1, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(31, 'Uzlīmes', 'Stickers', 'stickers', 'Dekoratīvas uzlīmes', 'Decorative stickers', 3, 3, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(32, 'Plakāti', 'Posters', 'posters', 'Plakāti un posteri', 'Posters and prints', 3, 2, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(33, 'Atslēgu piekariņi', 'Keychains', 'keychains', 'Mini aksesuāri', 'Mini accessories', 2, 1, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(35, 'Peles paliktņi', 'Mouse Pads', 'mouse-pads', 'Datorgalda aksesuāri', 'Desk accessories', 2, 2, 1, '2025-12-06 14:53:35', '2025-12-06 14:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to users - kas komentē / who comments',
  `content_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to content - kam pievienots / what is commented',
  `comment_text` text NOT NULL COMMENT 'Komentāra teksts (max 300 chars) / Comment text',
  `parent_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to comments - thread/replies',
  `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Vai ir apstiprināts / Is approved',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Izveidošanas datums / Created at',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Rediģēšanas datums / Updated at'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Komentāri pie satura ar thread atbalstu / Comments on content with thread support';

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`id`, `user_id`, `content_id`, `comment_text`, `parent_id`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 2, 1, 'Čau!', NULL, 1, '2025-12-08 15:22:15', '2025-12-08 15:22:15'),
(2, 2, 2, 'yo', NULL, 1, '2025-12-08 15:33:53', '2026-04-23 09:59:19'),
(3, 2, 1, 'testing', NULL, 1, '2025-12-09 13:29:23', '2025-12-09 13:29:23'),
(4, 2, 1, 'testing 2', NULL, 1, '2025-12-09 13:48:33', '2025-12-09 13:48:33'),
(5, 2, 2, 'That One Trip is AWESOMEEEEEEEEE', NULL, 1, '2025-12-09 13:50:58', '2026-04-23 09:59:16'),
(6, 2, 2, 'Testing to see if I can see the comment', NULL, 1, '2025-12-09 14:14:31', '2026-04-23 09:59:15'),
(7, 2, 5, 'EJ TU NOST ŠIS LĪMENIS BEIDZOT IR GATAVS', NULL, 1, '2025-12-09 14:16:41', '2025-12-09 14:16:41'),
(8, 2, 5, 'yo', NULL, 1, '2025-12-09 14:22:29', '2025-12-09 14:22:29'),
(9, 2, 3, 'howdy!', NULL, 1, '2025-12-09 23:22:41', '2025-12-09 23:22:41'),
(10, 2, 3, 'I AM GD LEVEL', NULL, 1, '2025-12-09 23:37:16', '2025-12-09 23:37:16'),
(11, 2, 2, 'cool beans', NULL, 1, '2025-12-10 00:08:18', '2026-04-23 09:59:13'),
(12, 3, 2, 'hello from the other side!', NULL, 1, '2025-12-10 01:17:46', '2026-04-23 10:05:35'),
(13, 2, 63, 'Good luck on your journey!', NULL, 1, '2025-12-10 02:21:42', '2025-12-10 02:21:42'),
(14, 2, 1, 'Hacy bija šeit', NULL, 1, '2025-12-10 21:59:36', '2025-12-10 21:59:36'),
(15, 2, 65, 'Atceros, ka iesāku tieši šādi. Pilnīgi iespaidīgi, kā viss iesākās un kur tagad esmu :)', NULL, 1, '2026-01-06 12:41:05', '2026-01-06 12:41:05'),
(16, 3, 66, 'wowwwwwwwwwwwww', NULL, 1, '2026-02-23 17:05:07', '2026-02-23 17:05:07'),
(17, 2, 82, 'normāli', NULL, 1, '2026-02-24 17:20:10', '2026-02-24 17:20:10'),
(18, 5, 66, 'Ļoti iespaidīgs līmenis, tā tik turpini taisīt :D', NULL, 1, '2026-03-26 15:16:22', '2026-03-26 15:16:22'),
(20, 5, 2, 'Hello!!!!!', 12, 1, '2026-04-22 23:48:51', '2026-04-22 23:48:51'),
(21, 2, 2, 'Oh hi! :D', 12, 1, '2026-04-23 10:15:08', '2026-04-23 10:15:08'),
(32, 5, 88, 'heyyyy', NULL, 1, '2026-04-24 20:56:08', '2026-04-24 20:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `comment_moods`
--

CREATE TABLE `comment_moods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `comment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `score` tinyint(3) UNSIGNED NOT NULL COMMENT 'Noskaņojuma vērtējums 0–100',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comment_moods`
--

INSERT INTO `comment_moods` (`id`, `comment_id`, `user_id`, `score`, `created_at`, `updated_at`) VALUES
(1, 12, 2, 100, '2026-04-23 10:13:47', '2026-04-23 10:13:51'),
(2, 11, 2, 83, '2026-04-23 10:13:56', '2026-04-23 10:13:56'),
(3, 6, 2, 92, '2026-04-23 10:13:58', '2026-04-23 10:13:58'),
(4, 5, 2, 100, '2026-04-23 10:14:02', '2026-04-23 10:14:02'),
(5, 2, 2, 77, '2026-04-23 10:14:04', '2026-04-23 10:14:04'),
(6, 12, 5, 100, '2026-04-23 10:16:01', '2026-04-23 18:15:27'),
(7, 11, 5, 81, '2026-04-23 10:16:07', '2026-04-25 10:33:29'),
(8, 6, 5, 63, '2026-04-23 10:16:14', '2026-04-23 12:28:41'),
(9, 5, 5, 100, '2026-04-23 10:16:20', '2026-04-23 10:16:20'),
(10, 2, 5, 74, '2026-04-23 10:16:25', '2026-04-23 16:53:24'),
(12, 18, 5, 100, '2026-04-23 16:54:14', '2026-04-23 16:54:14'),
(13, 16, 5, 65, '2026-04-23 16:54:30', '2026-04-23 16:54:32'),
(14, 1, 5, 84, '2026-04-23 17:11:29', '2026-04-23 17:11:29'),
(15, 14, 5, 100, '2026-04-23 17:25:31', '2026-04-23 17:25:31'),
(16, 4, 5, 70, '2026-04-23 17:25:32', '2026-04-23 17:25:32'),
(17, 3, 5, 50, '2026-04-23 17:25:38', '2026-04-23 17:25:39'),
(20, 32, 5, 62, '2026-04-24 20:56:17', '2026-04-24 21:29:01');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to users (if registered)',
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL COMMENT 'Tālruņa numurs ar valsts kodu / Phone number with country code',
  `country_code` varchar(5) DEFAULT NULL COMMENT 'Valsts kods / Country code',
  `subject` varchar(200) NOT NULL COMMENT 'Ziņojuma virsraksts / Message subject',
  `message` text NOT NULL COMMENT 'Ziņojuma saturs / Message content',
  `is_read` tinyint(1) DEFAULT 0 COMMENT 'Vai ir izlasīts / Is read',
  `is_replied` tinyint(1) DEFAULT 0 COMMENT 'Vai ir atbildēts / Is replied',
  `reply_text` text DEFAULT NULL COMMENT 'Administratora atbilde / Admin reply text',
  `replied_at` timestamp NULL DEFAULT NULL COMMENT 'Atbildes datums / Reply date',
  `replied_by` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to administrators',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Datums / Created at',
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='E-pasta kontaktu ziņojumi / Email contact messages';

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `user_id`, `name`, `email`, `phone`, `country_code`, `subject`, `message`, `is_read`, `is_replied`, `reply_text`, `replied_at`, `replied_by`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Ralfs', 'ralph.migals@gmail.com', NULL, NULL, 'Kontaktlapas 1. tests', 'Sveiki! Šeit pārbaudu sistēmu', 1, 0, NULL, NULL, NULL, '2026-01-08 22:53:27', '2026-01-14 15:34:38'),
(2, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '26167232', '+371', 'Kontaktlapas 2. tests', 'Izmēģinu jauno fīču', 1, 0, NULL, NULL, NULL, '2026-01-09 00:17:42', '2026-01-10 21:41:56'),
(3, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '26167232', '+371', 'English contact message', 'I genuinely love this system you\'ve prepared. Very nice!', 1, 0, NULL, NULL, NULL, '2026-01-09 00:31:10', '2026-01-10 21:41:58'),
(4, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '29123456', '+371', 'Test thingy', 'Yuki was here', 1, 1, 'Hi YukiMasterGD :DDDD', '2026-01-14 15:32:06', 5, '2026-01-12 14:35:01', '2026-01-14 15:32:06'),
(5, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '29123456', '+371', 'Testa tituls', 'testa teksts', 1, 1, 'testa atbilde', '2026-01-14 15:28:51', 5, '2026-01-13 17:13:18', '2026-01-14 15:28:51'),
(6, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '29123456', '+371', 'Kristians bija šeit', 'haha lol', 1, 1, 'tas ir nice, es tev arī saku čau', '2026-01-16 12:24:31', 5, '2026-01-16 12:16:32', '2026-01-16 12:24:31'),
(7, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '29123456', '+371', 'Tests 23. februārī', 'Čau!', 1, 0, NULL, NULL, NULL, '2026-02-23 16:29:40', '2026-02-23 21:27:04'),
(8, 3, 'Roltons Alt Account (Courier)', 'roltonsalt@gmail.com', '26123456', NULL, '[🚨 Kurjers] Cita problēma', 'testēju no kurjera puses', 1, 0, NULL, NULL, NULL, '2026-02-23 21:48:20', '2026-02-23 21:52:30'),
(9, 3, 'Roltons Alt Account (Courier)', 'roltonsalt@gmail.com', '26123456', NULL, '[🚨 Kurjers] Cita problēma', 'mēģinājums numur 2', 1, 1, 'es arī testēju', '2026-02-23 21:54:08', 5, '2026-02-23 21:51:15', '2026-02-23 21:54:08'),
(10, 3, 'Roltons Alt Account (Courier)', 'roltonsalt@gmail.com', '26123456', NULL, '[🚨 Kurjers] Transportlīdzekļa problēma', 'the tires are busted gng', 1, 1, 'not my problem lol', '2026-02-23 22:26:05', 5, '2026-02-23 22:20:34', '2026-02-23 22:26:05'),
(11, 3, 'Roltons Alt Account (Courier)', 'roltonsalt@gmail.com', '26123456', NULL, '[🚨 Kurjers] Nepareiza adrese', 'pareizi sūtu, ja?', 1, 0, NULL, NULL, NULL, '2026-02-23 22:45:54', '2026-02-23 22:47:21'),
(12, 2, 'RoltonsLV', 'ralph.migals@gmail.com', '26167232', '+371', 'Testa temats 24022026', 'Tētis bija šeit', 1, 0, NULL, NULL, NULL, '2026-02-24 16:46:58', '2026-02-24 17:23:50'),
(13, 3, 'Roltons Alt Account (Courier)', 'roltonsalt@gmail.com', '26123456', NULL, '[🚨 Kurjers] Cita problēma', 'atlaide nerādās', 1, 0, NULL, NULL, NULL, '2026-02-24 17:34:30', '2026-02-25 13:30:54'),
(14, 2, 'Ralfs M.', 'ralph.migals@gmail.com', '29123456', '+371', 'Tests', '25. datums', 1, 0, NULL, NULL, NULL, '2026-02-25 16:14:06', '2026-02-25 16:30:57'),
(15, 3, 'Courier Master (Courier)', 'courier@gmail.com', '26123456', NULL, '[🚨 Kurjers] Cita problēma — Pas. #RM-20260225-BA2A1C', 'redzi šo ziņu?', 1, 0, NULL, NULL, NULL, '2026-02-25 16:36:46', '2026-02-27 12:34:34'),
(16, 2, 'Ralfs M.', 'ralph.migals@gmail.com', '29123456', '+371', 'other', 'testa ziņojums no veikala', 1, 0, NULL, NULL, NULL, '2026-04-20 13:10:31', '2026-04-21 09:41:01');

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title_lv` varchar(100) NOT NULL COMMENT 'Virsraksts latviski (max 100 chars)',
  `title_en` varchar(100) DEFAULT NULL COMMENT 'Title in English',
  `slug` varchar(255) NOT NULL COMMENT 'URL-friendly slug',
  `type` enum('video','blog','post','announcement') NOT NULL COMMENT 'Satura veids: video, blogs, ziņas vai paziņojumi / Content type',
  `description_lv` text DEFAULT NULL COMMENT 'Apraksts latviski / Description in Latvian',
  `description_en` text DEFAULT NULL COMMENT 'Apraksts angliski / Description in English',
  `content_body_lv` longtext DEFAULT NULL COMMENT 'Pilns saturs latviski (blogs)',
  `content_body_en` longtext DEFAULT NULL COMMENT 'Full content in English (blog)',
  `video_url` varchar(500) DEFAULT NULL COMMENT 'Video URL (YouTube, TikTok, Instagram, etc.)',
  `video_platform` varchar(50) DEFAULT NULL COMMENT 'Platforma: YouTube, TikTok, Instagram, Facebook, Twitch, Vimeo',
  `thumbnail` varchar(255) DEFAULT NULL COMMENT 'Video thumbnail image',
  `featured_image` varchar(255) DEFAULT NULL COMMENT 'Featured/hero image for blog posts',
  `blog_images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Blog post images (JSON array of image paths)' CHECK (json_valid(`blog_images`)),
  `duration` int(10) UNSIGNED DEFAULT NULL COMMENT 'Video ilgums sekundēs / Duration in seconds',
  `category` varchar(100) DEFAULT NULL COMMENT 'Kategorija / Category',
  `view_count` int(10) UNSIGNED DEFAULT 0 COMMENT 'Skatījumu skaits / View count',
  `like_count` int(10) UNSIGNED DEFAULT 0 COMMENT 'Patīk skaits / Like count',
  `is_published` tinyint(1) DEFAULT 0 COMMENT 'Vai ir publicēts / Is published',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Vai ir featured / Is featured',
  `published_at` timestamp NULL DEFAULT NULL COMMENT 'Publicēšanas datums / Publication date',
  `created_by` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to users - content creator',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Saturs: video un blogi ar divvalodu atbalstu / Content: videos and blogs with bilingual support';

--
-- Dumping data for table `content`
--

INSERT INTO `content` (`id`, `title_lv`, `title_en`, `slug`, `type`, `description_lv`, `description_en`, `content_body_lv`, `content_body_en`, `video_url`, `video_platform`, `thumbnail`, `featured_image`, `blog_images`, `duration`, `category`, `view_count`, `like_count`, `is_published`, `is_featured`, `published_at`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Mans fragments \"UNFORTUNE\" (ft. @qd1m) // Geometry Dash 2.2', 'My part in \"UNFORTUNE\" (ft. @qd1m) // Geometry Dash 2.2', 'unfortune-gd-collab', 'video', 'Mans fragments episkajā Geometry Dash 2.2 mega-kollabā kopā ar @qd1m. Intensīva un izaicinošā GD spēle!', 'My part in the epic Geometry Dash 2.2 megacollab featuring @qd1m. Intense and challenging GD gameplay!', NULL, NULL, 'https://www.youtube.com/watch?v=7Frm2a32heI', 'YouTube', '/img/thumbnails/unfortune.png', NULL, NULL, NULL, 'Geometry Dash', 59, 2, 1, 1, '2025-11-22 12:00:00', 2, '2025-12-07 12:14:45', '2026-04-23 18:37:23'),
(2, '[SOLO] \"That One Trip\" autors RealRoltonsLV (es) || GD 2.207', '[SOLO] \"That One Trip\" by RealRoltonsLV (me) || GD 2.207', 'that-one-trip-solo', 'video', 'Mans solo Geometry Dash līmenis \"That One Trip\", izveidots un nospēlēts GD 2.207 versijā. Pilnīgi oriģināls dizains!', 'My solo Geometry Dash level \"That One Trip\", created and played in GD 2.207. Completely original design!', NULL, NULL, 'https://www.youtube.com/watch?v=0I1xuI7Obpw&t', 'YouTube', '/img/thumbnails/that-one-trip.png', NULL, NULL, NULL, 'Geometry Dash', 76, 2, 1, 1, '2025-08-24 11:00:00', 2, '2025-08-24 11:00:00', '2026-04-25 10:54:07'),
(3, 'Mans fragments \"I AM GD LEVEL\" || Geometry Dash 2.2', 'My part in \"I AM GD LEVEL\" || Geometry Dash 2.2', 'i-am-gd-level-collab', 'video', 'Mans ieguldījums leģendārajā \"I AM GD LEVEL\" mega-kollabā Geometry Dash 2.2. Viens no grūtākajiem fragmentiem!', 'My contribution to the legendary \"I AM GD LEVEL\" megacollab in Geometry Dash 2.2. One of the hardest parts!', NULL, NULL, 'https://www.youtube.com/watch?v=GIRJISwkB_w', 'YouTube', '/img/thumbnails/i-am-gd-level.png', NULL, NULL, NULL, 'Geometry Dash', 13, 1, 1, 0, '2025-02-02 12:00:00', 2, '2025-02-02 12:00:00', '2025-12-09 23:47:46'),
(4, 'Kad Magpipe pievienojas Australind.. un gatavo to feini (GP no manis un @Kcool.101)', 'When Magpipe joins Australind.. and cooks gud (GP by Me and ‪@Kcool.101‬)', 'magpipe-australind', 'video', 'Mans, Kcool101 un Magpipe ieguldījums leģendārajā \"Australind\" mega-kollabā Geometry Dash 2.2.', 'My, Kcool101 and Magpipe\'s contribution to the legendary \"Australind\" megacollab in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=RCu-FQoDTUo', 'YouTube', '/img/thumbnails/magpipe-australind.png', NULL, NULL, NULL, 'Geometry Dash', 3, 0, 1, 0, '2024-11-18 12:00:00', 2, '2024-11-18 12:00:00', '2024-11-18 12:00:00'),
(5, '\"COME UP\" autors RealRoltonsLV & vēl! // (NCS Gauntlet konkurss)', '\"COME UP\" by RealRoltonsLV & More! // (NCS Gauntlet Contest)', 'come-up', 'video', 'RoltonaLV un viņa komandas ieguldījums leģendārajā \"COME UP\" mega-kollabā Geometry Dash 2.2.', 'RoltonsLV and his team\'s contribution to the legendary \"COME UP\" megacollab in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=JOa6WAF8PVM', 'YouTube', '/img/thumbnails/come-up.png', NULL, NULL, NULL, 'Geometry Dash', 8, 1, 1, 0, '2024-10-05 11:00:00', 2, '2024-10-05 11:00:00', '2026-01-13 21:09:52'),
(6, '[NCS DAILY] \"Placid Ivy Grove\" autors ‪@chutruongwaifu‬', '[NCS DAILY] \"Placid Ivy Grove\" by ‪@chutruongwaifu‬', 'placid-ivy-grove', 'video', 'Truonga ieguldījums leģendārajā \"Placid Ivy Grove\" mega-kollabā Geometry Dash 2.2.', 'Truong\'s contribution to the legendary \"Placid Ivy Grove\" megacollab in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=k_hNSKwB9nA', 'YouTube', '/img/thumbnails/placid-ivy-grove.png', NULL, NULL, NULL, 'Geometry Dash', 7, 1, 1, 0, '2024-09-06 11:00:00', 2, '2024-09-06 11:00:00', '2024-09-06 11:00:00'),
(7, '\"hostage adventure\" autors truongwf // ĀTRSKRĒJIENA OTRAIS LABĀKAIS 🥈 (0:12:762)', '\"hostage adventure\" by truongwf // SPEEDRUN SECOND BEST 🥈 (0:12:762)', 'hostage-adventure-speedrun', 'video', 'RoltonaLV ātrskrējiena piegājiens pieveikt pasaules labāko rekordu Truonga ieguldītajā līmenī \"Placid Ivy Grove\" Geometry Dash 2.2.', 'RoltonsLV\'s speedrun attempt to beat the world\'s best record in Truong\'s invested level \"Placid Ivy Grove\" in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=Yo-p0cY23sM', 'YouTube', '/img/thumbnails/hostage-adventure-speedrun.png', NULL, NULL, NULL, 'Geometry Dash', 3, 1, 1, 0, '2024-08-14 11:00:00', 2, '2024-08-14 11:00:00', '2024-08-14 11:00:00'),
(8, '[PRIEKŠSKATS] \"COME UP\" no Epil komandas // (NCS Gauntlet konkurss)', '[PREVIEW] \"COME UP\" by Team Epil // (NCS Gauntlet Contest)', 'come-up-preview', 'video', 'RoltonaLV un Epil komandas ieguldījums leģendārajā \"COME UP\" mega-kollabā Geometry Dash 2.2. Šis video ir tikai priekšskats, lai redzētu veikto progresu līmenī pirms pabeigšanas', 'RoltonsLV and Team Epil\'s contribution to the legendary \"COME UP\" megacollab in Geometry Dash 2.2. This video is just a preview to see the progress made on the level before the end', NULL, NULL, 'https://www.youtube.com/watch?v=a0NmZw_XFGE', 'YouTube', '/img/thumbnails/come-up-preview.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2024-08-04 11:00:00', 2, '2024-08-04 11:00:00', '2024-08-04 11:00:00'),
(9, 'Mans fragments līmenī \"COME UP\" // NCS Gauntlet konkurss', 'My part in \"COME UP\" // NCS Gauntlet Contest', 'come-up-roltonslv-part', 'video', 'RoltonaLV ieguldījums leģendārajā \"COME UP\" mega-kollabā Geometry Dash 2.2.', 'RoltonsLV\'s contribution to the legendary \"COME UP\" megacollab in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=KUp_S8L_h5M', 'YouTube', '/img/thumbnails/come-up-roltonslv-part.png', NULL, NULL, NULL, 'Geometry Dash', 1, 0, 1, 0, '2024-07-02 11:00:00', 2, '2024-07-02 11:00:00', '2024-07-02 11:00:00'),
(10, '[IZKĀRTOJUMS 2.2] \"COME UP\" autori Epil komanda // (NCS Gauntlet konkurss)', '[LAYOUT 2.2] \"COME UP\" by Team Epil // (NCS Gauntlet Contest)', 'come-up-layout', 'video', 'RoltonaLV un Epil komandas ieguldījums leģendārajā \"COME UP\" mega-kollabā Geometry Dash 2.2. Šis video ir tikai par izkārtojumu (layout), lai atklātu jauno līmeni', 'RoltonsLV and Team Epil\'s contribution to the legendary \"COME UP\" mega-collab Geometry Dash 2.2. This video is just about the layout to reveal the new level', NULL, NULL, 'https://www.youtube.com/watch?v=TxPSgkr_MN8', 'YouTube', '/img/thumbnails/come-up-layout.png', NULL, NULL, NULL, 'Geometry Dash', 1, 0, 1, 0, '2024-06-11 11:00:00', 2, '2024-06-11 11:00:00', '2024-06-11 11:00:00'),
(11, '[IZKĀRTOJUMS] \"hopefully a serious level\", autors RoltonsLV (es)', '[LAYOUT] \"hopefully a serious level\" by RoltonsLV (me)', 'hopefully-a-serious-level', 'video', 'RoltonaLV ieguldījums leģendārajā \"hopefully a serious level\" līmenī Geometry Dash 2.2. Šis video ir tikai par izkārtojumu (layout), lai atklātu jauno līmeni', 'RoltonsLV\'s contribution to the legendary \"hopefully a serious level\" level in Geometry Dash 2.2. This video is just about the layout to reveal the new level', NULL, NULL, 'https://www.youtube.com/watch?v=w3Qsza1tTkc', 'YouTube', '/img/thumbnails/hopefully-a-serious-level.png', NULL, NULL, NULL, 'Geometry Dash', 3, 0, 1, 0, '2023-10-16 11:00:00', 2, '2023-10-16 11:00:00', '2026-04-22 11:49:12'),
(12, '(The) Red * Room autors SpooFy // EFEKTU DĒMONS???', '(The) Red * Room by SpooFy // EFFECT DEMON???', 'the-red-room-by-spoofy', 'video', 'Spoofy ieguldījums leģendārajā \"(The) Red * Room autors SpooFy\" līmenī Geometry Dash 2.2. Šis video tika augšupielādēts izklaides nolūkos', 'Spoofy\'s contribution to the legendary \"(The) Red * Room autors SpooFy\" level in Geometry Dash 2.2. This video was uploaded for fun', NULL, NULL, 'https://www.youtube.com/watch?v=LQ1U_UPfKSc', 'YouTube', 'the-red-room-by-spoofy.png', NULL, NULL, NULL, 'Geometry Dash', 1, 0, 1, 0, '2023-09-28 11:00:00', 2, '2023-09-28 11:00:00', '2023-09-28 11:00:00'),
(13, 'iespējams, visnoderīgākie \"cat smoke\" Mirage CS2 spēlē', 'probably the most useful cat smoke in Mirage CS2', 'cs2-mirage-cat-smoke', 'video', 'Šis video tika izveidots apmācības nolūkos', 'This video was made for tutorial purposes', NULL, NULL, 'https://www.youtube.com/watch?v=_3kxz9lqFGw', 'YouTube', 'cs2-mirage-cat-smoke.png', NULL, NULL, NULL, 'CS2', 1, 0, 1, 0, '2023-09-23 11:00:00', 2, '2023-09-23 11:00:00', '2023-09-23 11:00:00'),
(14, '\"We Up\" autore ‪@emilyistaken‬ un vēl!! || MŪSU DĀVANA DRAUGAM NEOFAR', '\"We Up\" by ‪@emilyistaken‬ and more!! || OUR GIFT FOR NEOFAR', 'we-up', 'video', 'RoltonaLV un komandas ieguldījums leģendārajā \"We Up\" mega-kollabā Geometry Dash 2.2. Šis video ir tika izveidots, lai pārsteigtu Neofar ar jauno dāvanu', 'RoltonsLV and team\'s contribution to the legendary \"We Up\" megacollab in Geometry Dash 2.2. This video was created to surprise Neofar with his new gift.', NULL, NULL, 'https://www.youtube.com/watch?v=HU0f7lBdEoE', 'YouTube', 'we-up.png', NULL, NULL, NULL, 'Geometry Dash', 2, 0, 1, 0, '2023-08-27 11:00:00', 2, '2023-08-27 11:00:00', '2023-08-27 11:00:00'),
(15, 'Es uztaisīju 5 fragmentus 2 nedēļās!!', 'I made 5 parts in 2 weeks!!', 'i-made-5-parts-in-2-weeks', 'video', 'Īss video, kurā es parādu 5 fragmentus Geometry Dash līmeņos, ko uztaisīju divu nedēļu laikā.', 'Short video showcasing 5 parts in Geometry Dash levels that I created within two weeks.', NULL, NULL, 'https://www.youtube.com/watch?v=la9-9Bi4OSQ', 'YouTube', '/img/thumbnails/5-parts-2-weeks.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2023-07-26 11:00:00', 2, '2023-07-26 11:00:00', '2023-07-26 11:00:00'),
(16, 'Džona Meklērena Aicinājums.. BET AR BALSS IERAKSTIEM', 'John\'s Quest.. BUT IT HAS VOICELINES', 'johns-quest', 'video', 'John\'s Quest ASCII spēle ar balss ierakstiem', 'John\'s Quest ASCII game with voicelines', NULL, NULL, 'https://www.youtube.com/watch?v=B4hFc0m-pLc', 'YouTube', '/img/thumbnails/johns-quest.png', NULL, NULL, NULL, 'Coding', 0, 0, 1, 0, '2023-07-14 11:00:00', 2, '2023-07-14 11:00:00', '2023-07-14 11:00:00'),
(17, 'Čau, sen neesam tikušies :)', 'hey, it\'s been a while :)', 'hey-its-been-a-while', 'video', 'Īss video par atgriešanos un jaunumiem.', 'Short video about returning and updates.', NULL, NULL, 'https://www.youtube.com/watch?v=K8KDiSF5qsg', 'YouTube', '/img/thumbnails/its-been-a-while.png', NULL, NULL, NULL, 'Vlogs', 1, 0, 1, 0, '2023-07-09 11:00:00', 2, '2023-07-09 11:00:00', '2026-04-20 15:23:19'),
(18, 'Es mēģināju darīt kaut ko citu~', 'I tried doing something different~', 'i-tried-something-different', 'video', 'Mēģināju izveidot unikālu līmeni ar citādāku dizainu. Geometry Dash eksperimenti.', 'Attempted to create a unique level with a different design. Geometry Dash experiments.', NULL, NULL, 'https://www.youtube.com/watch?v=_fAEuDT7UN4', 'YouTube', '/img/thumbnails/something-different.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-12-28 12:00:00', 2, '2022-12-28 12:00:00', '2022-12-28 12:00:00'),
(19, 'Šis ir \"Color Burst\".. VISU LAIKU DUETISKĀKAIS', 'This is Color Burst.. THE MOST DUO OF ALL TIME', 'color-burst-duo', 'video', '\"Color Burst\" līmeņa šovs, duets ar citu veidotāju.', 'Showcase of the \"Color Burst\" level, a duo collaboration with another creator.', NULL, NULL, 'https://www.youtube.com/watch?v=eC7Jeb4oWlA', 'YouTube', '/img/thumbnails/color-burst.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-12-21 12:00:00', 2, '2022-12-21 12:00:00', '2022-12-21 12:00:00'),
(20, 'Reiz 2 Laiki..', 'Once Upon 2 Times..', 'once-upon-2-times', 'video', '\"Once Upon 2 Times\" līmeņa demonstrācija.', 'Showcase of the \"Once Upon 2 Times\" level.', NULL, NULL, 'https://www.youtube.com/watch?v=BM8gnwGu6ng', 'YouTube', '/img/thumbnails/once-upon-2-times.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-12-02 12:00:00', 2, '2022-12-02 12:00:00', '2022-12-02 12:00:00'),
(21, 'Truong slepenais dzimšanas dienas līmenis? :/', 'Truong\'s secret bday level? :/', 'truongs-secret-bday-level', 'video', 'Apskatām Truong slepeno dzimšanas dienas līmeni.', 'Checking out Truong\'s secret birthday level.', NULL, NULL, 'https://www.youtube.com/watch?v=6VysOHLkI8A', 'YouTube', '/img/thumbnails/truongs-bday-level.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-11-01 12:00:00', 2, '2022-11-01 12:00:00', '2022-11-01 12:00:00'),
(22, '[PREZENTĀCIJA] DRIFTCAT autors RoltonsLV (es) un citi || Otrā Nguoi Anh Em servera kollaba!!', '[SHOWCASE] DRIFTCAT by RoltonsLV (me) & others || The 2nd Nguoi Anh Em server collab!!', 'driftcat-showcase-collab', 'video', 'RoltonaLV un citu veidotāju \"DRIFTCAT\" kollabas prezentācija.', 'Showcase of the \"DRIFTCAT\" collaboration by RoltonsLV and other creators.', NULL, NULL, 'https://www.youtube.com/watch?v=TEx1OhH3MKk', 'YouTube', '/img/thumbnails/driftcat-showcase.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-10-30 12:00:00', 2, '2022-10-30 12:00:00', '2022-10-30 12:00:00'),
(23, '[IZKĀRTOJUMS] Alo Neqad autors RoltonsLV - Ieeja Discord Gauntlet CC', '[LAYOUT] Alo Neqad by RoltonsLV - Entry for Discord Gauntlet CC', 'alo-neqad-layout-cc', 'video', 'RoltonaLV izveidotais līmeņa izkārtojums \"Alo Neqad\" Discord Gauntlet CC konkursam.', 'Level layout \"Alo Neqad\" created by RoltonsLV for the Discord Gauntlet CC contest.', NULL, NULL, 'https://www.youtube.com/watch?v=ZGGdR3ORNco', 'YouTube', '/img/thumbnails/alo-neqad-layout.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-09-11 11:00:00', 2, '2022-09-11 11:00:00', '2022-09-11 11:00:00'),
(24, 'Heh.', 'heh.', 'heh', 'video', 'Īss un noslēpumains video.', 'Short and mysterious video.', NULL, NULL, 'https://www.youtube.com/watch?v=WJuinNw4pMA', 'YouTube', '/img/thumbnails/heh.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-08-12 11:00:00', 2, '2022-08-12 11:00:00', '2022-08-12 11:00:00'),
(25, 'DRIFTCAT CC REZULTĀTI... Bet tas ir īss video :/', 'DRIFTCAT CC RESULTS... But it\'s a short video :/', 'driftcat-cc-results-short', 'video', 'Īss video par \"DRIFTCAT\" Kollabas konkursu rezultātiem.', 'Short video about the results of the \"DRIFTCAT\" Collab Contest.', NULL, NULL, 'https://www.youtube.com/watch?v=0IEvlUnDims', 'YouTube', '/img/thumbnails/driftcat-cc-results.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-07-31 11:00:00', 2, '2022-07-31 11:00:00', '2022-07-31 11:00:00'),
(27, 'Līmeņa maiņas trijstūris ar TruongWF un PaganiniGD || MANA POV VEIDOŠANAS PROCESA SKATS', 'Level Swap Triangle /w TruongWF and PaganiniGD || MY POV BUILD PROCESS', 'level-swap-triangle-build-pov', 'video', 'Līmeņa maiņas trijstūra veidošanas process ar TruongWF un PaganiniGD.', 'The build process of the Level Swap Triangle with TruongWF and PaganiniGD.', NULL, NULL, 'https://www.youtube.com/watch?v=0IEvlUnDims', 'YouTube', '/img/thumbnails/level-swap-triangle-pov.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-07-08 11:00:00', 2, '2022-07-08 11:00:00', '2022-07-08 11:00:00'),
(29, 'DRIFTCAT Creator Contest!! [Informācija aprakstā]', 'DRIFTCAT Creator Contest!! [Info in the description]', 'driftcat-creator-contest', 'video', 'DRIFTCAT radītāju konkursa izsludināšana! Viss nepieciešamais aprakstā. Pievienojies un piedalies!', 'DRIFTCAT creator contest announcement! All details in the description. Join and participate!', NULL, NULL, 'https://www.youtube.com/watch?v=e6PYWahXNhQ', 'YouTube', '/img/thumbnails/driftcat-creator-contest.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-06-16 11:00:00', 2, '2022-06-16 11:00:00', '2022-06-16 11:00:00'),
(30, 'DRIFTCAT autors RoltonsLV un citi [PRIEKŠSKATA INFO]', 'DRIFTCAT by RoltonsLV and others [PREVIEW INFO]', 'driftcat-preview-info', 'video', 'DRIFTCAT projekta priekšskata informācija. RoltonsLV kopā ar citiem radītājiem strādā pie episka GD līmeņa!', 'DRIFTCAT project preview information. RoltonsLV together with other creators working on an epic GD level!', NULL, NULL, 'https://www.youtube.com/watch?v=HxYkgPnNTeU', 'YouTube', '/img/thumbnails/driftcat-preview-info.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-06-10 11:00:00', 2, '2022-06-10 11:00:00', '2022-06-10 11:00:00'),
(31, 'Mani 2 fragmenti DRIFTCAT līmenī autors es un citi! || GD 2.113', 'My 2 parts in DRIFTCAT by Me and others! || GD 2.113', 'driftcat-my-2-parts', 'video', 'Mani divi fragmenti DRIFTCAT mega-kollabā. Intensīva un izaicinošā Geometry Dash 2.113 spēle!', 'My two parts in the DRIFTCAT megacollab. Intense and challenging Geometry Dash 2.113 gameplay!', NULL, NULL, 'https://www.youtube.com/watch?v=h8wpbnjOzZI', 'YouTube', '/img/thumbnails/driftcat-my-2-parts.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-04-07 11:00:00', 2, '2022-04-07 11:00:00', '2022-04-07 11:00:00'),
(32, '[LAYOUT] DRIFTCAT autors RoltonsLV, TruongWF un citi!! (XL Easy Demon)', '[LAYOUT] DRIFTCAT by RoltonsLV, TruongWF and others!! (XL Easy Demon)', 'driftcat-layout', 'video', 'DRIFTCAT līmeņa pilns layout! XL Easy Demon līmenis, ko izstrādājuši RoltonsLV, TruongWF un citi radītāji.', 'Full layout of DRIFTCAT level! XL Easy Demon level created by RoltonsLV, TruongWF and other creators.', NULL, NULL, 'https://www.youtube.com/watch?v=vUw1ApUp-44', 'YouTube', '/img/thumbnails/driftcat-layout.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2022-01-19 12:00:00', 2, '2022-01-19 12:00:00', '2022-01-19 12:00:00'),
(33, 'Es biju slinks augšupielādēt šo, līdz..', 'I was lazy to upload this until..', 'lazy-upload', 'video', 'Video, kuru ilgi nebiju augšupielādējis. Beidzot tas ir šeit! Skatieties, kas notika.', 'Video I had been lazy to upload for a long time. Finally it\'s here! Watch what happened.', NULL, NULL, 'https://www.youtube.com/watch?v=NIHB6QYGAd8', 'YouTube', '/img/thumbnails/lazy-upload.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-12-27 12:00:00', 2, '2021-12-27 12:00:00', '2021-12-27 12:00:00'),
(34, 'TruongWF dzimšanas dienas līmeņi + īsas ziņas || Geometry Dash 2.113', 'TruongWF Birthday levels + Short News || Geometry Dash 2.113', 'truongwf-birthday-levels', 'video', 'Īpaši līmeņi, kas veltīti TruongWF dzimšanas dienai! Papildus arī īsas ziņas par GD komūnu.', 'Special levels dedicated to TruongWF\'s birthday! Plus short news about the GD community.', NULL, NULL, 'https://www.youtube.com/watch?v=KZvwOvmSmmQ', 'YouTube', '/img/thumbnails/truongwf-birthday.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-11-15 12:00:00', 2, '2021-11-15 12:00:00', '2021-11-15 12:00:00'),
(35, 'Mani mini CS:GO labākie momenti || Septembris 2021', 'My mini CS:GO Highlights || September 2021', 'csgo-highlights-sept-2021', 'video', 'Counter-Strike: Global Offensive labāko momentu apkopojums no septembra 2021. Episkākie kadri un situācijas!', 'Counter-Strike: Global Offensive highlights compilation from September 2021. Most epic shots and situations!', NULL, NULL, 'https://www.youtube.com/watch?v=Uy0NTXQzNCw', 'YouTube', '/img/thumbnails/csgo-highlights.png', NULL, NULL, NULL, 'CS:GO', 0, 0, 1, 0, '2021-09-23 11:00:00', 2, '2021-09-23 11:00:00', '2021-09-23 11:00:00'),
(36, 'Vivid Hue II 100% VERIFICĒTS AR LDM !!!', 'Vivid Hue II 100% VERIFIED WITH LDM !!!', 'vivid-hue-ii-verified', 'video', 'Vivid Hue II līmenis 100% verificēts ar Low Detail Mode! Episka uzvara pēc daudziem mēģinājumiem.', 'Vivid Hue II level 100% verified with Low Detail Mode! Epic victory after many attempts.', NULL, NULL, 'https://www.youtube.com/watch?v=Tetj9sn4VUw', 'YouTube', '/img/thumbnails/vivid-hue-ii-verified.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-06-16 11:00:00', 2, '2021-06-16 11:00:00', '2021-06-16 11:00:00'),
(37, 'Vivid Hue II verificēšanas process #1 (mēģinājumi un kļūdas)', 'Vivid Hue II Verifying Process #1 (runs and bloopers)', 'vivid-hue-ii-verifying-1', 'video', 'Pirmā daļa no Vivid Hue II verificēšanas procesa. Visi labākie un sliktākie mēģinājumi!', 'First part of Vivid Hue II verification process. All the best and worst attempts!', NULL, NULL, 'https://www.youtube.com/watch?v=O1aeYZ1_17k', 'YouTube', '/img/thumbnails/vivid-hue-ii-verifying.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-05-31 11:00:00', 2, '2021-05-31 11:00:00', '2021-05-31 11:00:00'),
(38, 'POSTAL 2 Multiplayer - Diena iesācēja dzīvē..', 'POSTAL 2 Multiplayer - A Day in a Life of a Noobie..', 'postal-2-multiplayer', 'video', 'POSTAL 2 multiplayer piedzīvojumi. Skatieties, kā iesācējs mēģina izdzīvot haotiskajā spēles pasaulē!', 'POSTAL 2 multiplayer adventures. Watch how a noobie tries to survive in the chaotic game world!', NULL, NULL, 'https://www.youtube.com/watch?v=3ealwZo-0ss', 'YouTube', '/img/thumbnails/postal-2-multiplayer.png', NULL, NULL, NULL, 'POSTAL 2', 0, 0, 1, 0, '2021-04-08 11:00:00', 2, '2021-04-08 11:00:00', '2021-04-08 11:00:00'),
(39, '- = {bez nosaukuma} = -', '- = {no title} = -', 'no-title-video', 'video', 'Īpašs video bez nosaukuma. Noslēpumains saturs, ko nevarat palaist garām!', 'Special video without a title. Mysterious content you can\'t miss!', NULL, NULL, 'https://www.youtube.com/watch?v=1tZz6waHC8U', 'YouTube', '/img/thumbnails/no-title-video.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-03-25 12:00:00', 2, '2021-03-25 12:00:00', '2021-03-25 12:00:00'),
(40, 'Episode 1: Ak velns, mēs atkal sākam.. kopā ar kool kids klub biedriem || Five Nights At Candy\'s 2', 'Episode 1: Aw shet, here we go again.. ft. kool kids klub members || Five Nights At Candy\'s 2', 'fnac2-episode-1', 'video', 'Pirmā epizode Five Nights At Candy\'s 2 spēlē kopā ar kool kids klub biedriem. Biedējošs saturs!', 'First episode of Five Nights At Candy\'s 2 with kool kids klub members. Scary content ahead!', NULL, NULL, 'https://www.youtube.com/watch?v=JZosvQI_BQ4', 'YouTube', '/img/thumbnails/fnac2-episode-1.png', NULL, NULL, NULL, 'FNAC 2', 0, 0, 1, 0, '2021-03-04 12:00:00', 2, '2021-03-04 12:00:00', '2021-03-04 12:00:00'),
(41, 'Mans otrais fragments Final Moments!!', 'My second part in Final Moments!!', 'final-moments-second-part', 'video', 'Mans otrais ieguldījums Final Moments mega-kollabā. Vēl intensīvāks un sarežģītāks fragments!', 'My second contribution to Final Moments megacollab. Even more intense and complex part!', NULL, NULL, 'https://www.youtube.com/watch?v=mdL5ny303fo', 'YouTube', '/img/thumbnails/final-moments-part2.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2021-03-03 12:00:00', 2, '2021-03-03 12:00:00', '2021-03-03 12:00:00'),
(42, 'Nē tu', 'No u', 'no-u', 'video', 'Īss un kodolīgs video ar humoru. Klasiskā \"No u\" atbilde!', 'Short and concise video with humor. The classic \"No u\" response!', NULL, NULL, 'https://www.youtube.com/watch?v=6KzuujB2T38', 'YouTube', '/img/thumbnails/no-u.png', NULL, NULL, NULL, 'Humor', 0, 0, 1, 0, '2021-03-01 12:00:00', 2, '2021-03-01 12:00:00', '2021-03-01 12:00:00'),
(43, 'Minecraft Speedrun Ep.1: Cirsto koku kopā ar DatKaktuz', 'Minecraft Speedrun Ep.1: Chopin\' wood ft. DatKaktuz', 'minecraft-speedrun-ep1', 'video', 'Pirmā Minecraft speedrun epizode! Skatieties, kā es un DatKaktuz mēģinām uzstādīt rekordu.', 'First Minecraft speedrun episode! Watch me and DatKaktuz trying to set a record.', NULL, NULL, 'https://www.youtube.com/watch?v=Y9f9JA0JRTs', 'YouTube', '/img/thumbnails/minecraft-speedrun-ep1.png', NULL, NULL, NULL, 'Minecraft', 0, 0, 1, 0, '2021-02-28 12:00:00', 2, '2021-02-28 12:00:00', '2021-02-28 12:00:00'),
(44, 'Vēl viens tests', 'Shitty test again', 'test-video-again', 'video', 'Eksperimentāls video tests. Mācās un uzlabojas!', 'Experimental video test. Learning and improving!', NULL, NULL, 'https://www.youtube.com/watch?v=3vQcoo6gnZQ', 'YouTube', '/img/thumbnails/test-video.png', NULL, NULL, NULL, 'Other', 0, 0, 1, 0, '2021-02-26 18:50:00', 2, '2021-02-26 18:50:00', '2021-02-26 18:50:00'),
(45, 'Paziņojumi (nedaudz \"eh\" viens, man nepatīk to skatīties)', 'Announcements (kinda \'\'eh\'\' one I\'m not enjoying watching it)', 'announcements-video', 'video', 'Svarīgi paziņojumi par kanālu un projektiem. Jānoklausās!', 'Important announcements about the channel and projects. Must listen!', NULL, NULL, 'https://www.youtube.com/watch?v=8F0mfzWvLDg', 'YouTube', '/img/thumbnails/announcements-video.png', NULL, NULL, NULL, 'Other', 0, 0, 1, 0, '2021-01-25 18:50:00', 2, '2021-01-25 18:50:00', '2021-01-25 18:50:00'),
(46, 'Kā kļūt labākam GD? (Bet 1 sekundē)', 'How to become better at GD? (But in 1 second)', 'gd-tips-1-second', 'video', 'Ātrākais padoms, kā uzlabot savas GD prasmes! Tikai 1 sekunde!', 'The fastest tip on how to improve your GD skills! Just 1 second!', NULL, NULL, 'https://www.youtube.com/watch?v=GK40gAiniac', 'YouTube', '/img/thumbnails/gd-tips.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-12-28 18:50:00', 2, '2020-12-28 18:50:00', '2020-12-28 18:50:00'),
(47, 'Kad TLG spēlē Krunker Ep. 1 (Uzfilmēts pirmdienā)', 'When TLG plays Krunker Ep. 1 (Made on Monday)', 'tlg-krunker-ep1', 'video', 'TLG pirmā epizode Krunker spēlē. Skatieties episkus šāvienus un smieklīgas situācijas!', 'TLG\'s first episode playing Krunker. Watch epic shots and funny situations!', NULL, NULL, 'https://www.youtube.com/watch?v=8vi8MaQXRtA', 'YouTube', '/img/thumbnails/tlg-krunker.png', NULL, NULL, NULL, 'Krunker', 0, 0, 1, 0, '2020-12-08 18:50:00', 2, '2020-12-08 18:50:00', '2020-12-08 18:50:00'),
(48, 'Neveikla montāža', 'Cringy edit', 'cringy-edit', 'video', 'Humora pārpilns video ar dīvainu montāžu. Nesmieti par daudz!', 'Humor-filled video with awkward editing. Don\'t laugh too much!', NULL, NULL, 'https://www.youtube.com/watch?v=CrsHjFcVryQ', 'YouTube', '/img/thumbnails/cringy-edit.png', NULL, NULL, NULL, 'Humor', 0, 0, 1, 0, '2020-12-07 18:50:00', 2, '2020-12-07 18:50:00', '2020-12-07 18:50:00'),
(49, 'Muļķīga Counter Strike 1.6 spēle || Spēlēju kopā ar RoltonsLV (es)', 'Silly Counter Strike 1.6 gameplay || Gaming with RoltonsLV (me)', 'cs16-silly-gameplay', 'video', 'Klasiskā Counter Strike 1.6 spēle ar humoru un smieklīgām situācijām!', 'Classic Counter Strike 1.6 gameplay with humor and funny situations!', NULL, NULL, 'https://www.youtube.com/watch?v=o6kOwg0z4gE', 'YouTube', '/img/thumbnails/cs16-gameplay.png', NULL, NULL, NULL, 'CS 1.6', 0, 0, 1, 0, '2020-11-27 21:55:05', 2, '2020-11-27 21:55:05', '2020-11-27 21:55:05'),
(50, 'Beidzot mēs to darām, puikas! [Bez nosaukuma] kollabs mana un TruongWF spēle!', 'We are finally doing it bois! [No name] collab gameplay by me and TruongWF!', 'no-name-collab', 'video', 'Bezdievīgs kollabs bez nosaukuma! Es un TruongWF kopā strādājam pie episka projekta.', 'Awesome collab with no name! Me and TruongWF working together on an epic project.', NULL, NULL, 'https://www.youtube.com/watch?v=4zseBYO7_PE', 'YouTube', '/img/thumbnails/no-name-collab.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-11-15 21:55:05', 2, '2020-11-15 21:55:05', '2020-11-15 21:55:05'),
(51, 'Akaichi II priekšskatījums 1 (Gaidāms Insane?)', 'Akaichi II sneak peek 1 (Upcoming Insane?)', 'akaichi-ii-sneak-peek-1', 'video', 'Pirmais Akaichi II priekšskatījums! Iespējams, būs Insane grūtības līmenis.', 'First Akaichi II sneak peek! Possibly will be Insane difficulty level.', NULL, NULL, 'https://www.youtube.com/watch?v=abmIGQ-2EC4', 'YouTube', '/img/thumbnails/akaichi-ii-sneak.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-11-10 21:55:05', 2, '2020-11-10 21:55:05', '2020-11-10 21:55:05'),
(52, 'Moves Like Stalker layout (bet saukts Stalker Song lol)', 'Moves Like Stalker layout (but called Stalker Song lol)', 'moves-like-stalker-layout', 'video', 'Layout līmenim Moves Like Stalker, kaut gan saukts par Stalker Song. Konfūzija garantēta!', 'Layout for Moves Like Stalker level, although called Stalker Song. Confusion guaranteed!', NULL, NULL, 'https://www.youtube.com/watch?v=XtkgkJE1fV8', 'YouTube', '/img/thumbnails/stalker-layout.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-24 20:56:05', 2, '2020-10-24 20:56:05', '2020-10-24 20:56:05'),
(53, 'PONPONPON pilns layout!!!! (atvainojiet par video ar kļūdām)', 'PONPONPON full layout!!!! (sorry for buggy video)', 'ponponpon-full-layout', 'video', 'Pilns PONPONPON līmeņa layout! Video ir nedaudz ar kļūdām, bet saturs ir episkais!', 'Full PONPONPON level layout! Video is a bit buggy, but the content is epic!', NULL, NULL, 'https://www.youtube.com/watch?v=nZdkjwk7_6A', 'YouTube', '/img/thumbnails/ponponpon-full.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-24 20:55:05', 2, '2020-10-24 20:55:05', '2020-10-24 20:55:05'),
(54, 'PONPONPON layout priekšskatījums 2 (layout ir, jā)', 'PONPONPON layout preview 2 (the layout is, yeah)', 'ponponpon-preview-2', 'video', 'Otrais PONPONPON layout priekšskatījums. Layout virzās uz priekšu!', 'Second PONPONPON layout preview. The layout is progressing forward!', NULL, NULL, 'https://www.youtube.com/watch?v=5zbgAghu5_Q', 'YouTube', '/img/thumbnails/ponponpon-preview2.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-23 20:55:05', 2, '2020-10-23 20:55:05', '2020-10-23 20:55:05'),
(55, 'PONPONPON layout priekšskatījums 1 (es pilnībā mīlu šo layout)', 'PONPONPON layout preview 1 (I absolutely love this layout)', 'ponponpon-preview-1', 'video', 'Pirmais PONPONPON layout priekšskatījums. Es pilnībā esmu sajūsmā par šo projektu!', 'First PONPONPON layout preview. I\'m absolutely excited about this project!', NULL, NULL, 'https://www.youtube.com/watch?v=U4wBKZCtVhw', 'YouTube', '/img/thumbnails/ponponpon-preview1.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-22 20:55:05', 2, '2020-10-22 20:55:05', '2020-10-22 20:55:05'),
(56, 'TLG mācās alfabētu kopā ar Ic3Tea!', 'TLG learns alphabet with Ic3Tea!', 'tlg-alphabet', 'video', 'Humora pārpilns video, kurā TLG mācās alfabētu kopā ar Ic3Tea. Smieklīgi!', 'Humor-filled video where TLG learns the alphabet with Ic3Tea. Hilarious!', NULL, NULL, 'https://www.youtube.com/watch?v=7GtCsWch04s', 'YouTube', '/img/thumbnails/tlg-alphabet.png', NULL, NULL, NULL, 'Humor', 0, 0, 1, 0, '2020-10-20 20:55:05', 2, '2020-10-20 20:55:05', '2020-10-20 20:55:05'),
(57, 'Vinterlecken autors klaurosssS 100%', 'Vinterlecken by klaurosssS 100%', 'vinterlecken-100', 'video', 'Vinterlecken līmenis 100%! klaurosssS izpildījums pilnībā paveikts.', 'Vinterlecken level 100%! klaurosssS performance fully completed.', NULL, NULL, 'https://www.youtube.com/watch?v=obP1I5Tt1yY', 'YouTube', '/img/thumbnails/vinterlecken.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-17 20:55:05', 2, '2020-10-17 20:55:05', '2020-10-17 20:55:05'),
(58, 'Helovīna kollaba layout priekšskatījums 1', 'Halloween collab layout sneak peak 1', 'halloween-collab-sneak', 'video', 'Pirmais priekšskatījums Helovīna kollaba layoutam. Biedējošs un episkais!', 'First sneak peek of Halloween collab layout. Scary and epic!', NULL, NULL, 'https://www.youtube.com/watch?v=vLqHIM3DwPA', 'YouTube', '/img/thumbnails/halloween-collab.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-10-12 20:55:05', 2, '2020-10-12 20:55:05', '2020-10-12 20:55:05'),
(59, 'TLG skatās YouTube (lasiet aprakstus)', 'TLG is watching YouTube (read descriptions)', 'tlg-watching-youtube', 'video', 'TLG skatās YouTube video. Sīkāka informācija aprakstā!', 'TLG is watching YouTube videos. More details in the description!', NULL, NULL, 'https://www.youtube.com/watch?v=5iKdRvlryXQ', 'YouTube', '/img/thumbnails/tlg-youtube.png', NULL, NULL, NULL, 'Humor', 0, 0, 1, 0, '2020-09-20 20:55:05', 2, '2020-09-20 20:55:05', '2020-09-20 20:55:05'),
(60, 'Šis muļķis patiešām to uzvarēja??? (kopā ar DatKaktuz)', 'That retard actually beat it??? (ft. DatKaktuz)', 'retard-beat-it', 'video', 'Neticams sasniegums! DatKaktuz patiešām uzvarēja šo grūto līmeni!', 'Unbelievable achievement! DatKaktuz actually beat this difficult level!', NULL, NULL, 'https://www.youtube.com/watch?v=uohN01T9WMI', 'YouTube', '/img/thumbnails/retard-beat-it.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-09-19 20:56:05', 2, '2020-09-19 20:56:05', '2020-09-19 20:56:05'),
(61, 'Skatieties, kā šis slimais puisis cieš, uzveicot insane izaicinājumu (kopā ar DatKaktuz)', 'Watch this sick guy suffering while beating an insane challenge (ft. DatKaktuz)', 'sick-guy-suffering', 'video', 'DatKaktuz cieš, mēģinot uzvarēt insane izaicinājumu. Episka cīņa!', 'DatKaktuz suffers while trying to beat an insane challenge. Epic struggle!', NULL, NULL, 'https://www.youtube.com/watch?v=hRwLhFcSeZQ', 'YouTube', '/img/thumbnails/sick-suffering.png', NULL, NULL, NULL, 'Geometry Dash', 0, 0, 1, 0, '2020-09-19 20:55:05', 2, '2020-09-19 20:55:05', '2020-09-19 20:55:05'),
(62, 'Muļķīgs JoJo meme (MroxSu\'s bababuy remake)', 'Retarted JoJo meme (remake of MroxSu\'s bababuy)', 'jojo-meme-remake', 'video', 'JoJo meme remake no MroxSu oriģināla bababuy. Smieklīgs un absurds!', 'JoJo meme remake from MroxSu\'s original bababuy. Funny and absurd!', NULL, NULL, 'https://www.youtube.com/watch?v=UVy4-v_lo5E', 'YouTube', '/img/thumbnails/jojo-meme.png', NULL, NULL, NULL, 'Humor', 0, 0, 1, 0, '2020-09-15 20:55:05', 2, '2020-09-15 20:55:05', '2020-09-15 20:55:05'),
(63, 'Mans Pirmais Blogs', 'My First Blog', 'first-blog', 'blog', 'Īss apraksts par šo blog ierakstu.', 'Short description about this blog post.', '<h2>Sveiki!</h2><p>Šis ir mans pirmais blog ieraksts. Lasi vairāk zemāk!</p>', '<h2>Hello!</h2><p>This is my first blog post. Read more below!</p>', NULL, NULL, '/img/Blogs/first-blog-pic.png', '/img/Blogs/first-blog-pic.png', '\"[\\\"blogs\\\\\\/first-blog-pic.png\\\",\\\"blogs\\\\\\/second-blog-pic.png\\\"]\"', NULL, 'Life update', 13, 0, 1, 1, '2025-12-10 01:55:10', 2, '2025-12-10 01:55:10', '2026-02-18 14:43:43'),
(65, 'Kā Es Sāku Veidot Geometry Dash Līmeņus', 'How I Started Creating Geometry Dash Levels', 'how-i-started-making-gd-levels', 'blog', 'Mans ceļojums no pilnīga iesācēja līdz GD radītāja. Uzzini, kā es sāku un kādas kļūdas es pieļāvu!', 'My journey from complete beginner to GD creator. Learn how I started and what mistakes I made!', '<div class=\"blog-post\">\r\n        <h2>Mans Sākums ar Geometry Dash</h2>\r\n        <p>2015. gadā es ielādēju Geometry Dash uz sava telefona. Sākumā es tikai spēlēju citu cilvēku līmeņus, bet pēc pāris mēnešiem es nolēmu mēģināt izveidot savu.</p>\r\n\r\n        <br>\r\n        <h3>Pirmie iespaidi</h3>\r\n        <p>Mans pirmais līmenis bija interesants! Šeit ir dažas nianses:</p>\r\n        <ul>\r\n            <li><strong>Līmeņa nosaukums</strong> - RalphLocked</li>\r\n            <li><strong>Pamatkrāsa</strong> - Pelēka</li>\r\n            <li><strong>Izmantoju daudz asmeņus</strong> - Jā</li>\r\n            <li><strong>Dziesmu, kuru izmantoju</strong> - Deadlocked</li>\r\n        </ul>\r\n\r\n        <br>\r\n        <p>Pēc daudziem mēģinājumiem un kļūdām, es iemācījos dažas svarīgas lietas:</p>\r\n        <ol>\r\n            <li><strong>Mazāk ir vairāk</strong> - Nevajag pārpildīt līmeni ar objektiem</li>\r\n            <li><strong>Sync ir svarīgs</strong> - Objektiem jāsinhronizējas ar beat</li>\r\n            <li><strong>Test, test, test</strong> - Spēlē savu līmeni simtiem reižu</li>\r\n            <li><strong>Feedback ir vērtīgs</strong> - Prasiet citiem viedokli</li>\r\n        </ol>\r\n\r\n        <blockquote style=\"border-left: 4px solid #dc2626; padding: 20px; background: #f9fafb; margin: 20px 0; color: #666;\">\r\n            \"Neviens nav ideāls no pirmās reizes. Galvenais ir mācīties no savām kļūdām un nekad nepadoties!\" - RoltonsLV\r\n        </blockquote>\r\n\r\n        <h3>Mani Padomi Iesācējiem:</h3>\r\n        <p>Ja tu arī gribi sākt veidot līmeņus, šeit ir daži padomi no manas pieredzes:</p>\r\n        <ul>\r\n            <li>Sāc ar ko vienkāršu - nepārcenties</li>\r\n            <li>Skaties uz citiem radītājiem un mācies</li>\r\n            <li>Izmanto tutorials YouTube</li>\r\n            <li>Pievienojies GD komūnai Discord vai Reddit</li>\r\n            <li>Praktizējies katru dienu</li>\r\n        </ul>\r\n\r\n        <h2>Nākotnes Plāni</h2>\r\n        <p>Šobrīd es strādāju pie jauna mega-kollaba projekta kopā ar citiem radītājiem. Sekojiet līdzi jaunumiem manā YouTube kanālā un Discord serverī!</p>\r\n        \r\n        <p><strong>Paldies par lasīšanu! 🎮✨</strong></p>\r\n    </div>', '<div class=\"blog-post\">\r\n        <h2>My Start with Geometry Dash</h2>\r\n        <p>In 2015 I downloaded Geometry Dash on my phone. At first I just played other people\'s levels, but after a few months I decided to try creating my own.</p>\r\n        <br>\r\n        <h3>First Impressions</h3>\r\n        <p>My first level was interesting! Here are some nuanses:</p>\r\n        <ul>\r\n            <li><strong>Level name</strong> - RalphLocked</li>\r\n            <li><strong>Main color</strong> - Gray</li>\r\n            <li><strong>Use a lot of spikes</strong> - Yes</li>\r\n            <li><strong>The song I used</strong> - Deadlocked</li>\r\n        </ul>\r\n\r\n        <br>\r\n        <p>After many attempts and mistakes, I learned some important things:</p>\r\n        <ol>\r\n            <li><strong>Less is more</strong> - Don\'t overcrowd the level with objects</li>\r\n            <li><strong>Sync is important</strong> - Objects must sync with beat</li>\r\n            <li><strong>Test, test, test</strong> - Play your level hundreds of times</li>\r\n            <li><strong>Feedback is valuable</strong> - Ask others for opinions</li>\r\n        </ol>\r\n\r\n        <blockquote style=\"border-left: 4px solid #dc2626; padding: 20px; background: #f9fafb; margin: 20px 0; color: #666;\">\r\n            \"Nobody is perfect from the first try. The key is to learn from your mistakes and never give up!\" - RoltonsLV\r\n        </blockquote>\r\n\r\n        <h3>My Tips for Beginners:</h3>\r\n        <p>If you also want to start creating levels, here are some tips from my experience:</p>\r\n        <ul>\r\n            <li>Start with something simple - don\'t overdo it</li>\r\n            <li>Watch other creators and learn</li>\r\n            <li>Use YouTube tutorials</li>\r\n            <li>Join GD community on Discord or Reddit</li>\r\n            <li>Practice every day</li>\r\n        </ul>\r\n\r\n        <h2>Future Plans</h2>\r\n        <p>Currently I\'m working on a new megacollab project together with other creators. Follow for updates on my YouTube channel and Discord server!</p>\r\n        \r\n        <p><strong>Thanks for reading! 🎮✨</strong></p>\r\n    </div>', NULL, NULL, '/img/Blogs/how-i-started-making-gd-levels.png', '/img/Blogs/how-i-started-making-gd-levels.png', '[\"/img/Blogs/second-blog-pic.png\"]', NULL, 'Geometry Dash', 18, 1, 1, 1, '2025-12-10 02:32:10', 2, '2025-12-10 02:32:10', '2026-04-22 11:38:19'),
(66, 'Kad LupoDZN pievienojas Australind.. un dabūja to gatavu (GP no manis)', 'When LupoDZN joins Australind.. and ate it (GP by Me)', 'lupodzn-australind', 'video', 'Mans un LupoDZN ieguldījums leģendārajā \"Australind\" mega-kollabā Geometry Dash 2.2.', 'My and LupoDZN\'s contribution to the legendary \"Australind\" megacollab in Geometry Dash 2.2.', NULL, NULL, 'https://www.youtube.com/watch?v=pymqvhww24A', 'YouTube', '/img/thumbnails/lupodzn-australind.png', NULL, NULL, NULL, 'Geometry Dash', 83, 4, 1, 1, '2025-12-16 12:00:00', 2, '2025-12-16 12:14:45', '2026-04-25 11:03:48'),
(78, 'RalphMania veikals tagad pieejams visā Eiropā!', 'RalphMania store now available across Europe!', 'ralphmania-now-available-europe', 'post', 'Mēs ar prieku paziņojam, ka tagad nosūtām pasūtījumus uz visām ES valstīm.', 'We are excited to announce that we now ship to all EU countries.', '<p>Pēc ilga darba mēs beidzot varam paziņot, ka <strong>RalphMania</strong> veikals tagad pieņem pasūtījumus no visām Eiropas Savienības valstīm!</p>\r\n<h3>Piegādes laiks:</h3>\r\n<ul>\r\n<li><strong>Latvija:</strong> 1-3 darba dienas</li>\r\n<li><strong>Lietuva, Igaunija:</strong> 3-5 darba dienas</li>\r\n<li><strong>Pārējā ES:</strong> 5-10 darba dienas</li>\r\n</ul>\r\n<p>Paldies, ka esat ar mums! 🎉</p>', '<p>After a lot of work, we can finally announce that the <strong>RalphMania</strong> store now accepts orders from all European Union countries!</p>\r\n<h3>Delivery times:</h3>\r\n<ul>\r\n<li><strong>Latvia:</strong> 1-3 business days</li>\r\n<li><strong>Lithuania, Estonia:</strong> 3-5 business days</li>\r\n<li><strong>Rest of EU:</strong> 5-10 business days</li>\r\n</ul>\r\n<p>Thank you for being with us! 🎉</p>', NULL, NULL, '/img/Posts/ralphmania-in-europe.png', 'news-europe-shipping.jpg', NULL, NULL, 'Veikals', 163, 42, 1, 1, '2025-12-15 08:00:00', 2, '2026-01-13 20:46:46', '2026-04-21 10:40:35'),
(80, 'Sadarbība ar satura veidotāju no Vjetnamas - truongwf', 'Collaboration with a Content Creator from Vietnam - truongwf', 'youtube-collaboration-with-truongwf-announcement', 'post', 'Mēs uzsākam sadarbību ar vienu no populārākajiem Vjetnamas YouTube kanāliem.', 'We are starting a collaboration with one of the most popular Vietnam YouTube channels.', '<p>Lieliskas ziņas! 🎬</p>\r\n<p>RalphMania ir uzsācis sadarbību ar <strong>truongwf</strong>, kas nozīmē:</p>\r\n<ul>\r\n<li>Ekskluzīvus video par mūsu produktiem</li>\r\n<li>Īpašus piedāvājumus sekotājiem</li>\r\n<li>Kopīgus konkursus ar balvām</li>\r\n</ul>\r\n<p>Sekojiet līdzi mūsu sociālajiem tīkliem, lai uzzinātu vairāk!</p>\r\n<p><strong>Apmeklēt YouTube kanālu</strong> → <a href=\"https://www.youtube.com/@chutruongwaifu\">https://www.youtube.com/@chutruongwaifu</a></p>', '<p>Great news! 🎬</p>\r\n<p>RalphMania has started a collaboration with <strong>truongwf</strong>, which means:</p>\r\n<ul>\r\n<li>Exclusive videos about our products</li>\r\n<li>Special offers for followers</li>\r\n<li>Joint contests with prizes</li>\r\n</ul>\r\n<p>Follow our social media for more updates!</p>\r\n<p><strong>Visit YouTube channel</strong> → <a href=\"https://www.youtube.com/@chutruongwaifu\">https://www.youtube.com/@chutruongwaifu</a></p>', NULL, 'YouTube', '/img/Posts/1776858416_d2c30cf3-6136-44a2-a47b-b947d1461a40.png', '/img/Posts/1776858416_d2c30cf3-6136-44a2-a47b-b947d1461a40.png', NULL, NULL, 'Sadarbības', 99, 24, 1, 0, '2025-12-01 01:00:00', 2, '2026-01-13 20:46:46', '2026-04-22 15:09:52'),
(82, 'Plānoti apkopes darbi 20. janvārī', 'Scheduled maintenance on January 20th', 'maintenance-january-20', 'announcement', 'Plānoti apkopes darbi, vietne īslaicīgi nebūs pieejama.', 'Scheduled maintenance, site will be temporarily unavailable.', '<p>⚠️ <strong>Svarīgs paziņojums!</strong></p>\r\n<p>2026. gada <strong>20. janvārī</strong> no plkst. <strong>02:00 līdz 06:00</strong> (Latvijas laiks) mūsu vietnē tiks veikti plānoti apkopes darbi.</p>\r\n<h3>Šajā laikā:</h3>\r\n<ul>\r\n<li>❌ Vietne nebūs pieejama</li>\r\n<li>❌ Pasūtījumi netiks apstrādāti</li>\r\n<li>✅ E-pasti tiks saņemti pēc darbiem</li>\r\n</ul>\r\n<p>Atvainojamies par sagādātajām neērtībām!</p>', '<p>⚠️ <strong>Important announcement!</strong></p>\r\n<p>On <strong>January 20th, 2026</strong>, from <strong>02:00 to 06:00</strong> (Latvia time), our website will undergo scheduled maintenance.</p>\r\n<h3>During this time:</h3>\r\n<ul>\r\n<li>❌ Website will be unavailable</li>\r\n<li>❌ Orders will not be processed</li>\r\n<li>✅ Emails will be received after maintenance</li>\r\n</ul>\r\n<p>We apologize for any inconvenience!</p>', NULL, NULL, '/img/Announcements/maintenance-work.png', NULL, NULL, NULL, 'Tehniski', 56, 5, 1, 0, '2026-01-10 10:00:00', 2, '2026-01-13 20:46:46', '2026-04-20 14:43:29'),
(83, 'Izmaiņas piegādes cenās no 2026. gada', 'Delivery price changes from 2026', 'delivery-price-changes-2026', 'announcement', 'Informējam par izmaiņām piegādes cenās, kas stāsies spēkā 2026. gadā.', 'Information about delivery price changes effective 2026.', '<p>📦 <strong>Piegādes cenu izmaiņas</strong></p>\r\n<p>No 2026. gada <strong>1. februāra</strong> stājas spēkā jaunas piegādes cenas:</p>\r\n<table style=\"width: 100%; border-collapse: collapse; margin: 1rem 0;\">\r\n<thead>\r\n<tr style=\"background: #f3f4f6;\">\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: left;\">Valsts</th>\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">Vecā cena</th>\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">Jaunā cena</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Latvija</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">2.99 €</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>3.49 €</strong></td></tr>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Lietuva/Igaunija</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">4.99 €</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>5.49 €</strong></td></tr>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Pārējā ES</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">9.99 €</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>10.99 €</strong></td></tr>\r\n</tbody>\r\n</table>\r\n<p>✅ <em>Bezmaksas piegāde pasūtījumiem virs 50 € saglabājas!</em></p>', '<p>📦 <strong>Delivery Price Changes</strong></p>\r\n<p>New delivery prices effective <strong>February 1st, 2026</strong>:</p>\r\n<table style=\"width: 100%; border-collapse: collapse; margin: 1rem 0;\">\r\n<thead>\r\n<tr style=\"background: #f3f4f6;\">\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: left;\">Country</th>\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">Old price</th>\r\n<th style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">New price</th>\r\n</tr>\r\n</thead>\r\n<tbody>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Latvia</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">€2.99</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>€3.49</strong></td></tr>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Lithuania/Estonia</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">€4.99</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>€5.49</strong></td></tr>\r\n<tr><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb;\">Rest of EU</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\">€9.99</td><td style=\"padding: 0.75rem; border: 1px solid #e5e7eb; text-align: right;\"><strong>€10.99</strong></td></tr>\r\n</tbody>\r\n</table>\r\n<p>✅ <em>Free shipping for orders over €50 remains!</em></p>', NULL, NULL, '/img/Announcements/shipping-cost.png', NULL, NULL, NULL, 'Piegāde', 78, 12, 1, 0, '2026-01-05 08:00:00', 2, '2026-01-13 20:46:46', '2026-01-13 20:46:46'),
(84, 'Ziemassvētku un Jaungada darba laiks', 'Christmas and New Year working hours', 'christmas-working-hours-2025', 'announcement', 'Informācija par darba laiku Ziemassvētku un Jaungada periodā.', 'Information about working hours during Christmas and New Year period.', '<p>🎄 <strong>Ziemassvētku un Jaungada darba laiks</strong></p>\r\n<p>Informējam par mūsu darba laiku svētku periodā:</p>\r\n<ul>\r\n<li><strong>23. decembris:</strong> 9:00 - 15:00</li>\r\n<li><strong>24.-26. decembris:</strong> ❌ Slēgts</li>\r\n<li><strong>27.-30. decembris:</strong> 10:00 - 17:00</li>\r\n<li><strong>31. decembris:</strong> 9:00 - 14:00</li>\r\n<li><strong>1. janvāris:</strong> ❌ Slēgts</li>\r\n<li><strong>No 2. janvāra:</strong> ✅ Normālais darba laiks</li>\r\n</ul>\r\n<p>Priecīgus svētkus! 🎅🎁</p>', '<p>🎄 <strong>Christmas and New Year Working Hours</strong></p>\r\n<p>Information about our working hours during the holiday period:</p>\r\n<ul>\r\n<li><strong>December 23:</strong> 9:00 AM - 3:00 PM</li>\r\n<li><strong>December 24-26:</strong> ❌ Closed</li>\r\n<li><strong>December 27-30:</strong> 10:00 AM - 5:00 PM</li>\r\n<li><strong>December 31:</strong> 9:00 AM - 2:00 PM</li>\r\n<li><strong>January 1:</strong> ❌ Closed</li>\r\n<li><strong>From January 2:</strong> ✅ Normal working hours</li>\r\n</ul>\r\n<p>Happy holidays! 🎅🎁</p>', NULL, NULL, '/img/Announcements/xmas-ny.png', NULL, NULL, NULL, 'Darba laiks', 129, 18, 1, 1, '2025-12-20 06:00:00', 2, '2026-01-13 20:46:46', '2026-03-26 09:34:28'),
(86, 'Poļu rūķis', 'Polish Gnome', 'polish-gnome', 'video', 'Viņš izskatījās baigi interesants, tāpēc savu pirmo TikTok izveidoju', 'He looked very interesting, so I made my first TikTok', NULL, NULL, 'https://www.tiktok.com/@realroltonslv/video/7454934264530996502', 'TikTok', '/img/thumbnails/polish-gnome.png', NULL, NULL, NULL, 'Humor', 1, 0, 1, 0, '2020-09-15 20:55:05', 2, '2020-09-15 20:55:05', '2026-04-20 14:54:47'),
(87, 'Instagram veida ziņa', 'Instagram-type post', 'instagram-veida-zina', 'post', 'izmēģinu jauno fīču', 'trying out new feature', 'Čau! Pirmo reizi tagad izmēģinu publicēt Instagram veida ziņu. Ceru, ka Jums patiks :)', 'Hello! I\'m now trying out to publish an Instagram-type of a post for the first time. Hope you\'ll like it :)', NULL, NULL, '/img/Posts/1776766069_screenshot-59.png', '/img/Posts/1776766069_screenshot-59.png', '[\"1776766069_69e74c7532f5d_screenshot-1196.png\",\"1776766069_69e74c7533380_screenshot-1195.png\",\"1776766069_69e74c75337de_screenshot-1194.png\"]', NULL, 'Tests', 23, 1, 1, 0, '2026-04-21 04:08:00', 5, '2026-04-21 10:07:49', '2026-04-24 20:40:06');
INSERT INTO `content` (`id`, `title_lv`, `title_en`, `slug`, `type`, `description_lv`, `description_en`, `content_body_lv`, `content_body_en`, `video_url`, `video_platform`, `thumbnail`, `featured_image`, `blog_images`, `duration`, `category`, `view_count`, `like_count`, `is_published`, `is_featured`, `published_at`, `created_by`, `created_at`, `updated_at`) VALUES
(88, 'Truongs bija šeit', 'Truong was here', 'truongs-bija-seit', 'post', 'Testēju, taisot vēl vienu ziņu', 'Testing on making another post', 'Truongs saka čau', 'Truong is saying hi', NULL, NULL, '/img/Posts/1776857947_4cfeb34c-e8af-4705-9375-a6d0a56bbbc4.jpg', '/img/Posts/1776857947_4cfeb34c-e8af-4705-9375-a6d0a56bbbc4.jpg', '[\"\\/img\\/Posts\\/1776856976_9269bbde-31ec-40f5-8cac-a94bd7b5a669.jpg\",\"\\/img\\/Posts\\/1776856976_e31778e2-7737-4171-bad1-21a5e4792e6c.png\"]', NULL, 'Humor', 25, 1, 1, 0, '2026-04-21 08:25:00', 5, '2026-04-22 11:22:56', '2026-04-24 21:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(64) NOT NULL,
  `type` varchar(255) NOT NULL DEFAULT 'percentage',
  `value` decimal(8,2) NOT NULL,
  `min_order_amount` decimal(8,2) NOT NULL DEFAULT 0.00,
  `max_discount_amount` decimal(8,2) DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `used_count` int(11) NOT NULL DEFAULT 0,
  `max_uses_per_user` int(11) NOT NULL DEFAULT 1,
  `cooldown_days` int(11) NOT NULL DEFAULT 14,
  `subscribers_only` tinyint(1) NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `description_lv` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `coupon_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(8,2) NOT NULL,
  `used_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `reusable_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `couriers`
--

CREATE TABLE `couriers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to users (courier account)',
  `full_name` varchar(100) NOT NULL COMMENT 'Kurjera vārds / Courier name',
  `vehicle_type` varchar(50) DEFAULT NULL COMMENT 'Transporta līdzekļa veids / Vehicle type',
  `delivery_area` varchar(100) DEFAULT NULL COMMENT 'Piegādes rajons / Delivery area',
  `phone` varchar(20) NOT NULL COMMENT 'Kontakttālrunis / Contact phone',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Vai ir aktīvs / Is active',
  `hired_at` date DEFAULT NULL COMMENT 'Nodarbinātības datums / Employment date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kurjeru informācija / Courier information';

--
-- Dumping data for table `couriers`
--

INSERT INTO `couriers` (`id`, `user_id`, `full_name`, `vehicle_type`, `delivery_area`, `phone`, `is_active`, `hired_at`, `created_at`, `updated_at`) VALUES
(1, 3, 'Courier Master (Courier)', 'Auto', 'Purvciems, Rīga', '26123456', 1, '2026-02-19', '2026-02-21 21:51:56', '2026-02-25 16:32:41');

-- --------------------------------------------------------

--
-- Table structure for table `courier_assignments`
--

CREATE TABLE `courier_assignments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `courier_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to couriers',
  `order_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to orders',
  `assigned_at` timestamp NULL DEFAULT current_timestamp() COMMENT 'Piešķiršanas datums / Assignment date',
  `completed_at` timestamp NULL DEFAULT NULL COMMENT 'Pabeigšanas datums / Completion date',
  `notes` text DEFAULT NULL COMMENT 'Kurjera piezīmes / Courier notes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Kurjeru piešķirtie pasūtījumi / Courier order assignments';

--
-- Dumping data for table `courier_assignments`
--

INSERT INTO `courier_assignments` (`id`, `courier_id`, `order_id`, `assigned_at`, `completed_at`, `notes`) VALUES
(1, 1, 25, '2026-02-23 13:47:27', '2026-02-23 14:04:14', NULL),
(2, 1, 26, '2026-02-23 13:47:37', '2026-02-23 14:05:15', 'Pirmo reizi notestēju statusa maiņu un pasūtījuma pārlūkošanu'),
(3, 1, 19, '2026-02-23 16:54:02', '2026-02-23 16:59:33', NULL),
(4, 1, 28, '2026-02-24 17:26:56', '2026-02-24 17:33:38', NULL),
(5, 1, 29, '2026-02-25 12:47:44', '2026-02-25 13:29:36', NULL),
(6, 1, 30, '2026-02-25 16:33:34', '2026-02-25 16:37:48', 'hello hello'),
(7, 1, 27, '2026-03-04 21:26:53', '2026-03-26 18:41:58', NULL),
(8, 1, 31, '2026-03-27 23:04:19', '2026-03-27 23:10:06', 'veiksmi'),
(9, 1, 16, '2026-03-27 23:04:19', '2026-04-09 13:36:47', 'veiksmi atkal!');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `likes`
--

CREATE TABLE `likes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `likeable_type` varchar(255) NOT NULL COMMENT 'Polymorphic type: Content, Product, etc.',
  `likeable_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Polymorphic ID',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `likes`
--

INSERT INTO `likes` (`id`, `user_id`, `likeable_type`, `likeable_id`, `created_at`, `updated_at`) VALUES
(4, 2, 'App\\Models\\Content', 5, '2025-12-09 14:19:42', '2025-12-09 14:19:42'),
(7, 2, 'App\\Models\\Content', 1, '2025-12-09 23:20:50', '2025-12-09 23:20:50'),
(8, 2, 'App\\Models\\Content', 3, '2025-12-09 23:21:00', '2025-12-09 23:21:00'),
(10, 2, 'App\\Models\\Content', 66, '2026-02-19 13:03:53', '2026-02-19 13:03:53'),
(11, 3, 'App\\Models\\Content', 66, '2026-02-23 17:04:20', '2026-02-23 17:04:20'),
(14, 5, 'App\\Models\\Content', 87, '2026-04-21 10:11:06', '2026-04-21 10:11:06'),
(15, 5, 'App\\Models\\Content', 65, '2026-04-22 11:38:06', '2026-04-22 11:38:06'),
(17, 5, 'App\\Models\\Content', 80, '2026-04-22 15:09:52', '2026-04-22 15:09:52'),
(19, 5, 'App\\Models\\Content', 66, '2026-04-22 23:42:34', '2026-04-22 23:42:34'),
(20, 5, 'App\\Models\\Content', 1, '2026-04-22 23:42:53', '2026-04-22 23:42:53'),
(21, 5, 'App\\Models\\Content', 2, '2026-04-23 09:42:49', '2026-04-23 09:42:49'),
(22, 2, 'App\\Models\\Content', 2, '2026-04-23 10:15:25', '2026-04-23 10:15:25'),
(24, 5, 'App\\Models\\Content', 88, '2026-04-24 21:02:06', '2026-04-24 21:02:06');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2024_01_01_000000_create_cache_table', 1),
(2, '2024_01_01_000002_create_jobs_table', 2),
(3, '2024_12_08_000000_add_last_login_at_to_users_table', 3),
(4, '2024_12_08_000001_add_name_columns_to_users_table', 4),
(5, '2024_12_08_000003_add_is_featured_to_content_table', 5),
(6, '2024_12_08_000004_create_likes_table', 6),
(7, '2025_12_17_000001_add_card_fields_migration', 7),
(8, '0001_01_01_000000_create_users_table', 8),
(9, '2026_01_09_000001_add_phone_to_contact_messages_table', 9),
(10, '2026_01_15_000001_create_settings_table', 10),
(11, '2026_01_27_create_newsletter_tables', 11),
(12, '2026_02_20_add_subscription_expires_at_to_newsletter_subscribers', 12),
(13, '2026_02_20_000001_create_coupons_table', 13),
(14, '2026_02_20_000002_add_sizes_to_products_cart_items', 14),
(15, '2026_02_20_000003_add_coupon_fields_to_orders', 15),
(16, '2026_03_31_002644_add_is_public_to_users_table', 16),
(17, '2026_04_23_000001_add_mood_score_to_comments', 17),
(18, '2026_04_23_000002_create_comment_moods_table', 18);

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `email` varchar(100) NOT NULL COMMENT 'Abonenta e-pasts',
  `token` varchar(64) NOT NULL COMMENT 'Unikāls tokens atteikšanās saitei',
  `is_verified` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Vai e-pasts ir apstiprināts',
  `verified_at` timestamp NULL DEFAULT NULL COMMENT 'Apstiprināšanas datums',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Vai abonements ir aktīvs',
  `unsubscribed_at` timestamp NULL DEFAULT NULL COMMENT 'Atteikšanās datums',
  `receive_news` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Saņemt jaunumus',
  `receive_promotions` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Saņemt piedāvājumus',
  `receive_announcements` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Saņemt paziņojumus',
  `subscription_expires_at` timestamp NULL DEFAULT NULL COMMENT 'null = abonēšana bez termiņa',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `user_id`, `email`, `token`, `is_verified`, `verified_at`, `is_active`, `unsubscribed_at`, `receive_news`, `receive_promotions`, `receive_announcements`, `subscription_expires_at`, `created_at`, `updated_at`) VALUES
(1, 2, 'ralph.migals@gmail.com', 'abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567abc890def12', 1, '2026-04-09 12:37:24', 1, NULL, 1, 1, 1, '2027-04-09 12:37:24', '2026-01-10 10:00:00', '2026-04-09 12:37:24'),
(2, 3, 'test.user@example.com', 'xyz789abc123def456ghi789jkl012mno345pqr678stu901vwx234yz567abc89', 1, '2026-01-12 12:30:00', 1, NULL, 1, 1, 0, NULL, '2026-01-12 12:30:00', '2026-01-12 12:30:00'),
(3, NULL, 'guest.subscriber@email.lv', 'guest123token456random789string012more345chars678here901and234mo', 1, '2026-01-15 07:15:00', 1, NULL, 1, 1, 1, NULL, '2026-01-15 07:15:00', '2026-01-15 07:15:00'),
(4, 5, 'ralphmania.roltonslv@gmail.com', 'JPK2bGYDBWWSZSoLJRfrHeQDDdLMFzysepfyjDVlwUDK3bQNKk4v6RL7JIMQfpvO', 1, '2026-03-26 14:26:18', 1, NULL, 1, 1, 1, '2027-03-26 14:26:18', '2026-03-26 14:26:18', '2026-03-30 21:42:08');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_number` varchar(50) NOT NULL COMMENT 'Pasūtījuma numurs (auto) / Order number',
  `user_id` bigint(20) UNSIGNED DEFAULT NULL COMMENT 'FK to users (NULL = guest)',
  `customer_name` varchar(100) NOT NULL COMMENT 'Klienta vārds / Customer name',
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `delivery_country` varchar(50) NOT NULL DEFAULT 'Latvia',
  `delivery_city` varchar(50) NOT NULL COMMENT 'Pilsēta / City',
  `delivery_address` varchar(100) NOT NULL COMMENT 'Adrese (max 100 chars) / Address',
  `delivery_postal_code` varchar(20) DEFAULT NULL COMMENT 'Pasta indekss / Postal code',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Summa bez piegādes / Subtotal',
  `shipping_cost` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Piegādes izmaksas / Shipping cost',
  `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Kupona atlaide EUR / Coupon discount EUR',
  `coupon_code` varchar(64) DEFAULT NULL COMMENT 'Izmantotais kupona kods / Used coupon code',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Kopējā summa / Total amount',
  `status` enum('pending','confirmed','processing','packed','shipped','in_transit','delivered','cancelled','refunded') NOT NULL DEFAULT 'pending' COMMENT 'Pasūtījuma statuss / Order status',
  `notes` text DEFAULT NULL COMMENT 'Klienta piezīmes / Customer notes',
  `tracking_number` varchar(100) DEFAULT NULL COMMENT 'Sūtījuma izsekošanas numurs / Tracking number',
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Apmaksas datums / Payment date',
  `shipped_at` timestamp NULL DEFAULT NULL COMMENT 'Nosūtīšanas datums / Shipping date',
  `delivered_at` timestamp NULL DEFAULT NULL COMMENT 'Piegādes datums / Delivery date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Klientu pasūtījumi ar 9 statusiem / Customer orders with 9 statuses';

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `delivery_country`, `delivery_city`, `delivery_address`, `delivery_postal_code`, `subtotal`, `shipping_cost`, `discount_amount`, `coupon_code`, `total_amount`, `status`, `notes`, `tracking_number`, `paid_at`, `shipped_at`, `delivered_at`, `created_at`, `updated_at`) VALUES
(1, 'RM-20251217-B5D716', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '26167232', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1001', 97.95, 0.00, 0.00, NULL, 118.52, 'refunded', NULL, NULL, NULL, NULL, NULL, '2025-12-17 02:18:41', '2026-02-23 14:09:13'),
(2, 'RM-20251217-6B0425', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '26167232', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1001', 99.98, 0.00, 0.00, NULL, 120.98, 'cancelled', NULL, NULL, NULL, '2026-01-12 14:44:44', '2026-01-12 14:44:40', '2025-12-17 03:31:11', '2026-01-12 14:44:54'),
(10, 'RM-20260106-CC9463', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 30.97, 3.99, 0.00, NULL, 34.96, 'in_transit', NULL, NULL, NULL, NULL, NULL, '2026-01-06 13:38:34', '2026-03-30 21:37:39'),
(11, 'RM-20260106-5A2166', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 58.98, 0.00, 0.00, NULL, 58.98, 'in_transit', NULL, NULL, NULL, NULL, NULL, '2026-01-06 19:28:19', '2026-03-30 21:37:36'),
(12, 'RM-20260106-B32F45', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 100.00, 0.00, 0.00, NULL, 100.00, 'delivered', 'draugam haha', NULL, NULL, NULL, '2026-03-30 21:37:32', '2026-01-06 19:34:28', '2026-03-30 21:37:32'),
(13, 'RM-20260106-6E35F6', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 50.00, 0.00, 0.00, NULL, 50.00, 'shipped', NULL, NULL, NULL, '2026-03-30 21:37:29', NULL, '2026-01-06 19:52:26', '2026-03-30 21:37:29'),
(14, 'RM-20260107-9A32CF', 3, 'Roltona Alts', 'roltonsalt@gmail.com', '+37129000000', 'Latvia', 'Rīga', 'Tukuma iela 3', 'LV-1093', 7.99, 3.99, 0.00, NULL, 11.98, 'shipped', NULL, NULL, NULL, '2026-03-30 21:37:27', NULL, '2026-01-07 12:27:53', '2026-03-30 21:37:27'),
(15, 'RM-20260107-6A5B42', 3, 'Roltona Alts', 'roltonsalt@gmail.com', '+37129000000', 'Latvia', 'Rīga', 'Tukuma iela 3', 'LV-1093', 22.99, 3.99, 0.00, NULL, 26.98, 'shipped', NULL, NULL, NULL, '2026-03-30 21:37:25', NULL, '2026-01-07 12:56:24', '2026-03-30 21:37:25'),
(16, 'RM-20260108-B3F7C6', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 12.99, 3.99, 0.00, NULL, 16.98, 'delivered', NULL, NULL, NULL, '2026-04-09 13:27:05', '2026-04-09 13:36:47', '2026-01-08 12:24:30', '2026-04-09 13:36:47'),
(17, 'RM-20260108-20B1B6', 3, 'Roltona Alts', 'roltonsalt@gmail.com', '+37129000000', 'Latvia', 'Rīga', 'Tukuma iela 3', 'LV-1093', 18.99, 3.99, 0.00, NULL, 22.98, 'packed', NULL, NULL, NULL, NULL, NULL, '2026-01-08 12:27:56', '2026-03-30 21:37:18'),
(18, 'RM-20260108-BB6CDE', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 78.97, 0.00, 0.00, NULL, 78.97, 'packed', 'Elvis bija šeit lol', NULL, NULL, NULL, NULL, '2026-01-08 15:49:13', '2026-03-30 21:37:17'),
(19, 'RM-20260113-2859E4', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 64.98, 0.00, 0.00, NULL, 64.98, 'packed', NULL, NULL, NULL, '2026-02-23 16:54:02', '2026-02-23 16:59:33', '2026-01-13 17:15:09', '2026-03-30 21:37:14'),
(20, 'RM-20260116-C6DD14', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 8.99, 3.99, 0.00, NULL, 12.98, 'packed', NULL, NULL, NULL, NULL, NULL, '2026-01-16 12:19:50', '2026-03-30 21:37:12'),
(21, 'RM-20260219-EE1CC8', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 15.98, 3.99, 0.00, NULL, 19.97, 'packed', 'nolēmu notestēt', NULL, NULL, NULL, NULL, '2026-02-19 12:54:20', '2026-03-30 21:37:11'),
(22, 'RM-20260219-40FA5F', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 49.99, 3.99, 0.00, NULL, 53.98, 'packed', NULL, NULL, NULL, NULL, NULL, '2026-02-19 12:55:54', '2026-03-30 21:37:09'),
(23, 'RM-20260220-8B5632', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 26.97, 3.99, 0.00, NULL, 30.96, 'packed', 'tests ar atlaižu kuponu', NULL, NULL, NULL, NULL, '2026-02-20 20:05:02', '2026-03-30 21:37:07'),
(24, 'RM-20260220-8C8CBC', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 99.98, 0.00, 10.00, 'WELCOME10', 89.98, 'packed', NULL, NULL, NULL, NULL, NULL, '2026-02-20 20:20:54', '2026-03-30 21:37:52'),
(25, 'RM-20260220-B9C15D', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 38.98, 3.99, 9.75, 'WINTER25', 33.22, 'delivered', 'tests ar atlaides kuponu + app.blade failu tests', NULL, NULL, '2026-02-22 11:43:58', '2026-02-23 14:04:14', '2026-02-20 20:28:45', '2026-02-23 14:04:14'),
(26, 'RM-20260222-35895F', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 22.99, 3.99, 2.30, 'WELCOME10', 24.68, 'delivered', NULL, NULL, NULL, '2026-02-23 13:47:37', '2026-02-23 14:05:15', '2026-02-22 11:39:56', '2026-02-23 14:05:15'),
(27, 'RM-20260223-D34822', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 23.96, 3.99, 2.40, 'WELCOME10', 25.55, 'delivered', 'haha biju šeit', NULL, NULL, '2026-03-04 21:26:53', '2026-03-26 18:41:58', '2026-02-23 16:47:55', '2026-03-26 18:41:58'),
(28, 'RM-20260224-C59E1C', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 45.98, 3.99, 5.00, 'SAVE5', 44.97, 'delivered', 'hai hello', NULL, NULL, '2026-02-24 17:26:56', '2026-02-24 17:33:38', '2026-02-24 17:07:34', '2026-02-24 17:33:38'),
(29, 'RM-20260225-D9B3F0', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 8.99, 3.49, 1.35, 'VIP15', 11.13, 'delivered', NULL, NULL, NULL, '2026-02-25 12:47:44', '2026-02-25 13:29:36', '2026-02-25 12:44:32', '2026-02-25 13:29:36'),
(30, 'RM-20260225-BA2A1C', 2, 'Ralfs Migals', 'ralph.migals@gmail.com', '29123456', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 32.96, 3.49, 3.30, 'WELCOME10', 33.15, 'delivered', 'M. Montvida bija šeit', NULL, NULL, '2026-02-25 16:33:34', '2026-02-25 16:37:48', '2026-02-25 16:22:31', '2026-02-25 16:37:48'),
(31, 'RM-20260326-843F1C', 5, 'Ralfs Migals', 'ralphmania.roltonslv@gmail.com', '29123456', 'Latvia', 'Rīga', 'Brīvības iela 1', 'LV-1001', 8.99, 3.49, 0.00, NULL, 12.48, 'delivered', 'Piemēra teksts papildus piezīmei', NULL, NULL, '2026-03-27 23:04:19', '2026-04-02 19:20:34', '2026-03-26 14:58:51', '2026-04-02 19:20:34');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to orders',
  `product_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to products',
  `product_name` varchar(50) NOT NULL COMMENT 'Produkta nosaukums (snapshot) / Product name snapshot',
  `quantity` int(10) UNSIGNED NOT NULL COMMENT 'Daudzums / Quantity',
  `size` varchar(10) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL COMMENT 'Cena pirkšanas brīdī / Price at purchase',
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantity` * `price`) STORED COMMENT 'Kopā / Total',
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Produkti pasūtījumā / Products in order';

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `size`, `price`, `created_at`) VALUES
(1, 1, 4, 'GD Edition Džemperis (Melns)', 1, NULL, 49.99, NULL),
(2, 1, 1, 'RalphMania Logo T-Krekls (Melns)', 2, NULL, 8.99, NULL),
(3, 1, 7, 'RalphMania Telefona Vāciņš (iPhone)', 2, NULL, 14.99, NULL),
(4, 2, 3, 'GD Edition Džemperis (Sarkans)', 2, NULL, 49.99, NULL),
(5, 10, 6, 'RalphMania Cepure Snapback', 1, NULL, 14.99, NULL),
(6, 10, 11, 'RalphMania Atslēgu Piekariņš', 2, NULL, 7.99, NULL),
(7, 11, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL),
(8, 11, 3, 'GD Edition Džemperis (Sarkans)', 1, NULL, 49.99, NULL),
(9, 12, 18, 'RalphMania Dāvanu Karte €100', 1, NULL, 100.00, NULL),
(10, 13, 17, 'RalphMania Dāvanu Karte €50', 1, NULL, 50.00, NULL),
(11, 14, 11, 'RalphMania Atslēgu Piekariņš', 1, NULL, 7.99, NULL),
(12, 15, 15, 'RalphMania Peles Paliktnis (XXL)', 1, NULL, 22.99, NULL),
(13, 16, 9, 'RalphMania Auduma Soma', 1, NULL, 12.99, NULL),
(14, 17, 10, 'RalphMania Ūdens Pudele', 1, NULL, 18.99, NULL),
(15, 18, 15, 'RalphMania Peles Paliktnis (XXL)', 1, NULL, 22.99, NULL),
(16, 18, 14, 'RalphMania Nozīmju Komplekts', 1, NULL, 15.99, NULL),
(17, 18, 13, 'Parakstīts Plakāts (Limited)', 1, NULL, 39.99, NULL),
(18, 19, 4, 'GD Edition Džemperis (Melns)', 1, NULL, 49.99, NULL),
(19, 19, 6, 'RalphMania Cepure Snapback', 1, NULL, 14.99, NULL),
(20, 20, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL),
(21, 21, 11, 'RalphMania Atslēgu Piekariņš', 2, NULL, 7.99, NULL),
(22, 22, 3, 'GD Edition Džemperis (Sarkans)', 1, NULL, 49.99, NULL),
(23, 23, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL),
(24, 23, 1, 'RalphMania Logo T-Krekls (Melns)', 2, NULL, 8.99, NULL),
(25, 24, 3, 'GD Edition Džemperis (Sarkans)', 2, NULL, 49.99, NULL),
(26, 25, 15, 'RalphMania Peles Paliktnis (XXL)', 1, NULL, 22.99, NULL),
(27, 25, 14, 'RalphMania Nozīmju Komplekts', 1, NULL, 15.99, NULL),
(28, 26, 15, 'RalphMania Peles Paliktnis (XXL)', 1, NULL, 22.99, NULL),
(29, 27, 8, 'RalphMania Uzlīmju Paka', 4, NULL, 5.99, NULL),
(30, 28, 14, 'RalphMania Nozīmju Komplekts', 1, NULL, 15.99, NULL),
(31, 28, 5, 'Garroku Krekls RalphMania', 1, NULL, 29.99, NULL),
(32, 29, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL),
(33, 30, 8, 'RalphMania Uzlīmju Paka', 1, NULL, 5.99, NULL),
(34, 30, 1, 'RalphMania Logo T-Krekls (Melns)', 2, NULL, 8.99, NULL),
(35, 30, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL),
(36, 31, 1, 'RalphMania Logo T-Krekls (Melns)', 1, NULL, 8.99, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Paroles atjaunošanas tokeni / Password reset tokens';

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('ralfs.migals@icloud.com', '$2y$12$XFAGX.tgo/KoZZhSiseWLeU7OrQLFzvkESvCowuvdtNjTQN/bN0ju', '2026-04-19 10:57:16'),
('ralph.migals@gmail.com', '$2y$12$F.xEKOg8ATV9x51eKLuGr.piviyehVc57vWZdCtE7NIT0CwmidK6O', '2026-01-08 13:56:10');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to orders',
  `payment_method` varchar(50) NOT NULL COMMENT 'Maksājuma veids: card, paypal, bank_transfer / Payment method',
  `card_last4` varchar(4) DEFAULT NULL COMMENT 'Last 4 digits of card',
  `card_brand` varchar(20) DEFAULT NULL COMMENT 'Card brand (Visa, Mastercard, etc)',
  `card_exp_month` varchar(2) DEFAULT NULL COMMENT 'Card expiry month (MM)',
  `card_exp_year` varchar(4) DEFAULT NULL COMMENT 'Card expiry year (YYYY)',
  `amount` decimal(10,2) NOT NULL COMMENT 'Summa / Amount',
  `currency` varchar(3) NOT NULL DEFAULT 'EUR',
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending' COMMENT 'Apmaksas statuss / Payment status',
  `transaction_id` varchar(100) DEFAULT NULL COMMENT 'External payment gateway ID',
  `gateway_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Full payment gateway response' CHECK (json_valid(`gateway_response`)),
  `paid_at` timestamp NULL DEFAULT NULL COMMENT 'Apmaksas datums / Payment date',
  `refunded_at` timestamp NULL DEFAULT NULL COMMENT 'Atmaksas datums / Refund date',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maksājumu informācija ar payment gateway atbalstu / Payment information with gateway support';

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `card_last4`, `card_brand`, `card_exp_month`, `card_exp_year`, `amount`, `currency`, `status`, `transaction_id`, `gateway_response`, `paid_at`, `refunded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'card', NULL, NULL, NULL, NULL, 118.52, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2025-12-17 02:18:41', '2025-12-17 02:18:41'),
(2, 2, 'card', NULL, NULL, NULL, NULL, 120.98, 'EUR', 'refunded', NULL, NULL, NULL, NULL, '2025-12-17 03:31:11', '2025-12-17 14:57:33'),
(3, 10, 'card', '4242', 'Visa', '12', '2026', 34.96, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-06 13:38:34', '2026-01-06 13:38:34'),
(4, 11, 'card', '0404', 'Unknown', '12', '2026', 58.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-06 19:28:19', '2026-01-06 19:28:19'),
(5, 12, 'card', '0404', 'Unknown', '12', '2026', 100.00, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-06 19:34:28', '2026-01-06 19:34:28'),
(6, 13, 'card', '0404', 'Unknown', '12', '2026', 50.00, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-06 19:52:26', '2026-01-06 19:52:26'),
(7, 14, 'card', '0404', 'Unknown', '12', '2026', 11.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-07 12:27:53', '2026-01-07 12:27:53'),
(8, 15, 'card', '0404', 'Unknown', '12', '2026', 26.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-07 12:56:24', '2026-01-07 12:56:24'),
(9, 16, 'card', '0404', 'Unknown', '12', '2026', 16.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-08 12:24:30', '2026-01-08 12:24:30'),
(10, 17, 'card', '0404', 'Unknown', '12', '2026', 22.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-08 12:27:56', '2026-01-08 12:27:56'),
(11, 18, 'card', '0404', 'Unknown', '12', '2026', 78.97, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-08 15:49:13', '2026-01-08 15:49:13'),
(12, 19, 'card', '0404', 'Unknown', '12', '2026', 64.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-13 17:15:09', '2026-01-13 17:15:09'),
(13, 20, 'card', '0404', 'Unknown', '12', '2026', 12.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-01-16 12:19:50', '2026-01-16 12:19:50'),
(14, 21, 'card', '0404', 'Unknown', '12', '2026', 19.97, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-19 12:54:20', '2026-02-19 12:54:20'),
(15, 22, 'bank_transfer', NULL, NULL, NULL, NULL, 53.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-19 12:55:54', '2026-02-19 12:55:54'),
(16, 23, 'card', '0404', 'Unknown', '12', '2026', 30.96, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-20 20:05:02', '2026-02-20 20:05:02'),
(17, 24, 'card', '0404', 'Unknown', '12', '2026', 89.98, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-20 20:20:54', '2026-02-20 20:20:54'),
(18, 25, 'card', '0404', 'Unknown', '12', '2026', 33.22, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-20 20:28:45', '2026-02-20 20:28:45'),
(19, 26, 'card', '0505', 'Unknown', '12', '2026', 24.68, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-22 11:39:56', '2026-02-22 11:39:56'),
(20, 27, 'card', '0404', 'Unknown', '12', '2026', 25.55, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-23 16:47:55', '2026-02-23 16:47:55'),
(21, 28, 'card', '0404', 'Unknown', '12', '2026', 44.97, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-24 17:07:34', '2026-02-24 17:07:34'),
(22, 29, 'card', '0404', 'Unknown', '12', '2025', 11.13, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-25 12:44:32', '2026-02-25 12:44:32'),
(23, 30, 'card', '0404', 'Unknown', '12', '2026', 33.15, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-02-25 16:22:31', '2026-02-25 16:22:31'),
(24, 31, 'card', '0404', 'Unknown', '12', '2026', 12.48, 'EUR', 'pending', NULL, NULL, NULL, NULL, '2026-03-26 14:58:51', '2026-03-26 14:58:51');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name_lv` varchar(50) NOT NULL COMMENT 'Nosaukums latviski (max 50 chars)',
  `name_en` varchar(50) NOT NULL COMMENT 'Nosaukums angliski (max 50 chars)',
  `slug` varchar(255) NOT NULL COMMENT 'URL-friendly slug',
  `sku` varchar(100) DEFAULT NULL COMMENT 'Stock Keeping Unit',
  `category_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to categories',
  `description_lv` varchar(255) DEFAULT NULL COMMENT 'Apraksts latviski (max 255 chars)',
  `description_en` varchar(255) DEFAULT NULL COMMENT 'Apraksts angliski (max 255 chars)',
  `price` decimal(10,2) NOT NULL COMMENT 'Cena (EUR, formāts XX.XX) / Price',
  `compare_price` decimal(10,2) DEFAULT NULL COMMENT 'Vecā cena (atlaide) / Old price (discount)',
  `image` varchar(255) DEFAULT NULL COMMENT 'Galvenais attēls (.jpg, .png) / Main image',
  `stock_quantity` int(10) UNSIGNED DEFAULT 0 COMMENT 'Krājums / Stock quantity',
  `low_stock_threshold` int(10) UNSIGNED DEFAULT 5 COMMENT 'Brīdinājums par zemu krājumu / Low stock warning',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Vai produkts ir aktīvs / Is product active',
  `is_featured` tinyint(1) DEFAULT 0 COMMENT 'Vai ir featured / Is featured',
  `has_sizes` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='RalphMania zīmola produkti ar divvalodu atbalstu / RalphMania brand products with bilingual support';

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name_lv`, `name_en`, `slug`, `sku`, `category_id`, `description_lv`, `description_en`, `price`, `compare_price`, `image`, `stock_quantity`, `low_stock_threshold`, `is_active`, `is_featured`, `has_sizes`, `created_at`, `updated_at`) VALUES
(1, 'RalphMania Logo T-Krekls (Melns)', 'RalphMania Logo T-Shirt (Black)', 'logo-tshirt-black', 'RM-TS-001-BLK', 10, 'Klasiskais RalphMania logo uz augstas kvalitātes melna kokvilnas t-krekla', 'Classic RalphMania logo on high-quality black cotton t-shirt', 8.99, 11.99, '/img/Products/t-shirt-black.png', 48, 10, 1, 1, 1, '2025-12-07 14:00:21', '2026-04-24 21:33:12'),
(2, 'RalphMania Logo T-Krekls (Balts)', 'RalphMania Logo T-Shirt (White)', 'logo-tshirt-white', 'RM-TS-001-WHT', 10, 'Klasiskais RalphMania logo uz augstas kvalitātes balta kokvilnas t-krekla', 'Classic RalphMania logo on high-quality white cotton t-shirt', 8.99, 11.99, '/img/Products/t-shirt-white.png', 45, 10, 1, 1, 1, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(3, 'GD Edition Džemperis (Sarkans)', 'GD Edition Hoodie (Red)', 'gd-hoodie-red', 'RM-HD-002-RED', 12, 'Geometry Dash speciālā izdevuma džemperis ar kapuci un RalphMania logo', 'Geometry Dash special edition hoodie with RalphMania logo', 49.99, 59.99, '/img/Products/hoodie-red.png', 30, 5, 1, 1, 1, '2025-12-07 14:00:21', '2025-12-17 14:57:33'),
(4, 'GD Edition Džemperis (Melns)', 'GD Edition Hoodie (Black)', 'gd-hoodie-black', 'RM-HD-002-BLK', 12, 'Geometry Dash speciālā izdevuma džemperis ar kapuci un RalphMania logo', 'Geometry Dash special edition hoodie with RalphMania logo', 49.99, NULL, '/img/Products/hoodie-black.png', 34, 5, 1, 1, 1, '2025-12-07 14:00:21', '2025-12-17 02:18:41'),
(5, 'Garroku Krekls RalphMania', 'Long Sleeve RalphMania Shirt', 'long-sleeve-shirt', 'RM-LS-003', 10, 'Ērts garroku krekls ar RalphMania uzrakstu', 'Comfortable long sleeve shirt with RalphMania branding', 29.99, NULL, '/img/Products/long-sleeve-shirt.png', 25, 5, 1, 0, 1, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(6, 'RalphMania Cepure Snapback', 'RalphMania Snapback Cap', 'snapback-cap', 'RM-CAP-004', 13, 'Stilīga snapback cepure ar izšūtu RalphMania logo', 'Stylish snapback cap with embroidered RalphMania logo', 14.99, 19.99, '/img/Products/snapback-cap.png', 40, 8, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(7, 'RalphMania Telefona Vāciņš (iPhone)', 'RalphMania Phone Case (iPhone)', 'phone-case-iphone', 'RM-ACC-005-IPH', 22, 'Izturīgs telefona vāciņš ar RalphMania dizainu (iPhone 13/14/15)', 'Durable phone case with RalphMania design (iPhone 13/14/15)', 14.99, NULL, '/img/Products/phonecase-iphone.png', 58, 10, 1, 1, 0, '2025-12-07 14:00:21', '2025-12-17 02:18:41'),
(8, 'RalphMania Uzlīmju Paka', 'RalphMania Sticker Pack', 'sticker-pack', 'RM-STK-006', 31, 'Komplekts ar 10 RalphMania un GD tematiskām uzlīmēm', 'Set of 10 RalphMania and GD themed stickers', 5.99, NULL, '/img/Products/stickers.png', 100, 20, 1, 1, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(9, 'RalphMania Auduma Soma', 'RalphMania Tote Bag', 'tote-bag', 'RM-BAG-007', 20, 'Ekoloģiska auduma soma ar RalphMania printu', 'Eco-friendly canvas tote bag with RalphMania print', 12.99, 16.99, '/img/Products/tote-bag.png', 35, 5, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(10, 'RalphMania Ūdens Pudele', 'RalphMania Water Bottle', 'water-bottle', 'RM-BTL-008', 30, 'Nerūsējošā tērauda ūdens pudele (500ml) ar logo', 'Stainless steel water bottle (500ml) with logo', 18.99, NULL, '/img/Products/waterbottle.png', 28, 5, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(11, 'RalphMania Atslēgu Piekariņš', 'RalphMania Keychain', 'keychain', 'RM-KEY-009', 33, 'Metāla atslēgu piekariņš ar RalphMania logo', 'Metal keychain with RalphMania logo', 7.99, NULL, '/img/Products/keychain.png', 75, 15, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(12, 'RalphMania Plakātu Komplekts', 'RalphMania Poster Set', 'poster-set', 'RM-PST-010', 32, 'Komplekts ar 3 augsti kvalitātes plakātiem (A2 formāts)', 'Set of 3 high-quality posters (A2 format)', 19.99, NULL, '/img/Products/posters.png', 40, 5, 1, 1, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(13, 'Parakstīts Plakāts (Limited)', 'Signed Poster (Limited)', 'signed-poster', 'RM-SIG-011-LTD', 32, 'Ierobežota izdevuma parakstīts plakāts - tikai 50 gab!', 'Limited edition signed poster - only 50 pieces!', 39.99, 49.99, '/img/Products/signed-poster.png', 50, 3, 1, 1, 0, '2025-12-07 14:00:21', '2026-02-24 17:25:06'),
(14, 'RalphMania Nozīmju Komplekts', 'RalphMania Pin Badge Set', 'pin-badge-set', 'RM-PIN-012', 21, 'Kolekcionējamu emalijas nozīmju komplekts (5 gab)', 'Collectible enamel pin badge set (5 pieces)', 15.99, NULL, '/img/Products/pinbadges.png', 55, 10, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(15, 'RalphMania Peles Paliktnis (XXL)', 'RalphMania Mouse Pad (XXL)', 'mousepad-xxl', 'RM-MP-013-XXL', 35, 'Liela izmēra gaming peles paliktnis ar RalphMania dizainu', 'Extra large gaming mouse pad with RalphMania design', 22.99, NULL, '/img/Products/mousepad.png', 45, 8, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(16, 'RalphMania Dāvanu Karte €25', 'RalphMania Gift Card €25', 'giftcard-25', 'RM-GC-25', 4, 'Digitāla dāvanu karte €25 vērtībā', 'Digital gift card worth €25', 25.00, NULL, '/img/Products/giftcard-25.png', 999, 0, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(17, 'RalphMania Dāvanu Karte €50', 'RalphMania Gift Card €50', 'giftcard-50', 'RM-GC-50', 4, 'Digitāla dāvanu karte €50 vērtībā', 'Digital gift card worth €50', 50.00, NULL, '/img/Products/giftcard-50.png', 999, 0, 1, 1, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21'),
(18, 'RalphMania Dāvanu Karte €100', 'RalphMania Gift Card €100', 'giftcard-100', 'RM-GC-100', 4, 'Digitāla dāvanu karte €100 vērtībā', 'Digital gift card worth €100', 100.00, NULL, '/img/Products/giftcard-100.png', 999, 0, 1, 0, 0, '2025-12-07 14:00:21', '2025-12-07 14:00:21');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL COMMENT 'FK to users - kas sniedz atsauksmi / who reviews',
  `reviewable_type` varchar(50) NOT NULL COMMENT 'content vai products / content or products',
  `reviewable_id` bigint(20) UNSIGNED NOT NULL COMMENT 'ID no content vai products / ID from content or products',
  `rating` tinyint(3) UNSIGNED NOT NULL COMMENT 'Vērtējums 1-5 zvaigznes / Rating 1-5 stars',
  `review_text_lv` text DEFAULT NULL COMMENT 'Atsauksmes teksts latviski / Review text in Latvian',
  `review_text_en` text DEFAULT NULL COMMENT 'Atsauksmes teksts angliski / Review text in English',
  `is_approved` tinyint(1) DEFAULT 0 COMMENT 'Vai ir apstiprināts / Is approved',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'Izveidošanas datums / Created at',
  `updated_at` timestamp NULL DEFAULT NULL COMMENT 'Rediģēšanas datums / Updated at'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Atsauksmes par saturu vai produktiem / Reviews for content or products';

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `reviewable_type`, `reviewable_id`, `rating`, `review_text_lv`, `review_text_en`, `is_approved`, `created_at`, `updated_at`) VALUES
(1, 2, 'App\\Models\\Content', 1, 5, NULL, 'i luv u', 1, '2025-12-09 13:47:12', '2026-02-24 17:27:48'),
(2, 2, 'App\\Models\\Content', 2, 5, NULL, 'Hella amazing!', 1, '2025-12-09 13:50:32', '2026-03-27 23:05:25'),
(3, 2, 'App\\Models\\Content', 5, 5, NULL, NULL, 1, '2025-12-09 14:16:24', '2026-02-25 16:30:37'),
(4, 2, 'App\\Models\\Content', 3, 5, NULL, 'very good stuff', 1, '2025-12-09 23:22:21', '2026-01-16 12:22:27'),
(5, 2, 'App\\Models\\Content', 65, 5, NULL, 'Ļoti iespaidīgi!', 1, '2026-01-06 12:40:17', '2026-01-10 21:34:13'),
(6, 3, 'App\\Models\\Content', 66, 5, NULL, 'ļoti iespaidīgi!', 1, '2026-02-23 17:04:50', '2026-04-22 23:29:06'),
(7, 2, 'App\\Models\\Content', 82, 5, NULL, 'Migals bija šeit', 1, '2026-02-24 17:19:18', '2026-04-22 23:29:13'),
(8, 5, 'App\\Models\\Product', 1, 5, NULL, 'Ļoti ērts krekls un izdevīga atlaide! Iesaku nopirkt pirms tā iespēja pazūd ;)', 1, '2026-03-26 11:39:58', '2026-03-26 11:39:58'),
(9, 2, 'App\\Models\\Product', 1, 4, 'Krekls ir labs, ir manā gaumē :)', 'Krekls ir labs, ir manā gaumē :)', 1, '2026-04-20 12:48:56', '2026-04-20 12:48:56');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL COMMENT 'Role system name',
  `display_name_lv` varchar(100) NOT NULL COMMENT 'Displeja nosaukums latviski',
  `display_name_en` varchar(100) NOT NULL COMMENT 'Display name in English',
  `description_lv` text DEFAULT NULL COMMENT 'Apraksts latviski',
  `description_en` text DEFAULT NULL COMMENT 'Description in English',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lietotāju lomas: viesis, lietotājs, administrators, kurjers / User roles: guest, user, admin, courier';

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `display_name_lv`, `display_name_en`, `description_lv`, `description_en`, `created_at`, `updated_at`) VALUES
(1, 'guest', 'Viesis', 'Guest', 'Viesis bez reģistrācijas', 'Guest without registration', '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(2, 'user', 'Lietotājs', 'User', 'Reģistrēts lietotājs ar pamata tiesībām', 'Registered user with basic permissions', '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(3, 'administrator', 'Administrators', 'Administrator', 'Pilnas sistēmas piekļuves tiesības', 'Full system access permissions', '2025-12-06 14:53:35', '2025-12-06 14:53:35'),
(4, 'courier', 'Kurjers', 'Courier', 'Pasūtījumu piegādes pārvaldība', 'Order delivery management', '2025-12-06 14:53:35', '2025-12-06 14:53:35');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Laravel sesiju tabula / Laravel session table';

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('BXtyZ6pL6GbbafzueHGz7GUz57wnuqXewu3NmZfw', 5, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36 Edg/147.0.0.0', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoickVlTldGaWs4bjNGWlFjUGptRVZxUTNBcFdGNVd0QWJSOGV6ZFZKSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDg6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9jb250ZW50L3RoYXQtb25lLXRyaXAtc29sbyI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjU7fQ==', 1777116708);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` text DEFAULT NULL,
  `group` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `group`, `created_at`, `updated_at`) VALUES
(1, 'site_name', 'RalphMania', 'general', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(2, 'site_description_lv', 'Labākais interneta veikals Latvijā', 'general', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(3, 'site_description_en', 'The best online shop in Latvia', 'general', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(4, 'admin_email', 'ralphmania.roltonslv@gmail.com', 'general', '2026-01-15 19:00:43', '2026-01-15 19:29:08'),
(5, 'timezone', 'Europe/Riga', 'general', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(6, 'date_format', 'd.m.Y', 'general', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(7, 'currency', 'EUR', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(8, 'currency_symbol', '€', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(9, 'tax_rate', '21', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(10, 'free_shipping_threshold', '50', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(11, 'low_stock_threshold', '5', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(12, 'items_per_page', '20', 'shop', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(13, 'mail_from_name', 'RalphMania', 'email', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(14, 'mail_from_address', 'noreply@ralphmania.com', 'email', '2026-01-15 19:00:43', '2026-01-15 19:30:04'),
(15, 'smtp_host', 'smtp.gmail.com', 'email', '2026-01-15 19:00:43', '2026-01-15 19:25:11'),
(16, 'smtp_port', '587', 'email', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(17, 'smtp_username', 'ralphmania.roltonslv@gmail.com', 'email', '2026-01-15 19:00:43', '2026-01-15 19:25:11'),
(18, 'smtp_password', NULL, 'email', '2026-01-15 19:00:43', '2026-01-15 19:09:52'),
(19, 'facebook_url', 'https://www.facebook.com/ralfs.migals.3/', 'social', '2026-01-15 19:00:43', '2026-01-15 19:26:46'),
(20, 'instagram_url', 'https://www.instagram.com/ralfsmigals/', 'social', '2026-01-15 19:00:43', '2026-01-15 19:27:03'),
(21, 'twitter_url', 'https://x.com/RealRoltonsLV', 'social', '2026-01-15 19:00:43', '2026-01-15 19:23:54'),
(22, 'youtube_url', 'https://www.youtube.com/@RoltonsLV', 'social', '2026-01-15 19:00:43', '2026-01-15 19:09:52'),
(23, 'tiktok_url', 'https://www.tiktok.com/@realroltonslv', 'social', '2026-01-15 19:00:43', '2026-01-15 19:23:09'),
(24, 'meta_title_lv', 'RalphMania - Interneta Veikals', 'seo', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(25, 'meta_title_en', 'RalphMania - Online Shop', 'seo', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(26, 'meta_description_lv', 'Plašs preču klāsts ar ātrāko piegādi Latvijā. Iepērcies ērti un izdevīgi!', 'seo', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(27, 'meta_description_en', 'Wide range of products with the fastest delivery in Latvia. Shop conveniently!', 'seo', '2026-01-15 19:00:43', '2026-01-15 19:00:43'),
(28, 'google_analytics_id', NULL, 'seo', '2026-01-15 19:00:43', '2026-01-15 19:09:52'),
(29, 'facebook_pixel_id', NULL, 'seo', '2026-01-15 19:00:43', '2026-01-15 19:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `subscriber_offers`
--

CREATE TABLE `subscriber_offers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(50) NOT NULL COMMENT 'Atlaides kods',
  `title_lv` varchar(100) NOT NULL COMMENT 'Nosaukums LV',
  `title_en` varchar(100) NOT NULL COMMENT 'Nosaukums EN',
  `description_lv` text DEFAULT NULL COMMENT 'Apraksts LV',
  `description_en` text DEFAULT NULL COMMENT 'Apraksts EN',
  `discount_type` enum('percentage','fixed') NOT NULL DEFAULT 'percentage' COMMENT 'Atlaides tips',
  `discount_value` decimal(10,2) NOT NULL COMMENT 'Atlaides vērtība',
  `min_order_amount` decimal(10,2) DEFAULT NULL COMMENT 'Minimālā pasūtījuma summa',
  `max_uses` int(11) DEFAULT NULL COMMENT 'Maksimālais izmantošanas skaits',
  `used_count` int(11) NOT NULL DEFAULT 0 COMMENT 'Izmantots reižu skaits',
  `subscribers_only` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Tikai abonentiem',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subscriber_offers`
--

INSERT INTO `subscriber_offers` (`id`, `code`, `title_lv`, `title_en`, `description_lv`, `description_en`, `discount_type`, `discount_value`, `min_order_amount`, `max_uses`, `used_count`, `subscribers_only`, `is_active`, `starts_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'Laipni lūgti RalphMania!', 'Welcome to RalphMania!', '10% atlaide jūsu pirmajam pasūtījumam kā jaunajam abonentam.', '10% off your first order as a new subscriber.', 'percentage', 10.00, 15.00, NULL, 4, 1, 1, NULL, '2026-12-31 21:59:59', '2026-02-18 14:44:44', '2026-02-25 16:22:31'),
(2, 'WINTER25', 'Ziemas izpārdošana', 'Winter Sale', 'Ekskluzīva 25% atlaide visiem apģērbiem!', 'Exclusive 25% off all clothing items!', 'percentage', 25.00, 30.00, 100, 13, 1, 1, '2025-12-31 22:00:00', '2026-02-28 21:59:59', '2026-02-18 14:44:44', '2026-02-20 20:28:45'),
(3, 'SAVE5', '€5 atlaide', '€5 Off', 'Ietaupiet €5 pasūtījumiem virs €25.', 'Save €5 on orders over €25.', 'fixed', 5.00, 25.00, 50, 9, 1, 1, NULL, '2026-03-31 20:59:59', '2026-02-18 14:44:44', '2026-02-24 17:07:34'),
(4, 'VIP15', 'VIP abonentu atlaide', 'VIP Subscriber Discount', '15% atlaide kā pateicība par uzticību!', '15% off as thanks for your loyalty!', 'percentage', 15.00, NULL, NULL, 1, 1, 1, NULL, NULL, '2026-02-18 14:44:44', '2026-02-25 12:44:32');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(30) NOT NULL COMMENT 'Lietotājvārds (max 30 chars)',
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL COMMENT 'E-pasta adrese (max 100 chars)',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Hashed password (min 8 chars, digits + uppercase)',
  `phone` varchar(20) DEFAULT NULL COMMENT 'Tālruņa numurs / Phone number',
  `birth_date` date DEFAULT NULL COMMENT 'Dzimšanas datums / Birth date',
  `country` varchar(50) DEFAULT 'Latvia' COMMENT 'Valsts / Country',
  `city` varchar(50) DEFAULT NULL COMMENT 'Pilsēta / City',
  `address` varchar(100) DEFAULT NULL COMMENT 'Adrese (max 100 chars) / Address',
  `postal_code` varchar(20) DEFAULT NULL COMMENT 'Pasta indekss / Postal code',
  `profile_picture` varchar(255) DEFAULT NULL COMMENT 'Profila bilde (.jpg, .png) / Profile picture',
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT 2 COMMENT 'FK to roles (1=guest, 2=user, 3=admin, 4=courier)',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Vai konts ir aktīvs / Is account active',
  `is_public` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Vai profils ir publiski redzams / Is profile publicly visible',
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Lietotāji ar lomu piešķiri / Users with role assignments';

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `email`, `email_verified_at`, `password`, `phone`, `birth_date`, `country`, `city`, `address`, `postal_code`, `profile_picture`, `role_id`, `is_active`, `is_public`, `last_login_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(2, 'Ralfs M.', 'Ralfs', 'Migals', 'ralph.migals@gmail.com', '2026-04-02 20:50:39', '$2y$12$XeiD8n1L4y3Auow3J.j6jO.l7jxsDllk3j5H4NPCZQv5cGwy1vWQi', '29123456', '2005-09-03', 'Latvia', 'Rīga', 'Hibrīda iela 4', 'LV-1097', 'avatars/t110fy6FDCPJmCyLS6HfTHfHsLNbsxavvnHKGhUX.png', 3, 1, 1, '2026-04-25 10:37:32', 'LEybO8V0l4yzauzlVSwXtck5Xh7K32bHGRYYKVXXUEzRTQCtZipwJAWmeyhD', '2025-12-07 11:41:46', '2026-04-25 10:37:32'),
(3, 'Courier Master', 'Roltona', 'Alts', 'courier@gmail.com', NULL, '$2y$12$0tvfebjkb6g7RseIXUYjLuyj/tBEcJzZgG9.53928R.yUKWgIJy7y', '+37129000000', '2005-09-02', 'Latvia', 'Rīga', 'Tukuma iela 3', 'LV-1093', NULL, 4, 1, 1, '2026-04-09 13:11:32', NULL, '2025-12-07 23:07:17', '2026-04-09 13:11:32'),
(5, 'RealRoltonsLV', 'Ralfs', 'Migals', 'ralphmania.roltonslv@gmail.com', '2026-01-08 13:20:32', '$2y$12$iOpm.hLBNaxQYO7zJxTXreKDMoCgqwWBbMEXTGvFNk42M0pZ7t676', NULL, NULL, 'Latvia', 'Rīga', 'Brīvības iela 1', 'LV-1001', 'avatars/qfMB1D0GTFl6E52xcNN8v0iafqSt31xwb7kZSYch.png', 3, 1, 1, '2026-04-25 10:46:22', 'dmlz7d22hfh09w3n7QS03hDERjjv5RFgyAD5HAhMYZs878n7CGN2c6SpvSnh', '2026-01-08 13:19:48', '2026-04-25 10:46:22'),
(7, 'Fish Sticks', 'fish', 'sticks', 'roltonsalt2@gmail.com', NULL, '$2y$12$cupfQk/H3PUuZ8EwzeNOf.rtCG2ip3gC4UngkuuUYkr9FmGmBv5sa', NULL, NULL, 'Latvia', NULL, NULL, NULL, NULL, 2, 1, 1, NULL, NULL, '2026-01-15 12:40:15', '2026-01-16 12:25:31'),
(9, 'albert130', 'Alberts', 'Einšteins', 'ralfs.migals@icloud.com', '2026-04-19 10:40:19', '$2y$12$wjj0kZXEgmeQN6f1V3.rWu1/GmS74AFN1BnJHl.GGnoifH8d2XFsm', NULL, NULL, 'Latvia', NULL, NULL, NULL, NULL, 2, 1, 1, '2026-04-19 11:24:50', NULL, '2026-04-19 10:39:37', '2026-04-24 21:32:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_logs_user` (`user_id`),
  ADD KEY `idx_activity_logs_type` (`activity_type`),
  ADD KEY `idx_activity_logs_created` (`created_at`);

--
-- Indexes for table `administrators`
--
ALTER TABLE `administrators`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_administrators_user` (`user_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `carts_user_id_unique` (`user_id`),
  ADD KEY `carts_session_id_index` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_items_user` (`user_id`),
  ADD KEY `idx_cart_items_session` (`session_id`),
  ADD KEY `idx_cart_items_product` (`product_id`),
  ADD KEY `cart_items_cart_id_index` (`cart_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `idx_categories_slug` (`slug`),
  ADD KEY `idx_categories_parent` (`parent_id`),
  ADD KEY `idx_categories_active` (`is_active`,`sort_order`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_comments_user` (`user_id`),
  ADD KEY `idx_comments_content` (`content_id`),
  ADD KEY `idx_comments_parent` (`parent_id`),
  ADD KEY `idx_comments_approved` (`is_approved`);

--
-- Indexes for table `comment_moods`
--
ALTER TABLE `comment_moods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comment_moods_comment_id_user_id_unique` (`comment_id`,`user_id`),
  ADD KEY `comment_moods_user_id_foreign` (`user_id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_contact_messages_user` (`user_id`),
  ADD KEY `idx_contact_messages_read` (`is_read`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `idx_content_slug` (`slug`),
  ADD KEY `idx_content_type` (`type`),
  ADD KEY `idx_content_category` (`category`),
  ADD KEY `idx_content_published` (`is_published`,`published_at`),
  ADD KEY `idx_content_creator` (`created_by`);
ALTER TABLE `content` ADD FULLTEXT KEY `idx_content_search_lv` (`title_lv`,`description_lv`);
ALTER TABLE `content` ADD FULLTEXT KEY `idx_content_search_en` (`title_en`,`description_en`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_usages_user_id_foreign` (`user_id`),
  ADD KEY `coupon_usages_order_id_foreign` (`order_id`),
  ADD KEY `coupon_usages_coupon_id_user_id_index` (`coupon_id`,`user_id`);

--
-- Indexes for table `couriers`
--
ALTER TABLE `couriers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_couriers_user` (`user_id`),
  ADD KEY `idx_couriers_active` (`is_active`);

--
-- Indexes for table `courier_assignments`
--
ALTER TABLE `courier_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_courier_assignments_courier` (`courier_id`),
  ADD KEY `idx_courier_assignments_order` (`order_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `likes`
--
ALTER TABLE `likes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_like` (`user_id`,`likeable_type`,`likeable_id`),
  ADD KEY `likes_likeable_type_likeable_id_index` (`likeable_type`,`likeable_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `newsletter_subscribers_email_unique` (`email`),
  ADD UNIQUE KEY `newsletter_subscribers_token_unique` (`token`),
  ADD KEY `newsletter_subscribers_user_id_foreign` (`user_id`),
  ADD KEY `newsletter_subscribers_email_index` (`email`),
  ADD KEY `newsletter_subscribers_is_active_index` (`is_active`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD UNIQUE KEY `idx_orders_number` (`order_number`),
  ADD KEY `idx_orders_user` (`user_id`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_created` (`created_at`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_items_order` (`order_id`),
  ADD KEY `idx_order_items_product` (`product_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`),
  ADD KEY `idx_password_reset_created` (`created_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payments_order` (`order_id`),
  ADD KEY `idx_payments_status` (`status`),
  ADD KEY `idx_payments_transaction` (`transaction_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `idx_products_slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_products_sku` (`sku`),
  ADD KEY `idx_products_category` (`category_id`),
  ADD KEY `idx_products_active` (`is_active`),
  ADD KEY `idx_products_price` (`price`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_products_search_lv` (`name_lv`,`description_lv`);
ALTER TABLE `products` ADD FULLTEXT KEY `idx_products_search_en` (`name_en`,`description_en`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reviews_user` (`user_id`),
  ADD KEY `idx_reviews_reviewable` (`reviewable_type`,`reviewable_id`),
  ADD KEY `idx_reviews_rating` (`rating`),
  ADD KEY `idx_reviews_approved` (`is_approved`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_roles_name` (`name`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`),
  ADD KEY `settings_group_index` (`group`);

--
-- Indexes for table `subscriber_offers`
--
ALTER TABLE `subscriber_offers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `subscriber_offers_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_username` (`username`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `administrators`
--
ALTER TABLE `administrators`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `comment_moods`
--
ALTER TABLE `comment_moods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `couriers`
--
ALTER TABLE `couriers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courier_assignments`
--
ALTER TABLE `courier_assignments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `likes`
--
ALTER TABLE `likes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `subscriber_offers`
--
ALTER TABLE `subscriber_offers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `administrators`
--
ALTER TABLE `administrators`
  ADD CONSTRAINT `administrators_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`content_id`) REFERENCES `content` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `comments_ibfk_3` FOREIGN KEY (`parent_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `comment_moods`
--
ALTER TABLE `comment_moods`
  ADD CONSTRAINT `comment_moods_comment_id_foreign` FOREIGN KEY (`comment_id`) REFERENCES `comments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comment_moods_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `content`
--
ALTER TABLE `content`
  ADD CONSTRAINT `content_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `coupon_usages_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `coupon_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `couriers`
--
ALTER TABLE `couriers`
  ADD CONSTRAINT `couriers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `courier_assignments`
--
ALTER TABLE `courier_assignments`
  ADD CONSTRAINT `courier_assignments_ibfk_1` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `courier_assignments_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `likes`
--
ALTER TABLE `likes`
  ADD CONSTRAINT `likes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD CONSTRAINT `newsletter_subscribers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
