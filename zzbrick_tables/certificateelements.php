<?php 

/**
 * certificates module
 * Table definition for 'certificate elements'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Elements of Certificates';
$zz['table'] = 'certificateelements';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'certificateelement_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'certificate_id';
$zz['fields'][2]['type'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT certificate_id, certificate
	FROM certificates
	ORDER BY certificate';
$zz['fields'][2]['display_field'] = 'certificate';

$zz['fields'][3]['title'] = 'Element';
$zz['fields'][3]['field_name'] = 'element_category_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = sprintf('SELECT category_id, category
	FROM categories
	WHERE main_category_id = %d
	ORDER BY sequence, category', wrap_category_id('certificate-element'));
$zz['fields'][3]['display_field'] = 'category';

$zz['fields'][4]['title'] = 'Image';
$zz['fields'][4]['field_name'] = 'element_medium_id';
$zz['fields'][4]['id_field_name'] = 'medium_id';
$zz['fields'][4]['type'] = 'select';
$zz['fields'][4]['sql'] = sprintf('SELECT /*_PREFIX_*/media.medium_id
		, folders.title AS folder
		, CONCAT("[", /*_PREFIX_*/media.medium_id, "] ", /*_PREFIX_*/media.title) AS image
	FROM /*_PREFIX_*/media 
	LEFT JOIN /*_PREFIX_*/media folders
		ON /*_PREFIX_*/media.main_medium_id = folders.medium_id
	WHERE /*_PREFIX_*/media.filetype_id != %d
	ORDER BY folders.title, /*_PREFIX_*/media.title', wrap_filetype_id('folder'));
$zz['fields'][4]['sql_character_set'][1] = 'utf8';
$zz['fields'][4]['sql_character_set'][2] = 'utf8';
$zz['fields'][4]['display_field'] = 'image';
$zz['fields'][4]['group'] = 'folder';
$zz['fields'][4]['exclude_from_search'] = true;

$zz['fields'][5]['field_name'] = 'parameters';
$zz['fields'][5]['type'] = 'parameter';
$zz['fields'][5]['hide_in_list'] = true;



$zz['sql'] = 'SELECT certificateelements.*
		, certificates.certificate
		, categories.category
	FROM certificateelements
	LEFT JOIN certificates USING (certificate_id)
	LEFT JOIN categories
		ON certificateelements.element_category_id = categories.category_id
	LEFT JOIN /*_PREFIX_*/media
		ON /*_PREFIX_*/media.medium_id = certificateelements.element_medium_id
	LEFT JOIN /*_PREFIX_*/filetypes AS o_mime USING (filetype_id)
	LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
		ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
';
$zz['sqlorder'] = ' ORDER BY certificate, categories.sequence, categories.category';

