<?php

function cms_urkunde_out($pdf, $turnier, $line, $vorlagen, $type) {

	$pdf->image($vorlagen.'/KIKA-Urkunde-2013.png', 0, 0, 595, 842);

// Turniername
	$pdf->SetXY(10, 456);
	$pdf->setFont($turnier['font_regular'], '', 24);
	$pdf->Cell(575, 24, $turnier['obertitel'], 0, 2, 'C');
	$pdf->Cell(575, 24, $turnier['titel'], 0, 2, 'C');
	$pdf->setFont($turnier['font_bold'], '', 18);
	$pdf->Cell(575, 24, $turnier['untertitel'], 0, 2, 'C'); 

// Spielername
	$line['verein'] = mf_certificates_balance_text($line['verein'], 38, 22);
	$abstand_links = 95;
	$abstand_oben = $pdf->getY() + 14;
	$schriftgrad = 30;

	$pdf->setFont($turnier['font_bold'], '', $schriftgrad);
	if (strlen($line['spieler']) > 34 AND !empty($line['vorname'])) {
		// Sonderfall 2009, geht nur, wenn Verein nur einzeilig ist!
		if (strlen($line['vorname']) > 34) {
			$vornamen = explode(' ', $line['vorname']);
			$line['vorname'] = substr($line['vorname'], strrpos($line['vorname'], ' '));
		} elseif (count($line['verein']) == 1) {
			$abstand_oben += 9;
		}
		$pdf->SetXY($abstand_links, $abstand_oben - $schriftgrad);
		if (!empty($vornamen[0])) 
			$pdf->Cell(405, 28, $vornamen[0], 0, 2, 'C'); 
		$pdf->Cell(405, 28, $line['vorname'], 0, 2, 'C'); 
		$pdf->Cell(405, 28, $line['nachname'], 0, 0, 'C');
	} else {
		if (count($line['verein']) > 1) {
			$abstand_oben -= 9;
		}
		$pdf->SetXY($abstand_links, $abstand_oben);
		
		$line['spieler'] = mf_certificates_balance_text($line['spieler'], 40, 36);
		foreach ($line['spieler'] as $spieler) {
			$pdf->Cell(405, 28, $spieler, 0, 2, 'C');
		}
	}

// Platzierung/mit Erfolg teilgenommen
	$pdf->SetXY(220, 570);
	$pdf->setFont($turnier['font_regular'], '', 24);
	$pdf->Cell(145, 28, 'hat den', 0, 2, 'C');
	$pdf->setFont($turnier['font_bold'], '', 30);
	$pdf->Cell(145, 34, $line['rang'].'. Platz', 0, 2, $line['rang'] ? 'C' : 'R'); 
	$pdf->setFont($turnier['font_regular'], '', 24);
	$pdf->Cell(145, 28, 'belegt', 0, 2, 'C'); 

// Fuß
	$pdf->setFont($turnier['font_regular'], '', 14);
	$pdf->text(220, 720, $turnier['place'].', '.$turnier['date_of_certificate']); 
	$pdf->text(160, 800, $turnier['signature_left']); 
	$pdf->text(320, 800, $turnier['signature_right']); 

	return $pdf;
}
