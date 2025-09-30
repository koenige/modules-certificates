<?php

/**
 * certificates module
 * Overview of certificates
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2008, 2012, 2014-2022, 2024-2025 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_certificates_certificates($params, $setting, $data) {
	if (count($params) !== 2) return false;
	
	$sql = 'SELECT events.event_id, events.identifier, events.event
			, IFNULL(events.event_year, YEAR(events.date_begin)) AS year
			, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
			, (SELECT COUNT(*) FROM participations
				WHERE NOT ISNULL(urkundentext)
				AND event_id = events.event_id) AS spezialurkunden
			, IFNULL(place, places.contact) AS place
			, tournaments.tabellenstaende
		FROM events
		LEFT JOIN tournaments USING (event_id)
		JOIN participations
			ON participations.event_id = events.event_id
			AND participations.usergroup_id = /*_ID usergroups spieler _*/
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN events_contacts events_places
			ON events.event_id = events_places.event_id
			AND events_places.role_category_id = /*_ID categories roles/location _*/
			AND events_places.sequence = 1
		LEFT JOIN contacts places
			ON events_places.contact_id = places.contact_id
		LEFT JOIN addresses
			ON events_places.contact_id = addresses.contact_id
		WHERE main_event_id = %d OR events.event_id = %d
		ORDER BY series.sequence, events.date_begin, events.identifier
	';
	$sql = sprintf($sql, $data['event_id'], $data['event_id']);
	$data['events'] = wrap_db_fetch($sql, 'event_id');

	if (!$data['events']) {
		$data['no_participants'] = true;
	} else {
		foreach ($data['events'] as $event_id => $event) {
			if ($event['spezialurkunden'] === '0')
				$data['events'][$event_id]['spezialurkunden'] = NULL;
			$data['events'][$event_id]['platz'][] = ['bereich' => ''];
			if (!$event['tabellenstaende']) continue;
			$tabellenstaende = explode(',', $event['tabellenstaende']);
			foreach ($tabellenstaende as $tabellenstand) {
				if (!$tabellenstand) continue;
				$data['events'][$event_id]['platz'][] = ['bereich' => $tabellenstand];
			}
		}
	}

	$page['text'] = wrap_template('certificates', $data);
	$page['title'] = 'Urkunden '.$data['event'].', '.wrap_date($data['duration']);
	$page['breadcrumbs'][]['title'] = 'Urkunden';
	$page['dont_show_h1'] = true;
	return $page;
}
