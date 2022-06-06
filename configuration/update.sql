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
