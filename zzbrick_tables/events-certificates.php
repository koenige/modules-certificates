<?php 

/**
 * certificates module
 * Table definition for 'events/certificates'
 *
 * Part of »Zugzwang Project«
 * http://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2021 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


$zz['title'] = 'Certificates for an event';
$zz['table'] = 'events_certificates';

$zz['fields'][1]['title'] = 'ID';
$zz['fields'][1]['field_name'] = 'event_certificate_id';
$zz['fields'][1]['type'] = 'id';

$zz['fields'][2]['field_name'] = 'event_id';
$zz['fields'][2]['type'] = 'write_once';
$zz['fields'][2]['type_detail'] = 'select';
$zz['fields'][2]['sql'] = 'SELECT event_id, date_begin, event
	FROM events
	WHERE ISNULL(main_event_id)
	ORDER BY event';
$zz['fields'][2]['display_field'] = 'event';
$zz['fields'][2]['search'] = 'CONCAT(events.event, " ", IFNULL(event_year, YEAR(date_begin)))';
$zz['fields'][2]['unique'] = true;
$zz['fields'][2]['if']['where']['hide_in_form'] = true;
$zz['fields'][2]['link'] = [
	'string1' => '/intern/termine/',
	'field1' => 'event_identifier',
	'string2' => '/'
];
$zz['fields'][2]['dont_show_where_class'] = true;

$zz['fields'][3]['field_name'] = 'certificate_id';
$zz['fields'][3]['type'] = 'select';
$zz['fields'][3]['sql'] = 'SELECT certificate_id, certificate
	FROM certificates
	ORDER BY certificate';
$zz['fields'][3]['display_field'] = 'certificate';
$zz['fields'][3]['hide_in_list'] = true;
$zz['fields'][3]['suffix'] = ' – <a href="/intern/urkunden/" target="_new">Galerie aller Urkunden</a>';

$zz['fields'][4]['field_name'] = 'place';
$zz['fields'][4]['hide_in_list'] = true;

$zz['fields'][5]['field_name'] = 'date_of_certificate';
$zz['fields'][5]['dont_copy'] = true;
$zz['fields'][5]['hide_in_list'] = true;
$zz['fields'][5]['type'] = 'date';

$zz['fields'][6]['title'] = 'Signature, left';
$zz['fields'][6]['field_name'] = 'signature_left';
$zz['fields'][6]['hide_in_list'] = true;

$zz['fields'][7]['title'] = 'Signature, right';
$zz['fields'][7]['field_name'] = 'signature_right';
$zz['fields'][7]['hide_in_list'] = true;


$zz['sql'] = 'SELECT events_certificates.*
		, CONCAT(events.event, " ", IFNULL(event_year, YEAR(date_begin))) AS event
		, events.identifier AS event_identifier
		, certificates.certificate
	FROM events_certificates
	LEFT JOIN events USING (event_id)
	LEFT JOIN certificates USING (certificate_id)
';
$zz['sqlorder'] = ' ORDER BY events.date_begin DESC, events.time_begin DESC,
	events.identifier';

$zz['subtitle']['event_id']['sql'] = 'SELECT event
	, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
	FROM events';
$zz['subtitle']['event_id']['var'] = ['event', 'duration'];
$zz['subtitle']['event_id']['format'][1] = 'wrap_date';
$zz['subtitle']['event_id']['link'] = '../';
$zz['subtitle']['event_id']['link_no_append'] = true;

$zz_conf['copy'] = true;
