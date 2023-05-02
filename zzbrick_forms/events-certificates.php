<?php 

/**
 * certificates module
 * Form for 'certificates' for an event
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021, 2023 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


if (empty($brick['data']['event_id'])) wrap_quit(404);

$zz = zzform_include('events-certificates');
$zz['where']['event_id'] = $brick['data']['event_id'];

$zz_conf['referer'] = '../';
