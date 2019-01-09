<?php
/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * Labelwriter-Shipping.php    written by Sten Johnsen
 *
 */

// TWEAK THE SETTINGS TO SUIT YOUR LABELWRITER.
// set paper type and size

define('PAGE_WIDTH', '9.8');
define('PAGE_HEIGHT', '5.4');

if ($pageloop == "0") {  // initialize paper type and size, positions, etc

  $pdf = new Cezpdf(array(PAGE_WIDTH, PAGE_HEIGHT));
  //$pdf = new Cezpdf(A7,landscape); // change for your desired paper / orientation

  $pdf->selectFont(DIR_PDFOC_FONTS . 'Helvetica-Bold.afm');
  $pdf->setFontFamily(DIR_PDFOC_FONTS . 'Helvetica.afm');


  // These determine the locations of the labels, I think dimensions are (72 * inches)
  // 2.54 cm = 1 inch
  // note that (0, 0 ) = bottom left corner of page; y is maximum at TOP of page, x is maximum at RIGHT of page
  define('PDFOC_X_MARGIN', '24');
  define('PDFOC_Y_MARGIN', '10');
  define('PDFOC_NAME_FONT_SIZE', '16');
  define('PDFOC_ADDRESS_FONT_SIZE', '12');

  // These control the little order_id text printed to the right of the address on the label
  define('PDFOC_ORDER_ID_FONT_SIZE', '10');
  define('PDFOC_ORDER_ID_X_OFFSET', '0'); // position from right side of label
  define('PDFOC_ORDER_ID_Y_OFFSET', '0'); // position from top of label

  // Return Address Sizes
  define('PDFOC_RETURN_ADDRESS_FONT_SIZE', '10');
  define('PDFOC_RETURN_ADDRESS_LINE_SPACING', '10');

  if ($_POST['startpos']) { $startpos = $_POST['startpos']; }  // start on the specified label
  else { $startpos = 1; }                                      // start at the beginning of the label sheet

  if ($_POST['address']) {
    if ($_POST['address'] == "billing") { $billing = true; }   // use billing address
    else { $billing = false; }                                 // use delivery address
  }
  else {
    $billing = false;                                          // use delivery address as default
  }

  if ($_POST['endpos']) { $endpos = $_POST['endpos']; }        // last label to use is specified
  else { $endpos = 1; }                      // fill label sheet to end

  PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);

} else {      // pageloop != 0, time to actually print a label

     if ($orders->EOF) {   // if there is no next order, break out of the label printing section
       return;
     }

     // if we are here, we have a valid order and a valid label position
     $order = new order($orders->fields['orders_id']);

     if ($billing == true) { // print billing address
        $address_array=explode("\n",zen_address_format($order->billing['format_id'], $order->billing, 1, '', " \n"));
     } else  {                // else print shipping address
        $address_array=explode("\n",zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', " \n"));
     }

     if (PDFOC_SHIP_FROM_COUNTRY == $address_array[count($address_array)-1]) { // don't print country if national delivery; only works for address formats #2, #4, and #5
           $address_array[count($address_array)-1] = '';
     }

	// Print return address
	$ypos = $pdf->ez['pageHeight'] - PDFOC_Y_MARGIN;		// Start at top of label
	$storename = STORE_NAME_ADDRESS;
	$store = explode("<br />", nl2br($storename));
	foreach($store as $line) {
	  $x = $pdf->ez['pageWidth'] - PDFOC_X_MARGIN - $pdf->getTextWidth(PDFOC_RETURN_ADDRESS_FONT_SIZE, trim($line));
	  $pdf->addText($x, $ypos -=PDFOC_RETURN_ADDRESS_LINE_SPACING, PDFOC_RETURN_ADDRESS_FONT_SIZE, trim($line));
	}

	// Print image
	$pdf->addJpegFromFile(DIR_PDFOC_TEMPLATES . 'logo.jpg', PDFOC_X_MARGIN, $ypos, 68);

	// Print line
	$pdf->setLineStyle(2);
	$ypos -=PDFOC_RETURN_ADDRESS_LINE_SPACING;
	$pdf->line(PDFOC_X_MARGIN, $ypos, $pdf->ez['pageWidth'] - PDFOC_X_MARGIN, $ypos);

	// Print the order number
	$ypos -=PDFOC_RETURN_ADDRESS_LINE_SPACING;
    $pdf->addText(	$pdf->ez['pageWidth'] - PDFOC_X_MARGIN - $pdf->getTextWidth(PDFOC_ORDER_ID_FONT_SIZE, $orders->fields['orders_id']), 
					$ypos,
					PDFOC_ORDER_ID_FONT_SIZE,
					$orders->fields['orders_id']);

    // reduce font size as needed to fit this address onto specified label size
    $fontsize = PDFOC_NAME_FONT_SIZE; //PDFOC_GENERAL_FONT_SIZE;
    foreach($address_array as $address) {
	  while ($pdf->getTextWidth($fontsize, $address) > $pdf->ez['pageWidth']) { $fontsize--;}
    } // EOFOREACH

	// Print the Destination address
	$x = PDFOC_X_MARGIN;						// Go to left margin
	foreach($address_array as $i=>$address) {
	  if ($i > 0) $fontsize = PDFOC_ADDRESS_FONT_SIZE;	// All lines but the first is smaller font
	   $pdf->addText(	$x,						// Left margin
	 					$ypos -=PDFOC_GENERAL_LINE_SPACING,	// One line lower before printing
						$fontsize,				// 
						$address);
    }

    // Send fake header to avoid timeout, got this trick from phpMyAdmin
    $time1  = time();
    if ($time1 >= $time0 + 30) {
      $time0 = $time1;
      header('X-bpPing: Pong');
    }

}  // EOIF $pageloop
?>
