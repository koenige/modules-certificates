/**
 * certificates module
 * SQL for installation of certificates module
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


CREATE TABLE `certificates` (
  `certificate_id` int unsigned NOT NULL AUTO_INCREMENT,
  `certificate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `events_certificates` (
  `event_certificate_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `certificate_id` int unsigned NOT NULL,
  `place` varchar(63) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_certificate` date DEFAULT NULL,
  `signature_left` varchar(63) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_right` varchar(63) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`event_certificate_id`),
  UNIQUE KEY `event_id_certficate_id` (`event_id`,`certificate_id`),
  KEY `certficate_id` (`certificate_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'event_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'certificates', 'certificate_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'certificate_id', 'no-delete');
