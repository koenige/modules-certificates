<?php 

/**
 * certificates module
 * Table definition for 'certificates'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2008, 2012, 2014, 2019-2023, 2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Certificates';
$zz['table'] = 'certificates';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'certificate_id';
$zz['fields'][1]['type'] = 'id';

if (wrap_setting('certificates_preview_media_folder')) {
	$zz['fields'][15]['title'] = 'Preview';
	$zz['fields'][15]['field_name'] = 'bild';
	$zz['fields'][15]['type'] = 'upload_image';
	$zz['fields'][15]['path'] = [
		'root' => wrap_setting('media_folder').wrap_setting('certificates_preview_media_folder').'/',
		'webroot' => wrap_setting('media_internal_path').wrap_setting('certificates_preview_media_folder').'/',
		'field1' => 'identifier', 
		'string2' => '.jpeg'
	];
	$zz['fields'][15]['optional_image'] = true;
	$zz['fields'][15]['input_filetypes'] = ['jpeg', 'tiff', 'gif', 'png'];
	
	$zz['fields'][15]['image'][0]['title'] = 'gro&szlig;';
	$zz['fields'][15]['image'][0]['field_name'] = 'gross';
	$zz['fields'][15]['image'][0]['width'] = 298; 
	$zz['fields'][15]['image'][0]['height'] = 421;
	$zz['fields'][15]['image'][0]['action'] = 'thumbnail';
	$zz['fields'][15]['image'][0]['path'] = $zz['fields'][15]['path'];
}

$zz['fields'][2]['title'] = 'Certificate';
$zz['fields'][2]['field_name'] = 'certificate';

$zz['fields'][3]['field_name'] = 'identifier';
$zz['fields'][3]['type'] = 'identifier';
$zz['fields'][3]['fields'] = ['certificate', 'identifier'];
$zz['fields'][3]['hide_in_list'] = true;

$zz['fields'][4]['field_name'] = 'remarks';
$zz['fields'][4]['type'] = 'memo';
$zz['fields'][4]['hide_in_list'] = true;

$zz['fields'][5]['field_name'] = 'parameters';
$zz['fields'][5]['type'] = 'parameter';
$zz['fields'][5]['hide_in_list'] = true;

$zz['fields'][6] = zzform_include('certificateelements');
$zz['fields'][6]['title'] = 'Elements';
$zz['fields'][6]['type'] = 'subtable';
$zz['fields'][6]['fields'][2]['type'] = 'foreign_key';


$zz['sql'] = 'SELECT certificates.*
	FROM certificates
';
$zz['sqlorder'] = ' ORDER BY certificate DESC';

$zz['list']['display'] = 'ul';

if (!wrap_access('certificates_templates_edit')) $zz['access'] = 'none';
