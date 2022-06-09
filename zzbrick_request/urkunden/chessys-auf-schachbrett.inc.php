<?php

function cms_urkunde_out($pdf, $turnier, $line, $vorlagen, $type) {

	$pdf->image($vorlagen.'/122-dem2003-Urkunde.jpg', 20, 25, 542, 295);
	$pdf->setFont($turnier['font_regular'], '', 90);
	$pdf->SetXY(10, 340);
	$pdf->Cell(575, 85, 'Urkunde', 0, 0, 'C');

// Turniername
	$pdf->SetXY(10, 425);
	$pdf->setFont($turnier['font_regular'], '', 20);
	$pdf->Cell(575, 22, $turnier['obertitel'], 0, 2, 'C');
	$pdf->Cell(575, 22, $turnier['titel'], 0, 2, 'C');
	$pdf->Cell(575, 22, $turnier['untertitel'], 0, 2, 'C'); 

// Spielername
	$line['verein'] = cms_urkunde_zeile_anpassen($line['verein'], 44, 36);
	$abstand_links = 95;
	$abstand_oben = $pdf->getY() + 22;
	$schriftgrad = 32;

	$pdf->setTextColor(0, 102, 204);   // Chessyblau
	$pdf->setFont($turnier['font_regular'], '', $schriftgrad);
	if (strlen($line['spieler']) > 33 AND !empty($line['vorname'])) {
		// Sonderfall 2009, geht nur, wenn Verein nur einzeilig ist!
		if (strlen($line['vorname']) > 33) {
			$vornamen = explode(' ', $line['vorname']);
			$line['vorname'] = substr($line['vorname'], strrpos($line['vorname'], ' '));
		} elseif (count($line['verein']) == 1) {
			$abstand_oben += 9;
		}
		$pdf->SetXY($abstand_links, $abstand_oben - $schriftgrad);
		if (!empty($vornamen[0])) 
			$pdf->Cell(405, 36, $vornamen[0], 0, 2, 'C'); 
		$pdf->Cell(405, 36, $line['vorname'], 0, 2, 'C'); 
		$pdf->Cell(405, 36, $line['nachname'], 0, 0, 'C');
	} else {
		if (count($line['verein']) > 1) {
			$abstand_oben -= 9;
		}
		$pdf->SetXY($abstand_links, $abstand_oben);
		
		$line['spieler'] = cms_urkunde_zeile_anpassen($line['spieler'], 33, 30);
		foreach ($line['spieler'] as $spieler) {
			$pdf->Cell(405, 36, $spieler, 0, 2, 'C');
		}
	}

// Vereinsname
	$pdf->setFont($turnier['font_regular'], '', 18);
	$pdf->SetXY($abstand_links, $pdf->getY() + 8);
	foreach ($line['verein'] as $vereinteil) {
		$pdf->Cell(405, 20, $vereinteil, 0, 2, 'C');
	}

// Platzierung/mit Erfolg teilgenommen
	$pdf->setTextColor(0, 0, 0);   // Schwarz
	if ($type === 'platz') {
		$pdf->SetX(158);
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(90, 44, 'hat den', 0, 0, 'R');
		$pdf->setFont($turnier['font_regular'], '', 24);
		$pdf->Cell(110, 42, $line['rang'].'. Platz', 0, 0, $line['rang'] ? 'C' : 'R');
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(90, 44, 'belegt', 0, 2, 'L'); 
	} else {
		$pdf->SetX(220);
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(145, 44, $line['textzeile'], 0, 0, 'C'); 
	}

// Fuß
	$pdf->SetXY(0, 690);
	$pdf->setFont($turnier['font_regular'], '', 14);
	$pdf->Cell(0, 14, $turnier['place'].', '.$turnier['date_of_certificate'], 0, 0, 'C'); 
	$pdf->text(110, 795, $turnier['signature_left']); 
	$pdf->text(410, 795, $turnier['signature_right']);

	return $pdf;
}
