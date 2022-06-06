<?php

// deutsche-schachjugend.de
// Copyright (c) 2004, 2016 Gustaf Mossakowski <gustaf@koenige.org>
// Urkundendruck in PDF: Chessy hält Urkunde


function cms_urkunde_out($pdf, $turnier, $data, $vorlagen, $type) {

	$pdf->AddFont('eraslight', '', 'ERASLGHT.TTF', true);
	$pdf->AddFont('ErasITC-Bold', '', 'ERASBD.TTF', true);

	foreach ($data as $line) {
		$pdf->addPage();
		$pdf->image($vorlagen.'/152b-Urkunde.jpg', 25, 10, 546, 818);

		$pdf->SetXY(168, 375);
		if (empty($turnier['titel_dativ'])) {
			$pdf->setFont('ErasITC-Bold', '', 20);
			$pdf->Cell(300, 20, ($turnier['obertitel'] ? ' '.$turnier['obertitel'] : ''), 0, 2, 'C');
			$pdf->Cell(300, 20, $turnier['titel'], 0, 2, 'C');
			if ($turnier['untertitel']) {
				$pdf->Cell(300, 20, $turnier['untertitel'], 0, 2, 'C');
			}
		}

		$spieler = cms_urkunde_zeile_anpassen($line['spieler'], 20, 16);
		$verein = cms_urkunde_zeile_anpassen($line['verein'], 28, 24);

	// Spielername
		if (count($spieler) > 2) {
			$fontsize = 20;
			$spieler = cms_urkunde_zeile_anpassen($line['spieler'], 25, 20);
		} else {
			$fontsize = 24;
		}
		$pdf->setFont('ErasITC-Bold', '', $fontsize);
		if (count($spieler) + count($verein) < 3) {
	 		$pdf->SetXY(168, $pdf->getY() + 40);
		} elseif (count($spieler) + count($verein) < 4) {
	 		$pdf->SetXY(168, $pdf->getY() + 30);
		} else {
	 		$pdf->SetXY(168, $pdf->getY() + 20);
	 	}
		foreach ($spieler as $teil) {
			$pdf->Cell(300, ceil($fontsize * 1.2), $teil, 0, 2, 'C'); 
		}

	// Vereinsname
		$verein = cms_urkunde_zeile_anpassen($line['verein'], 28, 24);
		if (count($verein) > 3) {
			$fontsize = 16;
			$verein = cms_urkunde_zeile_anpassen($line['verein'], 35, 30);
		} else {
			$fontsize = 20;
		}
		$pdf->setFont('eraslight', '', $fontsize);
		$pdf->SetXY(168, $pdf->getY() + 4);
		foreach ($verein as $teil) {
			$pdf->Cell(300, $fontsize * 1.2, $teil, 0, 2, 'C');
		}

	// Turniername
	// Platzierung/mit Erfolg teilgenommen
		$abstand = (count($verein) + count($spieler) < 5) ? 24 : 4;
		$pdf->SetXY(168, $pdf->getY() + $abstand);
		if (!empty($turnier['titel_dativ'])) {
			$pdf->setFont('eraslight', '', 14);
			$pdf->Cell(300, 20, 'hat bei der'.($turnier['obertitel'] ? ' '.$turnier['obertitel_dativ'] : ''), 0, 2, 'C');
			$pdf->Cell(300, 20, $turnier['titel_dativ'], 0, 2, 'C');
			$pdf->Cell(300, 20, $turnier['untertitel'], 0, 2, 'C');
		}
		if ($pdf->getY() > 570) $pdf->SetXY(215, $pdf->getY());
		if (empty($turnier['titel_dativ'])) {
			$pdf->SetXY($pdf->getX() + 8, $pdf->getY());
		}
		if ($type === 'platz') {
			$pdf->setFont('eraslight', '', 14);
			$pdf->Cell(90, 34, !empty($turnier['titel_dativ']) ? 'den' : 'hat den', 0, 0, 'R');
			$pdf->setFont('ErasITC-Bold', '', 18);
			$pdf->Cell(110, 32, $line['rang'].'. Platz', 0, 0, $line['rang'] ? 'C' : 'R');
			$pdf->setFont('eraslight', '', 14);
			$pdf->Cell(90, 34, 'belegt', 0, 2, 'L'); 
		} else {
			if ($line['textzeile'] === 'hat mit Erfolg teilgenommen' AND !empty($turnier['titel_dativ'])) {
				$line['textzeile'] = 'mit Erfolg teilgenommen';
			}
			$pdf->Cell(300, 34, $line['textzeile'], 0, 0, 'C');
		}

	// Fuß		
		$pdf->setFont('eraslight', '', 11);
		$pdf->SetXY(295, 630);
		$pdf->Cell(185, 15, $turnier['place'].', '.$turnier['date_of_certificate'], 0, 2, 'C'); 
		$pdf->SetXY(295, 690);
		$pdf->Cell(90, 15, $turnier['signature_left'], 0, 0, 'C'); 
		$pdf->Cell(90, 15, $turnier['signature_right'], 0, 2, 'C');
	}
	return $pdf;
}
