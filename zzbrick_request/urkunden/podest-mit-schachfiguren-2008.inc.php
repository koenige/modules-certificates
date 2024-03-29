<?php

function cms_urkunde_out($pdf, $turnier, $line, $vorlagen, $type) {

	$pdf->image($vorlagen.'/210b-DEM-Urkunde.jpg', 25, 65, 544, 658);

// Turniername
	$pdf->SetXY(145, 200);
	$pdf->setFont($turnier['font_bold'], '', 20);
	$pdf->Cell(200, 20, $turnier['obertitel'], 0, 2, 'C');
	$pdf->SetX(26);
	$pdf->Cell(575, 20, $turnier['titel'], 0, 2, 'C');
	$pdf->setFont($turnier['font_regular'], '', 18);
	$pdf->Cell(575, 18, $turnier['untertitel'], 0, 2, 'C'); 

// Spielername
	$pdf->setFont($turnier['font_bold'], '', 24);
	$spieler_len = $pdf->GetStringWidth($line['spieler']);
	$zeilen = ceil($spieler_len / 217.5); // 215 zu wenig, 218 zuviel
	$pdf->setFont($turnier['font_regular'], '', 18);
	$verein_len = $pdf->GetStringWidth($line['verein']);
	$zeilen += ceil($verein_len / 217);

	switch ($zeilen) {
		case 2: $add = 27; break;
		case 3: $add = 12; break;
		case 4: $add = 0; break;
	}
	$pdf->SetXY(200, 420 + $add);
	$pdf->setFont($turnier['font_bold'], '', 24);
	$pdf->MultiCell(223, 24, $line['spieler'], 0, 'C');

// Vereinsname
	$pdf->setXY(200, $pdf->GetY() + 4);
	$pdf->setFont($turnier['font_regular'], '', 18);
	$pdf->MultiCell(223, 20, $line['verein'], 0, 'C');


// Platzierung/mit Erfolg teilgenommen
	if ($type === 'platz') {
		$pdf->SetXY(220, 600);
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(150, 24, 'hat den', 0, 2, 'C');
		$pdf->setFont($turnier['font_bold'], '', 24);
		$pdf->Cell(150, 36, ($line['rang'] ? $line['rang'] : '      ').'. Platz', 0, 2, 'C');
		$pdf->setFont($turnier['font_regular'], '', 18);
		$pdf->Cell(150, 24, 'belegt', 0, 2, 'C'); 
	} else {
		$pdf->SetXY(220, 620);
		$pdf->setFont($turnier['font_bold'], '', 18);
		if ($line['textzeile'] === 'hat mit Erfolg teilgenommen')
			$line['textzeile'] = 'mit Erfolg teilgenommen';
		$pdf->MultiCell(150, 24, $line['textzeile'], 0, 'C'); 
	}

// Fuß
	$pdf->setFont($turnier['font_regular'], '', 14);
	$pdf->SetXY(0, 710);
	$pdf->Cell(0, 14, $turnier['place'].', '.$turnier['date_of_certificate'], 0, 0, 'C'); 
	$pdf->text(135, 786, $turnier['signature_left']); 
	$pdf->text(400, 786, $turnier['signature_right']); 

	return $pdf;
}
