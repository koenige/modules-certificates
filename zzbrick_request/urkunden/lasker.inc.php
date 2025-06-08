<?php

function cms_urkunde_out($pdf, $turnier, $line, $vorlagen, $type) {

	$pdf->image($vorlagen.'/Urkunde Lasker.jpg', 30, 50, 535, 350);

// Turniername
	$pdf->SetXY(10, 430);
	$pdf->setFont($turnier['font_bold'], '', 18);
	$pdf->Cell(575, 20, $turnier['obertitel'], 0, 2, 'C');
	$pdf->Cell(575, 20, $turnier['titel'], 0, 2, 'C');
	$pdf->Cell(575, 20, $turnier['untertitel'], 0, 2, 'C'); 

// Spielername
	$line['verein'] = mf_certificates_balance_text($line['verein'], 48, 40);
	$abstand_links = 95;
	$abstand_oben = $pdf->getY() + 24;
	$schriftgrad = 24;

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

// Vereinsname
	$pdf->setFont($turnier['font_regular'], '', 18);
	$pdf->SetXY($abstand_links, $pdf->getY() + 8);
	foreach ($line['verein'] as $vereinteil) {
		$pdf->Cell(405, 20, $vereinteil, 0, 2, 'C');
	}

// Platzierung/mit Erfolg teilgenommen
	if ($type === 'platz') {
		$pdf->SetX(158);
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(90, 44, 'hat den', 0, 0, 'R');
		$pdf->setFont($turnier['font_bold'], '', 24);
		$pdf->Cell(110, 42, $line['rang'].'. Platz', 0, 0, $line['rang'] ? 'C' : 'R'); 
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(90, 44, 'belegt', 0, 2, 'L'); 
	} else {
		$pdf->SetX(220);
		$pdf->setFont($turnier['font_bold'], '', 18);
		$pdf->Cell(145, 44, $line['textzeile'], 0, 0, 'C'); 
	}

// Fuß
	$pdf->image($vorlagen.'/386-Emanuel Lasker.png', 35, 677, 99, 121);
	$pdf->setFont($turnier['font_regular'], '', 14);
	$pdf->text(220, 710, $turnier['place'].', '.$turnier['date_of_certificate']); 
	$pdf->text(185, 790, $turnier['signature_left']); 
	$pdf->text(340, 790, $turnier['signature_right']);

	return $pdf;
}
