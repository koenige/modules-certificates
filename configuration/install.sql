/**
 * certificates module
 * SQL for installation of certificates module
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2020-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


CREATE TABLE `certificates` (
  `certificate_id` int unsigned NOT NULL AUTO_INCREMENT,
  `certificate` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `identifier` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `remarks` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `parameters` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`certificate_id`),
  UNIQUE KEY `identifier` (`identifier`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE `certificateelements` (
  `certificateelement_id` int unsigned NOT NULL AUTO_INCREMENT,
  `certificate_id` int unsigned NOT NULL,
  `element_category_id` int unsigned NOT NULL,
  `element_medium_id` int unsigned DEFAULT NULL,
  `parameters` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`certificateelement_id`),
  KEY `certificate_id` (`certificate_id`),
  KEY `element_category_id` (`element_category_id`),
  KEY `element_medium_id` (`element_medium_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'certificates', 'certificate_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'certificate_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'element_category_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'element_medium_id', 'no-delete');

INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Certificate Element', NULL, NULL, NULL, 'certificate-element', '&alias=certificate-element', NULL, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Image', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/image', '&alias=certificate_element/image&type=image', 1, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Logo', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/logo', '&alias=certificate_element/logo&type=logo', NULL, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Event', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/event', '&alias=certificate_element/event&type=event', 3, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Name', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/name', '&alias=certificate_element/name&type=name', 4, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Organisation', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/organisation', '&alias=certificate_element/organisation&type=organisation', 5, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Place & Date', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'ertificate-element/place-date', '&alias=certificate-element/place-date&type=place-date', 6, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Signature, left', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/signature-left', '&alias=certificate-element/signature-left&type=signature-left', 7, NOW());
INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Signature, right', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/signature-right', '&alias=certificate-element/signature-right&type=signature-right', 8, NOW());

CREATE TABLE `events_certificates` (
  `event_certificate_id` int unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int unsigned NOT NULL,
  `certificate_id` int unsigned NOT NULL,
  `place` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_certificate` date DEFAULT NULL,
  `signature_left` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signature_right` varchar(63) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_medium_id` int DEFAULT NULL,
  PRIMARY KEY (`event_certificate_id`),
  UNIQUE KEY `event_id_certficate_id` (`event_id`,`certificate_id`),
  KEY `certficate_id` (`certificate_id`),
  KEY `logo_medium_id` (`logo_medium_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'events', 'event_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'event_id', 'delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'certificates', 'certificate_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'certificate_id', 'no-delete');
INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'logo_medium_id', 'no-delete');
