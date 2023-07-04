<?php

/**
 * certificates module
 * Overview of certificates
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2008, 2012, 2014-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


function mod_certificates_urkunden($params) {
	if (count($params) !== 2) return false;
	
	$sql = 'SELECT events.event_id, events.identifier, events.event
			, IFNULL(events.event_year, YEAR(events.date_begin)) AS year
			, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
			, (SELECT COUNT(*) FROM participations
				WHERE NOT ISNULL(urkundentext)
				AND event_id = events.event_id) AS spezialurkunden
			, IFNULL(place, places.contact) AS turnierort
			, tournaments.tabellenstaende
			, IF(main_series.category = "Reihen", series.category, main_series.category) AS series
		FROM events
		LEFT JOIN tournaments USING (event_id)
		JOIN participations
			ON participations.event_id = events.event_id
			AND participations.usergroup_id = %d
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN categories main_series
			ON main_series.category_id = series.main_category_id
		LEFT JOIN contacts places
			ON events.place_contact_id = places.contact_id
		LEFT JOIN addresses
			ON events.place_contact_id = addresses.contact_id
		WHERE (main_series.path = "reihen/%s" OR events.identifier = "%d/%s")
		AND IFNULL(events.event_year, YEAR(events.date_begin)) = %d
		ORDER BY series.sequence, events.date_begin, events.identifier
	';
	$sql = sprintf($sql
		, wrap_id('usergroups', 'spieler')
		, wrap_db_escape($params[1]), $params[0]
		, wrap_db_escape($params[1]), $params[0]
	);
	$data = wrap_db_fetch($sql, 'event_id');
	if (!$data) {
		$sql = 'SELECT events.event_id, events.identifier, events.event
				, IFNULL(events.event_year, YEAR(events.date_begin)) AS year
				, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
				, IFNULL(place, places.contact) AS turnierort
			FROM events
			LEFT JOIN contacts places
				ON events.place_contact_id = places.contact_id
			LEFT JOIN addresses
				ON events.place_contact_id = addresses.contact_id
			WHERE events.identifier = "%d/%s"
		';
		$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
		$data = wrap_db_fetch($sql);
		$data['keine_spieler'] = true;
		$event['duration'] = $data['duration'];
		$event['year'] = $data['year'];
	} else {
		foreach ($data as $event_id => $turnier) {
			$data[$event_id]['platz'][] = ['bereich' => ''];
			if ($turnier['tabellenstaende']) {
				$tabellenstaende = explode(',', $turnier['tabellenstaende']);
				if ($tabellenstaende) {
					foreach ($tabellenstaende as $tabellenstand) {
						if (!$tabellenstand) continue;
						$data[$event_id]['platz'][] = ['bereich' => $tabellenstand];
					}
				}
			}
		}
	
		$event = reset($data);
		if (count($data) > 1) {
			$data['event'] = $event['series'];
		} else {
			$data['event'] = $event['event'];
		}
		$data['duration'] = $event['duration'];
		$data['turnierort'] = $event['turnierort'];
	}

	$page['text'] = wrap_template('urkunden', $data);
	$page['title'] = 'Urkunden '.$data['event'].', '.wrap_date($event['duration']);
	$page['breadcrumbs'][]['title'] = 'Urkunden';
	$page['dont_show_h1'] = true;
	return $page;
}
