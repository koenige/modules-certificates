<?php

// deutsche-schachjugend.de / dem2012.de
// Copyright (c) 2008, 2012, 2014-2020 Gustaf Mossakowski <gustaf@koenige.org>
// Übersicht Urkunden


function mod_certificates_urkunden($params) {
	if (count($params) !== 2) return false;
	
	$sql = 'SELECT events.event_id, events.identifier, events.event
			, YEAR(events.date_begin) AS jahr
			, CONCAT(events.date_begin, IFNULL(CONCAT("/", events.date_end), "")) AS duration
			, (SELECT COUNT(teilnahme_id) FROM teilnahmen
				WHERE NOT ISNULL(urkundentext)
				AND event_id = events.event_id) AS spezialurkunden
			, IFNULL(place, places.contact) AS turnierort
			, turniere.tabellenstaende
			, IF(main_series.category = "Reihen", series.category, main_series.category) AS series
		FROM events
		LEFT JOIN turniere USING (event_id)
		JOIN teilnahmen
			ON teilnahmen.event_id = events.event_id
			AND teilnahmen.usergroup_id = %d
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN categories main_series
			ON main_series.category_id = series.main_category_id
		LEFT JOIN contacts places
			ON events.place_contact_id = places.contact_id
		LEFT JOIN addresses
			ON events.place_contact_id = addresses.contact_id
		WHERE (main_series.path = "reihen/%s" OR events.identifier = "%d/%s")
		AND YEAR (events.date_begin) = %d
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
				, YEAR(events.date_begin) AS jahr
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
		$event['jahr'] = $data['jahr'];
		$event['duration'] = $data['duration'];
	} else {
		foreach ($data as $event_id => $turnier) {
			$tabellenstaende = explode(',', $turnier['tabellenstaende']);
			$data[$event_id]['platz'][] = ['bereich' => ''];
			if ($tabellenstaende) {
				foreach ($tabellenstaende as $tabellenstand) {
					if (!$tabellenstand) continue;
					$data[$event_id]['platz'][] = ['bereich' => $tabellenstand];
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
	$page['breadcrumbs'][] = '<a href="../../">'.$event['jahr'].'</a>';
	$page['title'] = $data['event'].', '.wrap_date($event['duration']);
	$page['breadcrumbs'][] = '<a href="../">'.$data['event'].'</a>';
	$page['breadcrumbs'][] = 'Urkunden';
	$page['dont_show_h1'] = true;
	return $page;
}
