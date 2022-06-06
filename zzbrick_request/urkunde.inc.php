<?php 

/**
 * certificates module
 * create PDF for certificates for print
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2008, 2012, 2014-2022 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * Druck der Urkunden für ein Turnier
 *
 * @param array $params
 *		[0]: Jahr
 *		[1]: Turnierkennung
 *		[2]: (optional) 'urkunden'
 *		[3]: Typ 'teilnahme.pdf', 'spezial.pdf', 'platz.pdf', 'platz-w.pdf' etc.
 *				or 'bearbeiten'
 * @return array $page
 */
function mod_certificates_urkunde($params) {
	global $zz_conf;
	global $zz_setting;
	
	if (count($params) === 4 AND $params[2] === 'urkunden') {
		unset($params[2]);
	}
	if (count($params) !== 3) return false;
	$params = array_values($params);
	if ($params[2] === 'bearbeiten') return brick_format('%%% forms events-certificates '.$params[0].' '.$params[1].' %%%');
	if (substr($params[2], -4) !== '.pdf') return false;

	// Turnier
	// @todo ggf. Urkundenstandardtext überschreibbar machen
	$sql = 'SELECT event_id, event, runden, IFNULL(events.event_year, YEAR(events.date_begin)) AS year
			, place, date_of_certificate
			, signature_left, signature_right
			, certificates.identifier AS urkunde_kennung
			, SUBSTRING_INDEX(series.path, "/", -1) AS series_path
			, SUBSTRING_INDEX(SUBSTRING(series.path FROM 8), "/", 1) AS main_series
			, series.category AS series
			, series.category_short AS series_short
			, events.identifier
			, tabellenstaende, urkunde_parameter, alter_max
			, IF(tournaments.geschlecht = "w", 1, NULL) AS weiblich
			, IF(events.offen = "ja", 1 , NULL) AS offen
			, SUBSTRING_INDEX(event_categories.path, "/", -1) AS event_category
		FROM events
		LEFT JOIN events_certificates USING (event_id)
		LEFT JOIN certificates USING (certificate_id)
		LEFT JOIN tournaments USING (event_id)
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN categories event_categories
			ON events.event_category_id = event_categories.category_id
		WHERE events.identifier = "%d/%s"';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$event = wrap_db_fetch($sql);
	if (!$event) return false;
	if (empty($event['urkunde_kennung'])) {
		$page['url_ending'] = 'none';
		$page['title'] = $event['event'].' '.$event['year'];
		$page['breadcrumbs'][] = '<a href="../">'.$event['event'].' '.$event['year'].'</a>';
		$page['breadcrumbs'][] = 'Urkunde';
		$page['text'] = '<p class="error">Bitte wähle erst <a href="./bearbeiten/">eine Urkunde aus!</a></p>';
		return $page;
	}

	if ($event['urkunde_parameter']) {
		parse_str($event['urkunde_parameter'], $parameter);
		unset($event['urkunde_parameter']);
		$event = array_merge($parameter, $event);
	}
	if (!isset($event['platzurkunden'])) {
		$event['platzurkunden'] = wrap_get_setting('platzurkunden');
	}
	$event['urkundentext'] = 'hat mit Erfolg teilgenommen';
	if (!isset($event['turnierzahl'])) {
		$event['turnierzahl'] = false;
	}
	$event['date_of_certificate'] = datum_de_lang($event['date_of_certificate']);

	// Urkundentyp
	$type = substr($params[2], 0, -4);
	$possible_types = ['teilnahme', 'spezial', 'platz'];
	if ($event['tabellenstaende']) {
		$tabellenstaende = explode(',', $event['tabellenstaende']);
		foreach ($tabellenstaende as $tabellenstand) {
			if (!$tabellenstand) continue;
			$possible_types[] = 'platz-'.$tabellenstand;
		}
	}
	if (!in_array($type, $possible_types)) return false;
	$where = [];
	$filter_kennung = '';
	if ($type === 'spezial') {
		$where[] = 'NOT ISNULL(urkundentext)';
	} elseif (substr($type, 0, 6) === 'platz-') {
		$filter_kennung = substr($type, 6);
		if (isset($event['platzurkunden_'.$filter_kennung])) {
			$event['platzurkunden'] = $event['platzurkunden_'.$filter_kennung];
		}
		if ($filter_kennung === 'w') $event['weiblich'] = true;
		$type = 'platz';
	}
	if ($type === 'platz') {
		$order_by_limit = sprintf('ORDER BY platz_no, t_nachname, t_vorname LIMIT %d ', $event['platzurkunden']);
	} else {
		$order_by_limit = 'ORDER BY t_nachname, t_vorname, person_id';
	}
	$filter = mf_tournaments_standings_filter($filter_kennung);
	if ($filter['error']) return false;
	$where = array_merge($where, $filter['where']);

	// Titel des Turniers
	$event['obertitel'] = '';
	$event['obertitel_dativ'] = '';
	$event['vereinsprefix'] = '';
	switch ($event['main_series']) {
	case 'dem':
		$event['titel'] = 'Deutsche Einzelmeisterschaft';
		$event['titel_dativ'] = 'Deutschen Einzelmeisterschaft';
		if (substr($event['series_path'], 0, 4) === 'odjm') {
			$event['titel'] = 'Deutsche Juniorenmeisterschaft';
			$event['titel_dativ'] = 'Deutschen Juniorenmeisterschaft';
		} elseif ($event['series_path'] === 'kika') {
			$event['titel'] = 'Kinderschachturnier der DSJ';
			$event['titel_dativ'] = 'Kinderschachturniers der DSJ';
		}
		if ($event['turnierzahl']) {
			$event['obertitel'] .= $event['turnierzahl'].'. ';
			$event['obertitel_dativ'] .= $event['turnierzahl'].'. ';
		} else {
			$event['titel'] .= ' '.$event['year'];
			$event['titel_dativ'] .= ' '.$event['year'];
		}
		if ($event['offen']) {
			if ($event['series_path'] === 'kika')
				$event['obertitel'] .= 'Offenes ';
			else
				$event['obertitel'] .= 'Offene ';
			$event['obertitel_dativ'] .= 'Offenen ';
		}
		if ($event['weiblich']) {
			$event['untertitel'] = 'der Altersklasse unter '.$event['alter_max'].' Jahren weiblich';
		} elseif ($event['series_path'] == 'odjm-a') {
			$event['untertitel'] = 'A-Turnier';
		} elseif ($event['series_path'] == 'odjm-b') {
			$event['untertitel'] = 'B-Turnier';
		} elseif ($event['series_path'] == 'odjm-c') {
			$event['untertitel'] = 'C-Turnier';
		} else {
			$event['untertitel'] = 'der Altersklasse unter '.$event['alter_max'].' Jahren';
			if ($event['series_path'] == 'odem-u25-a') $event['untertitel'] .= ' (A-Turnier)';
			elseif ($event['series_path'] == 'odem-u25-b') $event['untertitel'] .= ' (B-Turnier)';
		}
		break;
	case 'dsm':
	case 'dvm':
		$event['vereinsprefix'] = 'mit ';
		$event['titel'] = explode(' ', $event['series']);
		if ($event['main_series'] === 'dsm') {
			$event['untertitel'] = 'Wettkampfklasse '.array_pop($event['titel']);
			array_pop($event['titel']);
		} else {
			$event['untertitel'] = 'Altersklasse '.array_pop($event['titel']);
		}
		$event['titel'] = implode(' ', $event['titel']).' '.$event['year'];
		$event['titel_dativ'] = str_replace('Deutsche', 'Deutschen', $event['titel']);
		break;
	case 'dlm':
		$event['vereinsprefix'] = 'mit ';
		$event['titel'] = $event['series'].' '.$event['year'];
		$event['obertitel'] = '';
		$event['untertitel'] = '';
		break;
	default:
		$event['titel'] = $event['series'].' '.$event['year'];
		$event['obertitel'] = '';
		$event['untertitel'] = '';
		break;
	}

	// Teams?
	if ($event['event_category'] === 'mannschaft') {
		$sql = 'SELECT teams.team_id
				, CONCAT(team, IFNULL(CONCAT(" ", team_no), "")) AS spieler
				, (SELECT
					GROUP_CONCAT(CONCAT(t_vorname, " ", IFNULL(CONCAT(t_namenszusatz, " "), ""), t_nachname) ORDER BY brett_no SEPARATOR ", ") AS spieler
					FROM participations
					WHERE participations.team_id = teams.team_id
					AND NOT ISNULL(brett_no)) AS verein
				, tabellenstaende.platz_no
				, tabellenstaende.platz_no AS rang
			FROM teams
			LEFT JOIN tabellenstaende
				ON tabellenstaende.team_id = teams.team_id
				AND tabellenstaende.runde_no = %d
			WHERE teams.event_id = %d
			ORDER BY platz_no, team, team_no';
		$sql = sprintf($sql, $event['runden'], $event['event_id']);
		$data = wrap_db_fetch($sql, 'team_id');
		// @todo $where
		// @todo ORDER BY
	} else {
		// Spieler
		$sql = 'SELECT participations.person_id
				, CONCAT(participations.t_vorname, " ", IFNULL(CONCAT(participations.t_namenszusatz, " "), ""), participations.t_nachname) AS spieler
				, CONCAT(participations.t_vorname, " ", IFNULL(CONCAT(participations.t_namenszusatz, " "), "")) AS vorname
				, participations.t_nachname AS nachname
				, t_verein AS verein
				, urkundentext
				, tabellenstaende.platz_no
			FROM participations
			LEFT JOIN persons USING (person_id)
			LEFT JOIN tabellenstaende
				ON tabellenstaende.person_id = participations.person_id
				AND tabellenstaende.event_id = participations.event_id
				AND tabellenstaende.runde_no = %d
			WHERE participations.event_id = %d AND usergroup_id = %d
			AND NOT ISNULL(participations.person_id)
			%s
			%s
		';
		$sql = sprintf($sql, $event['runden']
			, $event['event_id']
			, wrap_id('usergroups', 'spieler')
			, $where ? ' AND '.implode(' AND ', $where) : ''
			, $order_by_limit
		);
		$data = wrap_db_fetch($sql, 'person_id');
	}
	foreach ($data as $id => $line) {
		$data[$id]['verein'] = $event['vereinsprefix'].$line['verein'];
		switch ($type) {
		case 'teilnahme':
			$data[$id]['textzeile'] = $event['urkundentext'];
			break;
		case 'spezial':
			$data[$id]['textzeile'] = $line['urkundentext'];
			break;
		} 
	}

	if ($event['event_category'] === 'einzel') {
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
	require_once __DIR__.'/urkunden/'.$event['urkunde_kennung'].'.inc.php';
	
	$pdf = new TFPDF('P', 'pt', 'A4');		// panorama = p, DIN A4, 595 x 842
	$pdf->setCompression(true);
	$pdf = cms_urkunde_out($pdf, $event, $data, $vorlagen, $type);

	$folder = $zz_setting['tmp_dir'].'/urkunden/'.$event['identifier'];
	wrap_mkdir($folder);
	if (file_exists($folder.'/urkunde-'.$type.'.pdf')) {
		unlink($folder.'/urkunde-'.$type.'.pdf');
	}
	$file['name'] = $folder.'/urkunde-'.$type.'.pdf';
	$file['send_as'] = $event['year'].' '.$event['series_short'].' Urkunden '.ucfirst($type).'.pdf';
	$file['etag_generate_md5'] = true;

	$pdf->output('F', $file['name'], true);
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
