/**
 * Zugzwang Project
 * SQL for installation of certificates module
 *
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020 Gustaf Mossakowski
 */


CREATE TABLE `urkunden` (
  `urkunde_id` int unsigned NOT NULL AUTO_INCREMENT,
  `urkunde_titel` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `kennung` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `kommentar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`urkunde_id`),
  UNIQUE KEY `kennung` (`kennung`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
