<?php 

/**
 * certificates module
 * common functions
 *
 * Part of »Zugzwang Project«
 * https://www.zugzwang.org/modules/certificates
 *
 * @author Gustaf Mossakowski <gustaf@koenige.org>
 * @copyright Copyright © 2022-2023 Gustaf Mossakowski
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
	if ($image_ratio < 1) {
		$element['width'] = round($element['width'] * $image_ratio, 2);
		$element['height'] = $element['height'];
	} else {
		$element['width'] = $element['width'];
		$element['height'] = round($element['height'] / $image_ratio, 2);
	}
	return $element;
}

/**
 * get x, y, width and height for element
 *
 *	// center = 50%
 *	// bottom = 44
 *	// width = 98
 *	// height = 98
 * @param array $element
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
			$element['pos_x'] = $element['center'] - .5 * $element['width'];
		}
	} elseif (isset($element['left'])) {
		$element['pos_x'] = $element['left'];
	} elseif (isset($element['right'])) {
		$element['pos_x'] = $page_width - $element['right'] - $element['width'];
	}
	
	// y: top, bottom
	if (isset($element['top'])) {
		$element['pos_y'] = $element['top'];
	} elseif (isset($element['bottom'])) {
		$element['pos_y'] = $page_height - $element['bottom'] - $element['height'];
	}
	return $element;
}
