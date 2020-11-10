<?php

		/* 
		
		$pdf = &PDF::factory('p', 'a4');      // Set up the pdf object.
		$pdf->open();                         // Start the document.
		$pdf->setCompression(true);           // Activate compression.
		$pdf->addPage();                      // Start a page.
		$pdf->setFont('Courier', '', 8);      // Set font to courier 8 pt.
		$pdf->text(100, 100, 'First page');   // Text at x=100 and y=100.
		$pdf->setFontSize(20);                // Set font size to 20 pt.
		$pdf->setFillColor('rgb', 1, 0, 0);   // Set text color to red.
		$pdf->text(100, 200, 'HELLO WORLD!'); // Text at x=100 and y=200.
		
		$pdf->setDrawColor('rgb', 0, 0, 1);   // Set draw color to blue.
		$pdf->line(100, 202, 240, 202);       // Draw a line.
		$pdf->setFillColor('rgb', 1, 1, 0);   // Set fill/text to yellow.
		$pdf->rect(200, 300, 100, 100, 'fd'); // Draw a filled rectangle.
		$pdf->addPage();                      // Add a new page.
		
		$pdf->setFont('Arial', 'BI', 12);     // Set font to arial bold
		                                      // italic 12 pt.
		$pdf->text(100, 100, 'Second page');  // Text at x=100 and y=100.
		$pdf->image('sample.jpg', 50, 200);   // Image at x=50 and y=200.
		$pdf->setLineWidth(4);                // Set line width to 4 pt.
		$pdf->circle(200, 300, 150, 'd');     // Draw a non-filled
		                                      // circle.
		$pdf->output('foo.pdf');              // Output the file named foo.pdf
		
		*/

?>