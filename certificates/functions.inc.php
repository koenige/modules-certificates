<?php 

/**
 * certificates module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023, 2025 Gustaf Mossakowski
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
 * position a text
 *
 * @param object $pdf
 * @param array $element
 * @param array $event
 * @param string $text
 */
function mf_certificates_text(&$pdf, $element, $event, $text) {
	$element = mf_certificates_position($pdf, $element);
	
	$font_size = mf_certificates_val($element['font-size']
		?? wrap_setting('certificates_font_size'));
	$pdf->setFont($event['font_regular'], '', $font_size);

	$left = mf_certificates_val($element['left']);
	$top = mf_certificates_val($element['top']);
	$pdf->SetXY($left, $top);

	$width = mf_certificates_val($element['width']);
	$height = mf_certificates_val($element['height'])
		?? round($font_size * wrap_setting('certificates_line_height'));
	$align = mf_certificates_align($element['text-align']) ?? ''; // standard is left
	$pdf->Cell($width, $height, $text, 0, 2, $align); 
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

