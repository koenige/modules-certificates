<?php 

/**
 * certificates module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023, 2025-2026 Gustaf Mossakowski
 * @license http://opensource.org/licenses/lgpl-3.0.html LGPL-3.0
 */


/**
 * position an image
 *
 * @param object $pdf
 * @param array $element
 */
function mf_certificates_image(&$pdf, $element) {
	$element['filename'] = wrap_setting('media_folder').'/'.$element['filename'].'.master.'.$element['extension'];
	$element = mf_certificates_imagesize($element);
	$element = mf_certificates_position($pdf, $element);
	$pdf->image($element['filename'], $element['pos_x'], $element['pos_y'], $element['width'], $element['height']);
	return;
}

/**
 * get width and height for image depending on ratio of actual image
 *
 * @param array $element
 * @return array
 */
function mf_certificates_imagesize($element) {
	$size = getimagesize($element['filename']);
	$image_ratio = $size[0] / $size[1];
	$element['width'] = mf_certificates_val($element['width']);
	$element['height'] = mf_certificates_val($element['height']);
	if ($image_ratio < 1) {
		$element['width'] = round($element['height'] * $image_ratio, 2);
		$element['height'] = $element['height'];
	} else {
		$element['width'] = $element['width'];
		$element['height'] = round($element['width'] / $image_ratio, 2);
	}
	return $element;
}

/**
 * get pos_x, pos_y for element
 *
 *	// center = 50%
 *	// bottom = 44
 *	// width = 98
 *	// height = 98
 * @param object $pdf
 * @param array $element
 *		width, height
 *		center, left, right, top, bottom, middle
 * @return array
 */
function mf_certificates_position($pdf, $element) {
	$page_width = $pdf->GetPageWidth();
	$page_height = $pdf->GetPageHeight();
	
	// x: left, right, center
	if (isset($element['center'])) {
		if (str_ends_with($element['center'], '%')) {
			$element['pos_x'] = $page_width * (substr($element['center'], 0, -1) / 100) - .5 * $element['width'];
		} else {
			$element['center'] = mf_certificates_val($element['center']);
			$element['pos_x'] = $element['center'] - .5 * $element['width'];
		}
	} elseif (isset($element['left'])) {
		$element['left'] = mf_certificates_val($element['left']);
		$element['pos_x'] = $element['left'];
	} elseif (isset($element['right'])) {
		$element['right'] = mf_certificates_val($element['right']);
		$element['pos_x'] = $page_width - $element['right'] - $element['width'];
	}
	
	// y: top, bottom
	if (isset($element['middle'])) {
		if (str_ends_with($element['middle'], '%')) {
			$element['pos_y'] = $page_height * (substr($element['middle'], 0, -1) / 100) - .5 * $element['height'];
		} else {
			$element['middle'] = mf_certificates_val($element['middle']);
			$element['pos_y'] = $element['middle'] - .5 * $element['height'];
		}
	} elseif (isset($element['top'])) {
		$element['top'] = mf_certificates_val($element['top']);
		$element['pos_y'] = $element['top'];
	} elseif (isset($element['bottom'])) {
		$element['bottom'] = mf_certificates_val($element['bottom']);
		$element['pos_y'] = $page_height - $element['bottom'] - $element['height'];
	}
	return $element;
}

/**
 * render certificate supertitle, title, and subtitle as a stacked block
 *
 * @param object $pdf
 * @param array $element parameters: top, left, center, width, text-align, font-size,
 *		font-weight, font-size-subtitle, font-weight-subtitle, line-height,
 *		balance-title-max, balance-title-row
 * @param array $event
 */
function mf_certificates_event(&$pdf, $element, $event) {
	$element['width'] = mf_certificates_element_width($pdf, $element);
	$element['height'] = 1;
	$element = mf_certificates_position($pdf, $element);
	$pdf->SetXY($element['pos_x'], $element['pos_y']);

	$align = mf_certificates_align($element['text-align'] ?? 'center');
	$line_height = isset($element['line-height']) ? (float) $element['line-height'] : 1;

	$font_size = mf_certificates_val($element['font-size']
		?? wrap_setting('certificates_font_size'));
	$font_size_subtitle = mf_certificates_val($element['font-size-subtitle'] ?? $font_size);
	$font_weight = $element['font-weight'] ?? 'bold';
	$font_weight_subtitle = $element['font-weight-subtitle'] ?? 'normal';

	$lines = [];
	$lines[] = ['text' => mf_certificates_event_super(), 'size' => $font_size, 'weight' => $font_weight];
	if ($event_title = mf_certificates_event_title($event, $event['series_parameter'])) {
		if (!empty($element['balance-title-max'])) {
			$title_row = $element['balance-title-row'] ?? $element['balance-title-max'];
			$title_parts = mf_certificates_balance_text($event_title
				, (int) $element['balance-title-max']
				, (int) $title_row);
			foreach ($title_parts as $title_part) {
				$lines[] = ['text' => $title_part, 'size' => $font_size, 'weight' => $font_weight];
			}
		} else {
			$lines[] = ['text' => $event_title, 'size' => $font_size, 'weight' => $font_weight];
		}
	}
	$lines[] = ['text' => mf_certificates_event_sub($event, $event['series_parameter']), 'size' => $font_size_subtitle, 'weight' => $font_weight_subtitle];
	foreach ($lines as $line) {
		$pdf->setFont(mf_certificates_event_font($event, $line['weight']), '', $line['size']);
		$cell_height = round($line['size'] * $line_height);
		$pdf->Cell($element['width'], $cell_height, $line['text'], 0, 2, $align);
	}
}

function mf_certificates_event_font($event, $font_weight) {
	if ($font_weight === 'bold' && !empty($event['font_bold'])) {
		return $event['font_bold'];
	}
	return $event['font_regular'];
}

/**
 * certificate supertitle from series parameters and tournament edition
 *
 * Uses `certificates_supertitle` with `{tournaments_edition}` replaced by the edition number.
 * If no supertitle is configured but `tournaments_edition` is set, uses `{tournaments_edition}.`
 * Without an edition, `{tournaments_edition}` and an optional following dot are removed from the template.
 *
 * @return string supertitle text, or empty string
 */
function mf_certificates_event_super() {
	$edition = wrap_setting('tournaments_edition');
	$template = wrap_setting('certificates_supertitle');
	if (!$template AND !$edition) return '';
	if (!$template)
		$template = '{tournaments_edition}.';
	if ($edition)
		return str_replace('{tournaments_edition}', $edition, $template);
	$supertitle = preg_replace('/\s*\{tournaments_edition\}\.?/', '', $template);
	return trim(preg_replace('/\s+/', ' ', $supertitle));
}

/**
 * certificate main title from series parameters
 *
 * Uses `certificates_title` from the series category parameters. Falls back to the series
 * category name. Appends the event year when `tournaments_edition` is not set.
 *
 * @param array $event event data with `series`, `year`
 * @param array $series parsed series parameters
 * @return string title text
 */
function mf_certificates_event_title($event, $series) {
	if (!empty($series['certificates_title'])) {
		$title = $series['certificates_title'];
	} elseif (!empty($event['series'])) {
		$title = $event['series'];
	} else {
		return '';
	}
	if (!wrap_setting('tournaments_edition')) {
		$title .= ' '.$event['year'];
	}
	return $title;
}

/**
 * certificate subtitle from series parameters
 *
 * Reads `certificates_subtitle` or, for female standings, `certificates_subtitle_female`
 * from the series category parameters. Replaces `{age_max}` with the tournament age limit.
 *
 * @param array $event event data with optional `weiblich`, `age_max`
 * @param array $series parsed series parameters
 * @return string subtitle text, or empty string if not configured
 */
function mf_certificates_event_sub($event, $series) {
	if (!empty($event['weiblich']) && !empty($series['certificates_subtitle_female'])) {
		$subtitle = $series['certificates_subtitle_female'];
	} elseif (!empty($series['certificates_subtitle'])) {
		$subtitle = $series['certificates_subtitle'];
	} else {
		return '';
	}
	if ($event['age_max']) {
		$subtitle = str_replace('{age_max}', $event['age_max'], $subtitle);
	}
	return $subtitle;
}


/**
 * position a text
 *
 * @param object $pdf
 * @param array $element
 * @param array $event
 * @param string $text
 */
function mf_certificates_text(&$pdf, $element, $event, $text) {
	$font_size = mf_certificates_val($element['font-size']
		?? wrap_setting('certificates_font_size'));
	$pdf->setFont($event['font_regular'], '', $font_size);

	$element['width'] = mf_certificates_element_width($pdf, $element);
	if (!isset($element['height'])) {
		$element['height'] = round($font_size * wrap_setting('certificates_line_height'));
	} else {
		$element['height'] = mf_certificates_val($element['height']);
	}
	$element = mf_certificates_position($pdf, $element);
	
	$left = mf_certificates_val($element['left']);
	$top = mf_certificates_val($element['top']);
	$pdf->SetXY($left, $top);

	$align = mf_certificates_align($element['text-align']) ?? ''; // standard is left
	$pdf->Cell($element['width'], $element['height'], $text, 0, 2, $align); 
}

/**
 * derive element width from explicit width or page margins
 *
 * @param object $pdf
 * @param array $element parameters: width, left, right
 * @return int
 */
function mf_certificates_element_width($pdf, $element) {
	if (!empty($element['width']))
		return mf_certificates_val($element['width']);
	$page_width = $pdf->GetPageWidth();
	if (!empty($element['left']) && !empty($element['right'])) {
		$left = mf_certificates_val($element['left']);
		$right = mf_certificates_val($element['right']);
		return $page_width - $left - $right;
	}
	if (!empty($element['left'])) {
		$left = mf_certificates_val($element['left']);
		return $page_width - 2 * $left;
	}
	return $page_width;
}

/**
 * Balance long text in two lines
 *
 * @param string $verein
 * @param int $max_len
 * @param int $len_per_row
 * @return array
 */
function mf_certificates_balance_text($text, $max_len, $len_per_row) {
	if (strlen($text) < $max_len) return [$text];
	$concat = strstr($text, ', ') ? ', ' : ' ';
	$parts = explode($concat, $text);
	$text = [0 => ''];
	$i = 0;
	foreach ($parts as $part) {
		if (strlen($text[$i].$part) > $len_per_row) $i++;
		if (!empty($text[$i]))
			$text[$i] .= $concat;
		else 
			$text[$i] = '';
		if (strlen($part) >= $len_per_row AND strstr($part, '-')) {
			$part = explode('-', $part);
			foreach ($part as $index => $sub_part) {
				if (strlen($text[$i].$sub_part) >= $len_per_row) {
					$i++;
					$text[$i] = '';
				}
				$text[$i] .= $sub_part;
				if ($index < count($part) - 1)
					$text[$i] .= '-';
			}
		} else {
			$text[$i] .= $part;
		}
	}
	if (empty($text[0])) array_shift($text); // if first string too long
	return $text;
}

/**
 * convert values into pt
 *
 * @param string $value
 * @return int
 */
function mf_certificates_val($value) {
	if (str_ends_with($value, 'pt')) $value = substr($value, 0, -2);
	return intval($value);
}

/**
 * return value for text alignment
 *
 * @param string $value
 * @return string
 */
function mf_certificates_align($value) {
	switch ($value) {
		case 'center': return 'C';
		case 'right': return 'R';
		default: case 'left': return 'L';
	}
}

/**
 * element type from category alias (last path segment)
 *
 * @param array $element
 * @return string
 */
function mf_certificates_element_type($element) {
	$path = $element['alias'] ?? $element['path'];
	$pos = strrpos($path, '/');
	if ($pos === false) return '';
	return substr($path, $pos + 1);
}

