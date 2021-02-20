<?php

function cms_urkunde_out($turnier, $data, $vorlagen, $type) {

	ini_set('memory_limit', '512M');
	$pdf = new TFPDF('P', 'pt', 'A4');		// panorama = p, DIN A4, 595 x 842
	$pdf->open();
	$pdf->setCompression(true);

	$pdf->AddFont('eraslight', '', 'ERASLGHT.TTF', true);
	$pdf->AddFont('ErasITC-Bold', '', 'ERASBD.TTF', true);

	$pdf->setMargins(0,0);

	foreach ($data as $line) {
		$pdf->addPage();
		$pdf->image($vorlagen.'/Schachbrett-2019.jpg', 20, 20, 555, 373);

	// Turniername
		$pdf->SetXY(10, 406);
		$pdf->setFont('ErasITC-Bold', '', 24);
		$pdf->Cell(575, 28, $turnier['obertitel'], 0, 2, 'C');
		$pdf->Cell(575, 28, $turnier['titel'], 0, 2, 'C');
		$pdf->Cell(575, 28, $turnier['untertitel'], 0, 2, 'C'); 

	// Spielername
		$line['verein'] = cms_urkunde_zeile_anpassen($line['verein'], 48, 40);
		$abstand_links = 95;
		$abstand_oben = $pdf->getY() + 24;
		$schriftgrad = 24;

		$pdf->setFont('ErasITC-Bold', '', $schriftgrad);
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
			
			$line['spieler'] = cms_urkunde_zeile_anpassen($line['spieler'], 40, 36);
			foreach ($line['spieler'] as $spieler) {
				$pdf->Cell(405, 28, $spieler, 0, 2, 'C');
			}
		}

	// Vereinsname
		$pdf->setFont('eraslight', '', 18);
		$pdf->SetXY($abstand_links, $pdf->getY() + 8);
		foreach ($line['verein'] as $vereinteil) {
			$pdf->Cell(405, 20, $vereinteil, 0, 2, 'C');
		}

	// Platzierung/mit Erfolg teilgenommen
		if ($type === 'platz') {
			$pdf->SetX(158);
			$pdf->setFont('eraslight', '', 18);
			$pdf->Cell(90, 44, 'hat den', 0, 0, 'R');
			$pdf->setFont('ErasITC-Bold', '', 24);
			$pdf->Cell(110, 42, $line['rang'].'. Platz', 0, 0, $line['rang'] ? 'C' : 'R'); 
			$pdf->setFont('eraslight', '', 18);
			$pdf->Cell(90, 44, 'belegt', 0, 2, 'L'); 
		} else {
			$pdf->SetX(220);
			$pdf->setFont('ErasITC-Bold', '', 18);
			$pdf->Cell(145, 44, $line['textzeile'], 0, 0, 'C'); 
		}

	// Fuß
		$pdf->image($vorlagen.'/DSJ-Logo.png', 248, 720, 98, 80);
		$pdf->SetXY(0, 690);
		$pdf->setFont('eraslight', '', 14);
		$pdf->Cell(0, 14, $turnier['place'].', '.$turnier['date_of_certificate'], 0, 0, 'C'); 
		$pdf->text(110, 795, $turnier['signature_left']); 
		$pdf->text(410, 795, $turnier['signature_right']);
	}
	return $pdf;
}
