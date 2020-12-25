<?php 

// deutsche-schachjugend.de, dem2012.de
// Copyright (c) 2008, 2012, 2014-2020 Gustaf Mossakowski <gustaf@koenige.org>
// Urkundendruck in PDF


/**
 * Druck der Urkunden für ein Turnier
 *
 * @param array $params
 *		[0]: Jahr
 *		[1]: Turnierkennung
 *		[2]: (optional) 'urkunden'
 *		[3]: Typ 'teilnahme.pdf', 'spezial.pdf', 'platz.pdf', 'platz-w.pdf' etc.
 * @return array $page
 */
function mod_certificates_urkunde($params) {
	global $zz_conf;
	global $zz_setting;
	
	if (count($params) === 4 AND $params[2] === 'urkunden') {
		unset ($params[2]);
	}
	if (count($params) !== 3) return false;
	$params = array_values($params);
	if (substr($params[2], -4) !== '.pdf') return false;

	// Turnier
	// @todo ggf. Urkundenstandardtext überschreibbar machen
	$sql = 'SELECT event_id, event, runden, YEAR(events.date_begin) AS year
			, urkunde_ort, urkunde_datum
			, urkunde_unterschrift1, urkunde_unterschrift2
			, certificates.identifier AS urkunde_kennung
			, SUBSTRING_INDEX(series.path, "/", -1) AS series_path
			, SUBSTRING_INDEX(SUBSTRING(series.path FROM 8), "/", 1) AS main_series
			, series.category AS series
			, series.category_short AS series_short
			, events.identifier
			, tabellenstaende, urkunde_parameter, alter_max
			, IF(turniere.geschlecht = "w", 1, NULL) AS weiblich
			, IF(events.offen = "ja", 1 , NULL) AS offen
			, SUBSTRING_INDEX(event_categories.path, "/", -1) AS event_category
		FROM events
		LEFT JOIN turniere USING (event_id)
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN categories event_categories
			ON events.event_category_id = event_categories.category_id
		LEFT JOIN certificates USING (certificate_id)
		WHERE events.identifier = "%d/%s"';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$turnier = wrap_db_fetch($sql);
	if (!$turnier) return false;
	if (empty($turnier['urkunde_kennung'])) {
		$page['url_ending'] = 'none';
		$page['text'] = '<p class="error">Bitte wähle erst in den <a href="../turnier/">Turniereinstellungen</a> eine Urkunde aus!</p>';
		return $page;
	}

	if ($turnier['urkunde_parameter']) {
		parse_str($turnier['urkunde_parameter'], $parameter);
		unset($turnier['urkunde_parameter']);
		$turnier = array_merge($parameter, $turnier);
	}
	if (!isset($turnier['platzurkunden'])) {
		$turnier['platzurkunden'] = wrap_get_setting('platzurkunden');
	}
	$turnier['urkundentext'] = 'hat mit Erfolg teilgenommen';
	if (!isset($turnier['turnierzahl'])) {
		$turnier['turnierzahl'] = false;
	}
	$turnier['urkunde_datum'] = datum_de_lang($turnier['urkunde_datum']);

	// Urkundentyp
	$type = substr($params[2], 0, -4);
	$possible_types = ['teilnahme', 'spezial', 'platz'];
	$tabellenstaende = explode(',', $turnier['tabellenstaende']);
	foreach ($tabellenstaende as $tabellenstand) {
		if (!$tabellenstand) continue;
		$possible_types[] = 'platz-'.$tabellenstand;
	}
	if (!in_array($type, $possible_types)) return false;
	$where = [];
	$filter_kennung = '';
	if ($type === 'spezial') {
		$where[] = 'NOT ISNULL(urkundentext)';
	} elseif (substr($type, 0, 6) === 'platz-') {
		$filter_kennung = substr($type, 6);
		if (isset($turnier['platzurkunden_'.$filter_kennung])) {
			$turnier['platzurkunden'] = $turnier['platzurkunden_'.$filter_kennung];
		}
		if ($filter_kennung === 'w') $turnier['weiblich'] = true;
		$type = 'platz';
	}
	if ($type === 'platz') {
		$order_by_limit = sprintf('ORDER BY platz_no, t_nachname, t_vorname LIMIT %d ', $turnier['platzurkunden']);
	} else {
		$order_by_limit = 'ORDER BY t_nachname, t_vorname, person_id';
	}
	$filter = my_tabellenstand_filter($filter_kennung);
	if ($filter['error']) return false;
	$where = array_merge($where, $filter['where']);

	// Titel des Turniers
	$turnier['obertitel'] = '';
	$turnier['obertitel_dativ'] = '';
	$turnier['vereinsprefix'] = '';
	switch ($turnier['main_series']) {
	case 'dem':
		$turnier['titel'] = 'Deutsche Einzelmeisterschaft';
		$turnier['titel_dativ'] = 'Deutschen Einzelmeisterschaft';
		if (substr($turnier['series_path'], 0, 4) === 'odjm') {
			$turnier['titel'] = 'Deutsche Juniorenmeisterschaft';
			$turnier['titel_dativ'] = 'Deutschen Juniorenmeisterschaft';
		} elseif ($turnier['series_path'] === 'kika') {
			$turnier['titel'] = 'Kinderschachturnier der DSJ';
			$turnier['titel_dativ'] = 'Kinderschachturniers der DSJ';
		}
		if ($turnier['turnierzahl']) {
			$turnier['obertitel'] .= $turnier['turnierzahl'].'. ';
			$turnier['obertitel_dativ'] .= $turnier['turnierzahl'].'. ';
		} else {
			$turnier['titel'] .= ' '.$turnier['year'];
			$turnier['titel_dativ'] .= ' '.$turnier['year'];
		}
		if ($turnier['offen']) {
			if ($turnier['series_path'] === 'kika')
				$turnier['obertitel'] .= 'Offenes ';
			else
				$turnier['obertitel'] .= 'Offene ';
			$turnier['obertitel_dativ'] .= 'Offenen ';
		}
		if ($turnier['weiblich']) {
			$turnier['untertitel'] = 'der Altersklasse unter '.$turnier['alter_max'].' Jahren weiblich';
		} elseif ($turnier['series_path'] == 'odjm-a') {
			$turnier['untertitel'] = 'A-Turnier';
		} elseif ($turnier['series_path'] == 'odjm-b') {
			$turnier['untertitel'] = 'B-Turnier';
		} elseif ($turnier['series_path'] == 'odjm-c') {
			$turnier['untertitel'] = 'C-Turnier';
		} else {
			$turnier['untertitel'] = 'der Altersklasse unter '.$turnier['alter_max'].' Jahren';
			if ($turnier['series_path'] == 'odem-u25-a') $turnier['untertitel'] .= ' (A-Turnier)';
			elseif ($turnier['series_path'] == 'odem-u25-b') $turnier['untertitel'] .= ' (B-Turnier)';
		}
		break;
	case 'dsm':
	case 'dvm':
		$turnier['vereinsprefix'] = 'mit ';
		$turnier['titel'] = explode(' ', $turnier['series']);
		if ($turnier['main_series'] === 'dsm') {
			$turnier['untertitel'] = 'Wettkampfklasse '.array_pop($turnier['titel']);
			array_pop($turnier['titel']);
		} else {
			$turnier['untertitel'] = 'Altersklasse '.array_pop($turnier['titel']);
		}
		$turnier['titel'] = implode(' ', $turnier['titel']).' '.$turnier['year'];
		$turnier['titel_dativ'] = str_replace('Deutsche', 'Deutschen', $turnier['titel']);
		break;
	case 'dlm':
		$turnier['vereinsprefix'] = 'mit ';
		$turnier['titel'] = $turnier['series'].' '.$turnier['year'];
		$turnier['obertitel'] = '';
		$turnier['untertitel'] = '';
		break;
	default:
		$turnier['titel'] = $turnier['series'].' '.$turnier['year'];
		$turnier['obertitel'] = '';
		$turnier['untertitel'] = '';
		break;
	}

	// Teams?
	if ($turnier['event_category'] === 'mannschaft') {
		$sql = 'SELECT teams.team_id
				, CONCAT(team, IFNULL(CONCAT(" ", team_no), "")) AS spieler
				, (SELECT
					GROUP_CONCAT(CONCAT(t_vorname, " ", IFNULL(CONCAT(t_namenszusatz, " "), ""), t_nachname) ORDER BY brett_no SEPARATOR ", ") AS spieler
					FROM teilnahmen
					WHERE teilnahmen.team_id = teams.team_id
					AND NOT ISNULL(brett_no)) AS verein
				, tabellenstaende.platz_no
				, tabellenstaende.platz_no AS rang
			FROM teams
			LEFT JOIN tabellenstaende
				ON tabellenstaende.team_id = teams.team_id
				AND tabellenstaende.runde_no = %d
			WHERE teams.event_id = %d
			ORDER BY platz_no, team, team_no';
		$sql = sprintf($sql, $turnier['runden'], $turnier['event_id']);
		$data = wrap_db_fetch($sql, 'team_id');
		// @todo $where
		// @todo ORDER BY
	} else {
		// Spieler
		$sql = 'SELECT teilnahmen.person_id
				, CONCAT(teilnahmen.t_vorname, " ", IFNULL(CONCAT(teilnahmen.t_namenszusatz, " "), ""), teilnahmen.t_nachname) AS spieler
				, CONCAT(teilnahmen.t_vorname, " ", IFNULL(CONCAT(teilnahmen.t_namenszusatz, " "), "")) AS vorname
				, teilnahmen.t_nachname AS nachname
				, t_verein AS verein
				, urkundentext
				, tabellenstaende.platz_no
			FROM teilnahmen
			LEFT JOIN personen USING (person_id)
			LEFT JOIN tabellenstaende
				ON tabellenstaende.person_id = teilnahmen.person_id
				AND tabellenstaende.event_id = teilnahmen.event_id
				AND tabellenstaende.runde_no = %d
			WHERE teilnahmen.event_id = %d AND usergroup_id = %d
			AND NOT ISNULL(teilnahmen.person_id)
			%s
			%s
		';
		$sql = sprintf($sql, $turnier['runden']
			, $turnier['event_id']
			, wrap_id('usergroups', 'spieler')
			, $where ? ' AND '.implode(' AND ', $where) : ''
			, $order_by_limit
		);
		$data = wrap_db_fetch($sql, 'person_id');
	}
	foreach ($data as $id => $line) {
		$data[$id]['verein'] = $turnier['vereinsprefix'].$line['verein'];
		switch ($type) {
		case 'teilnahme':
			$data[$id]['textzeile'] = $turnier['urkundentext'];
			break;
		case 'spezial':
			$data[$id]['textzeile'] = $line['urkundentext'];
			break;
		} 
	}

	if ($turnier['event_category'] === 'einzel') {
		$i = 1;
		foreach ($data as $person_id => $person) {
			if (function_exists('my_verein_saeubern')) {
				$data[$person_id]['verein'] = my_verein_saeubern($person['verein']);
			}
			if ($type === 'platz' AND !empty($filter['kennung'])) {
				$data[$person_id]['rang'] = $i;
				$i++;
			} else {
				$data[$person_id]['rang'] = $person['platz_no'];
			}
		}
	}

	$vorlagen = $zz_setting['media_folder'].'/urkunden-grafiken';
	require_once $zz_setting['modules_dir'].'/default/libraries/tfpdf.inc.php';
	require_once __DIR__.'/urkunden/'.$turnier['urkunde_kennung'].'.inc.php';
	$pdf = cms_urkunde_out($turnier, $data, $vorlagen, $type);

	$folder = $zz_setting['cache_dir'].'/urkunden/'.$turnier['identifier'];
	wrap_mkdir($folder);
	if (file_exists($folder.'/urkunde-'.$type.'.pdf')) {
		unlink($folder.'/urkunde-'.$type.'.pdf');
	}
	$file['name'] = $folder.'/urkunde-'.$type.'.pdf';
	$file['send_as'] = $turnier['year'].' '.$turnier['series_short'].' Urkunden '.ucfirst($type).'.pdf';
	$file['etag_generate_md5'] = true;

	$pdf->output($file['name']);
	wrap_file_send($file);
	exit;
}

function datum_de_lang($datum) {
	if (!$datum) return '';
	$datum_de = explode("-", $datum);
	if (substr($datum_de[2], 0, 1) === '0') {
		$datum_de[2] = substr($datum_de[2], 1);
	}
	return $datum_de[2].'. '.monat($datum_de[1]).' '.$datum_de[0];	
}

function monat($monat) {
	switch ($monat) {
		case '1': return 'Januar';
		case '2': return 'Februar';
		case '3': return 'März';
		case '4': return 'April';
		case '5': return 'Mai';
		case '6': return 'Juni';
		case '7': return 'Juli';
		case '8': return 'August';
		case '9': return 'September';
		case '10': return 'Oktober';
		case '11': return 'November';
		case '12': return 'Dezember';
	}
}

/**
 * Umbruch von langen Vereinsnamen auf zwei Zeilen
 *
 * @param string $verein
 * @param int $max_len
 * @param int $len_per_row
 * @return array
 */
function cms_urkunde_zeile_anpassen($verein, $max_len, $len_per_row) {
	if (strlen($verein) < $max_len) return [$verein];
	if (strstr($verein, ', ')) {
		$vereinteile = explode(', ', $verein);
		$concat = ', ';
	} else {
		$vereinteile = explode(' ', $verein);
		$concat = ' ';
	}
	$verein = [0 => ''];
	$i = 0;
	foreach ($vereinteile as $vereinteil) {
		if (strlen($verein[$i].$vereinteil) > $len_per_row) $i++;
		if (!empty($verein[$i]))
			$verein[$i] .= $concat;
		else 
			$verein[$i] = '';
		if (strlen($vereinteil) >= $len_per_row AND strstr($vereinteil, '-')) {
			$vereinteil = explode('-', $vereinteil);
			foreach ($vereinteil as $index => $unterteilung) {
				if (strlen($verein[$i].$unterteilung) >= $len_per_row) {
					$i++;
					$verein[$i] = '';
				}
				$verein[$i] .= $unterteilung;
				if ($index < count($vereinteil) - 1) {
					$verein[$i] .= '-';
				}
			}
		} else {
			$verein[$i] .= $vereinteil;
		}
	}
	if (empty($verein[0])) array_shift($verein); // falls erster String zu lang!
	return $verein;
}
