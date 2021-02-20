<?php 

/**
 * certificates module
 * Form for 'certificates' for an event
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$sql = 'SELECT event_id, event, YEAR(IFNULL(date_begin, date_end)) AS year, identifier
	FROM events
	WHERE identifier = "%d/%s"';
$sql = sprintf($sql, $brick['vars'][0], wrap_db_escape($brick['vars'][1]));
$event = wrap_db_fetch($sql);
if (!$event) wrap_quit(404);

$zz = zzform_include_table('events-certificates');
$zz['where']['event_id'] = $event['event_id'];

$zz_conf['referer'] = '../';
