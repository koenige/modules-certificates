<?php 

/**
 * certificates module
 * create PDF for certificates for print
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2008, 2012, 2014-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * print certificates for an event
 *
 * @param array $params
 *		[0]: year
 *		[1]: event identifier
 *		[2]: type 'teilnahme.pdf', 'spezial.pdf', 'platz.pdf', 'platz-w.pdf' etc.
 * @param array $settings
 * @param array $event
 * @return array $page
 */
function mod_certificates_certificate($params, $settings = [], $event = []) {
	if (!$event) return false;
	if (count($params) !== 3) return false;

	// Turnier
	// @todo ggf. Urkundenstandardtext überschreibbar machen
	$sql = 'SELECT runden
			, place, date_of_certificate
			, signature_left, signature_right
			, certificates.identifier AS urkunde_kennung
			, series.category AS series
			, tournaments.tabellenstaende, alter_max AS age_max
			, IF(tournaments.geschlecht = "w", 1, NULL) AS weiblich
			, certificate_id
			, certificates.parameters AS certificate_parameters
			, /*_PREFIX_*/media.filename, /*_PREFIX_*/media.version
			, o_mime.extension AS extension
		FROM events
		LEFT JOIN events_certificates USING (event_id)
		LEFT JOIN certificates USING (certificate_id)
		LEFT JOIN tournaments USING (event_id)
		LEFT JOIN categories series
			ON events.series_category_id = series.category_id
		LEFT JOIN /*_PREFIX_*/media
			ON /*_PREFIX_*/media.medium_id = events_certificates.logo_medium_id
		LEFT JOIN /*_PREFIX_*/filetypes AS o_mime USING (filetype_id)
		LEFT JOIN /*_PREFIX_*/filetypes AS t_mime 
			ON /*_PREFIX_*/media.thumb_filetype_id = t_mime.filetype_id
		WHERE events.identifier = "%d/%s"';
	$sql = sprintf($sql, $params[0], wrap_db_escape($params[1]));
	$event += wrap_db_fetch($sql);

	if (empty($event['urkunde_kennung'])) {
		$page['title'] = $event['event'].' '.$event['year'];
		$page['breadcrumbs'][]['title'] = 'Urkunde';
		if ($path = wrap_path('certificates_event_edit', [$params[0].'/'.$params[1]])) {
			$page['text'] = sprintf('<p class="error">Bitte wähle erst <a href="%s">eine Urkunde aus!</a></p>', $path);
		} else {
			$page['text'] = '<p class="error">Es ist noch keine Urkunde ausgewählt. Bitte die Verantwortlichen, eine auszuwählen.</p>';
		}
		return $page;
	}
	if ($event['certificate_parameters'])
		parse_str($event['certificate_parameters'], $event['p']);
	if ($event['series_parameter']) {
		parse_str($event['series_parameter'], $event['series_parameter']);
		wrap_match_module_parameters('series', $event['series_parameter'], false);
	}
	if ($event['tournament_parameter']) {
		parse_str($event['tournament_parameter'], $parameter);
		unset($event['tournament_parameter']);
		$event = array_merge($parameter, $event);
	}

	$sql = 'SELECT certificateelement_id
			, categories.category
			, media.filename, o_mime.extension
			, certificateelements.parameters
			, categories.parameters AS category_parameters
	    FROM certificateelements
	    LEFT JOIN categories
	    	ON certificateelements.element_category_id = categories.category_id
	    LEFT JOIN media
	    	ON certificateelements.element_medium_id = media.medium_id
		LEFT JOIN filetypes AS o_mime USING (filetype_id)
	    WHERE certificate_id = %d
	    ORDER BY categories.sequence';
	$sql = sprintf($sql, $event['certificate_id']);
	$event['elements'] = wrap_db_fetch($sql, 'certificateelement_id');
	$param_fields = ['parameters','category_parameters'];
	foreach ($event['elements'] as $id => $element) {
		foreach ($param_fields as $param_field) {
			if (!$element[$param_field]) continue;
			parse_str($element[$param_field], $element_params);
			$event['elements'][$id] = array_merge($event['elements'][$id], $element_params);
		}
	}

	$event['urkundentext'] = 'hat mit Erfolg teilgenommen';
	$event['date_of_certificate'] = ltrim(wrap_date_plain($event['date_of_certificate'], 'dates-de-long'), '0');

	// Urkundentyp
	$type = $params[2];
	$possible_types = ['teilnahme', 'spezial', 'platz'];
	if ($event['tabellenstaende']) {
		// @todo currently, only 'w' for female is supported
		$tabellenstaende = explode(',', $event['tabellenstaende']);
		foreach ($tabellenstaende as $tabellenstand) {
			if (!$tabellenstand) continue;
			$possible_types[] = 'platz-'.$tabellenstand;
		}
	}
	if (!in_array($type, $possible_types)) return false;
	$where = [];
	$filter_kennung = '';
	switch ($type) {
		case 'spezial':
			$where[] = 'urkundentext IS NOT NULL';
			break;
		case 'platz-w':
			$filter_kennung = 'w';
			if (wrap_setting('certificates_placement_count_female'))
				wrap_setting('certificates_placement_count', wrap_setting('certificates_placement_count_female'));
			$event['weiblich'] = true;
			$type = 'platz';
			break;
	}
	if ($type === 'platz') {
		$order_by_limit = 'ORDER BY rank_no, t_nachname, t_vorname
			LIMIT /*_SETTING certificates_placement_count _*/; ';
	} else {
		$order_by_limit = 'ORDER BY t_nachname, t_vorname, contact_id';
	}
	$filter = mf_tournaments_standings_filter($filter_kennung);
	if ($filter['error']) return false;
	$where = array_merge($where, $filter['where']);

	// Teams?
	if (wrap_setting('tournaments_type_team')) {
		$sql = 'SELECT teams.team_id
				, CONCAT(team, IFNULL(CONCAT(" ", team_no), "")) AS spieler
				, (SELECT
					GROUP_CONCAT(CONCAT(t_vorname, " ", IFNULL(CONCAT(t_namenszusatz, " "), ""), t_nachname) ORDER BY brett_no SEPARATOR ", ") AS spieler
					FROM participations
					WHERE participations.team_id = teams.team_id
					AND NOT ISNULL(brett_no)) AS verein
				, standings.rank_no
				, standings.rank_no AS rang
			FROM teams
			LEFT JOIN standings
				ON standings.team_id = teams.team_id
				AND standings.runde_no = %d
			WHERE teams.event_id = %d
			ORDER BY rank_no, team, team_no';
		$sql = sprintf($sql, $event['runden'], $event['event_id']);
		$data = wrap_db_fetch($sql, 'team_id');
		// @todo $where
		// @todo ORDER BY
	} else {
		// Spieler
		$sql = 'SELECT persons.person_id
				, CONCAT(participations.t_vorname, " ", IFNULL(CONCAT(participations.t_namenszusatz, " "), ""), participations.t_nachname) AS spieler
				, CONCAT(participations.t_vorname, " ", IFNULL(CONCAT(participations.t_namenszusatz, " "), "")) AS vorname
				, participations.t_nachname AS nachname
				, t_verein AS verein
				, urkundentext
				, standings.rank_no
			FROM participations
			LEFT JOIN persons USING (contact_id)
			LEFT JOIN standings
				ON standings.person_id = persons.person_id
				AND standings.event_id = participations.event_id
				AND standings.runde_no = %d
			WHERE participations.event_id = %d AND usergroup_id = /*_ID usergroups spieler _*/
			AND NOT ISNULL(participations.contact_id)
			AND participations.status_category_id = /*_ID categories participation-status/participant _*/
			%s
			%s
		';
		$sql = sprintf($sql, $event['runden']
			, $event['event_id']
			, $where ? ' AND '.implode(' AND ', $where) : ''
			, $order_by_limit
		);
		$data = wrap_db_fetch($sql, 'person_id');
	}
	$event['organisation_prefix'] = wrap_setting('tournaments_type_team') ? 'mit ' : '';
	foreach ($data as $id => $line) {
		$data[$id]['verein'] = $event['organisation_prefix'].$line['verein'];
		switch ($type) {
		case 'teilnahme':
			$data[$id]['textzeile'] = $event['urkundentext'];
			break;
		case 'spezial':
			$data[$id]['textzeile'] = $line['urkundentext'];
			break;
		} 
	}
	
	if (wrap_setting('tournaments_type_single')) {
		$i = 1;
		foreach ($data as $person_id => $person) {
			if ($type === 'platz' AND !empty($filter['kennung'])) {
				$data[$person_id]['rang'] = $i;
				$i++;
			} else {
				$data[$person_id]['rang'] = $person['rank_no'];
			}
		}
	}

	wrap_lib('tfpdf');
	require_once __DIR__.'/urkunden/'.$event['urkunde_kennung'].'.inc.php';
	
	if (!empty($event['p']['memory_limit'])) {
		if (wrap_return_bytes(ini_get('memory_limit')) < wrap_return_bytes($event['p']['memory_limit']))
			ini_set('memory_limit', $event['p']['memory_limit']);
	}
	$pdf = new TFPDF('P', 'pt', 'A4');		// panorama = p, DIN A4, 595 x 842
	$pdf->setCompression(true);
	$pdf->setMargins(0,0);
	if (!empty($event['p']['font_file'])) {
		foreach ($event['p']['font_file'] as $typeface => $font_file) {
			$font_path = pathinfo($font_file);
			$pdf->AddFont($font_path['filename'], '', $font_file, true);
			$event['font_'.$typeface] = $font_path['filename'];
		}
	}
	foreach ($data as $line) {
		$pdf->addPage();
		foreach ($event['elements'] as $element) {
			switch ($element['type']) {
			case 'image':
				mf_certificates_image($pdf, $element);
				break;
			case 'logo':
				if (!$event['filename']) break;
				$element['filename'] = $event['filename'];
				$element['extension'] = $event['extension'];
				mf_certificates_image($pdf, $element);
				break;
			case 'event':
				mf_certificates_event($pdf, $element, $event);
				break;
			case 'textblock':
				if (empty($element['text'])) break;
				mf_certificates_text($pdf, $element, $event, $element['text']);
				break;
			}
		}
		$pdf = cms_urkunde_out($pdf, $event, $line, $type);
		$pdf->SetAutoPageBreak(false);
		foreach ($event['elements'] as $element) {
			switch ($element['type']) {
			case 'place-date':
				$text = sprintf('%s, %s', $event['place'], $event['date_of_certificate']);
				mf_certificates_text($pdf, $element, $event, $text);
				break;
			case 'signature-left':
				mf_certificates_text($pdf, $element, $event, $event['signature_left']);
				break;
			case 'signature-right':
				mf_certificates_text($pdf, $element, $event, $event['signature_right']);
				break;
			}
		}
		$pdf->SetAutoPageBreak(true);
	}

	$folder = wrap_setting('tmp_dir').'/urkunden/'.$event['identifier'];
	wrap_mkdir($folder);
	if (file_exists($folder.'/urkunde-'.$type.'.pdf')) {
		unlink($folder.'/urkunde-'.$type.'.pdf');
	}
	$file['name'] = $folder.'/urkunde-'.$type.'.pdf';
	$file['send_as'] = $event['year'].' '.$event['series_short'].' Urkunden '.ucfirst($type).'.pdf';
	$file['etag_generate_md5'] = true;

	$pdf->output('F', $file['name'], true);
	wrap_send_file($file);
}
