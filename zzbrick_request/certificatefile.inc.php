<?php 

/**
 * tournaments module
 * send a certificate preview file
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/tournaments
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * send a certificate preview file
 *
 * @param array $params
 * @return array
 */
function mod_certificates_certificatefile($params) {
	if (count($params) !== 1) return [];
	$filename = implode('/', $params).'.jpeg';
	$file['name'] = wrap_setting('certificates_preview_dir').'/'.$filename;
	wrap_send_file($file);
}
