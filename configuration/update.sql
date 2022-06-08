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
