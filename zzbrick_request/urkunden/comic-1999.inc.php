<?php

function cms_urkunde_out($pdf, $turnier, $line, $type) {

// Abstand von oben
	$line['verein'] = mf_certificates_balance_text($line['verein'], 44, 36);
	if (strlen($line['spieler']) > 20 AND !empty($line['vorname'])) {
		$line['spieler'] = [$line['vorname'], $line['nachname']]; 
	} else {
		$line['spieler'] = mf_certificates_balance_text($line['spieler'], 26, 20);
	}
	if (count($line['spieler']) > 1 OR count($line['verein']) > 3) {
		$pdf->SetXY(0, 380);
	} else {
		$pdf->SetXY(0, 430);
	}

// Spielername
	$pdf->setFont($turnier['font_bold'], '', 36);
	$pdf->setTextColor(0, 102, 204);   // Chessyblau
	foreach ($line['spieler'] as $spieler) {
		$pdf->Cell(0, 40, $spieler, 0, 2, 'C');
	}

	$pdf->setFont($turnier['font_bold'], '', 24);
	$pdf->setTextColor(0, 0, 0);   // Schwarz
	$pdf->SetXY(0, $pdf->getY() + 10);

// Vereinsname
	foreach ($line['verein'] as $vereinteil) {
		$pdf->Cell(0, 24, $vereinteil, 0, 1, 'C');
	}

// Turniername
// Platzierung/mit Erfolg teilgenommen
	$pdf->setFont($turnier['font_regular'], '', 14);
	$pdf->SetXY(0, $pdf->getY() + 14);
	if ($turnier['turnierzahl'])
		$pdf->Cell(0, 18, 'hat bei der '.$turnier['obertitel_dativ'], 0, 0, 'C');
	else
		$pdf->Cell(0, 18, 'hat bei der', 0, 2, 'C');
	$pdf->Cell(0, 18, $turnier['titel_dativ'], 0, 2,'C'); 
	$pdf->Cell(0, 18, $turnier['untertitel'], 0, 2, 'C');
	if ($type === 'platz') {
		$pdf->SetX(144);
		$pdf->Cell(90, 44, 'den', 0, 0, 'R');
		$pdf->setFont($turnier['font_bold'], '', 18);
		$pdf->Cell(110, 42, $line['rang'].'. Platz', 0, 0, $line['rang'] ? 'C' : 'R');
		$pdf->setFont($turnier['font_regular'], '', 14);
		$pdf->Cell(90, 44, 'belegt', 0, 2, 'L'); 
	} else {
		$pdf->setFont($turnier['font_bold'], '', 14);
		if ($line['textzeile'] === 'hat mit Erfolg teilgenommen') {
			$line['textzeile'] = 'mit Erfolg teilgenommen';
		}
		$pdf->Cell(0, 44, $line['textzeile'], 0, 2, 'C'); 
	}

	return $pdf;
}
