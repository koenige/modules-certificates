<?php

function cms_urkunde_out($pdf, $turnier, $data, $vorlagen, $type) {

	foreach ($data as $line) {
		$pdf->addPage();
		$pdf->image($vorlagen.'/KIKA-Ritter.png', 40, 40, 515, 497);

	// Turniername
		$pdf->SetXY(10, 540);
		$pdf->setFont($turnier['font_regular'], '', 24);
		$pdf->Cell(575, 24, $turnier['titel'], 0, 2, 'C');
		// @todo untertitel?

	// Spielername
		$line['verein'] = cms_urkunde_zeile_anpassen($line['verein'], 38, 22);

		$abstand_links = 95;
		$abstand_oben = $pdf->getY() + 20;
		$schriftgrad = 30;

		$pdf->setFont($turnier['font_bold'], '', $schriftgrad);
		if (strlen($line['spieler']) > 34 AND !empty($line['vorname'])) {
			// Sonderfall 2009, geht nur, wenn Verein nur einzeilig ist!
			if (strlen($line['vorname']) > 34) {
				$vornamen = explode(' ', $line['vorname']);
				$line['vorname'] = substr($line['vorname'], strrpos($line['vorname'], ' '));
			}
			$pdf->SetXY($abstand_links, $abstand_oben - $schriftgrad);
			if (!empty($vornamen[0])) 
				$pdf->Cell(405, 28, $vornamen[0], 0, 2, 'C'); 
			$pdf->Cell(405, 28, $line['vorname'], 0, 2, 'C'); 
			$pdf->Cell(405, 28, $line['nachname'], 0, 1, 'C');
		} else {
			$pdf->SetXY($abstand_links, $abstand_oben);
			$pdf->Cell(405, 28, $line['spieler'], 0, 1, 'C'); 
		}

	// Platzierung/mit Erfolg teilgenommen
		$pdf->setFont($turnier['font_regular'], '', 24);
		$pdf->Cell(595, 32, 'hat den', 0, 2, 'C');
		$pdf->setFont($turnier['font_bold'], '', 30);
		$pdf->SetX(230);
		$pdf->Cell(145, 34, $line['rang'].'. Platz', 0, 1, $line['rang'] ? 'C' : 'R'); 
		$pdf->setFont($turnier['font_regular'], '', 24);
		if (!empty($line['urkundentext'])) {
			$pdf->Cell(595, 28, 'belegt '.$line['urkundentext'], 0, 2, 'C'); 
		} else {
			$pdf->Cell(595, 28, 'belegt', 0, 2, 'C'); 
		}

	// Fuß
		$rechter_rand = 0;
		$pdf->setFont($turnier['font_regular'], '', 14);
		$pdf->text($rechter_rand + 220, 740, $turnier['place'].', '.$turnier['date_of_certificate']); 
		$pdf->text($rechter_rand + 160, 800, $turnier['signature_left']); 
		$pdf->text($rechter_rand + 320, 800, $turnier['signature_right']); 
	}
	return $pdf;
}
