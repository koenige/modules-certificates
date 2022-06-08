/**
 * certificates module
 * SQL updates
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */

/* 2022-06-05-1 */	ALTER TABLE `certificates` ADD `parameters` text COLLATE 'utf8mb4_unicode_ci' NULL;
/* 2022-06-07-1 */	ALTER TABLE `events_certificates` ADD `logo_medium_id` int NULL;
/* 2022-06-07-2 */	ALTER TABLE `events_certificates` ADD INDEX `logo_medium_id` (`logo_medium_id`);
/* 2022-06-07-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'events_certificates', 'event_certificate_id', 'logo_medium_id', 'no-delete');
/* 2022-06-08-1 */	CREATE TABLE `certificateelements` (`certificateelement_id` int unsigned NOT NULL AUTO_INCREMENT, `certificate_id` int unsigned NOT NULL, `element_category_id` int unsigned NOT NULL, `element_medium_id` int unsigned DEFAULT NULL, `parameters` text COLLATE utf8mb4_unicode_ci, PRIMARY KEY (`certificateelement_id`), KEY `certificate_id` (`certificate_id`), KEY `element_category_id` (`element_category_id`), KEY `element_medium_id` (`element_medium_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/* 2022-06-08-2 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'certificates', 'certificate_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'certificate_id', 'delete');
/* 2022-06-08-3 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'categories', 'category_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'element_category_id', 'no-delete');
/* 2022-06-08-4 */	INSERT INTO _relations (`master_db`, `master_table`, `master_field`, `detail_db`, `detail_table`, `detail_id_field`, `detail_field`, `delete`) VALUES ((SELECT DATABASE()), 'media', 'medium_id', (SELECT DATABASE()), 'certificateelements', 'certificateelement_id', 'element_medium_id', 'no-delete');
/* 2022-06-08-5 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Certificate Element', NULL, NULL, NULL, 'certificate-element', '&alias=certificate-element', NULL, NOW());
/* 2022-06-08-6 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Image', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/image', '&alias=certificate_element/image&type=image', 1, NOW());
/* 2022-06-08-7 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Logo', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/logo', '&alias=certificate_element/logo&type=logo', NULL, NOW());
/* 2022-06-08-8 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Event', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/event', '&alias=certificate_element/event&type=event', 3, NOW());
/* 2022-06-08-9 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Name', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/name', '&alias=certificate_element/name&type=name', 4, NOW());
/* 2022-06-08-10 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Organisation', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/organisation', '&alias=certificate_element/organisation&type=organisation', 5, NOW());
/* 2022-06-08-11 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Place & Date', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'ertificate-element/place-date', '&alias=certificate-element/place-date&type=place-date', 6, NOW());
/* 2022-06-08-12 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Signature, left', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/signature-left', '&alias=certificate-element/signature-left&type=signature-left', 7, NOW());
/* 2022-06-08-13 */	INSERT INTO categories (`category`, `category_short`, `description`, `main_category_id`, `path`, `parameters`, `sequence`, `last_update`) VALUES ('Signature, right', NULL, NULL, (SELECT category_id FROM categories c WHERE path = 'certificate-element'), 'certificate-element/signature-right', '&alias=certificate-element/signature-right&type=signature-right', 8, NOW());
