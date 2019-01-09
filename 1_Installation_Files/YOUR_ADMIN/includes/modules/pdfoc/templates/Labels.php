<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * Labels.php
 *
 */

if ($pageloop == "0") {  // initialize paper type and size, positions, etc

  $pdf = new Cezpdf(A4,portrait); // change for your desired paper / orientation

  $pdf->selectFont(DIR_PDFOC_FONTS . 'Helvetica.afm');
  $pdf->setFontFamily(DIR_PDFOC_FONTS . 'Helvetica.afm');

  // How many labels to print on a page
  define('PDFOC_NUM_COLUMNS', '2');
  define('PDFOC_NUM_ROWS', '8');
  define('PDFOC_NUM_LABELS_PER_PAGE', PDFOC_NUM_ROWS * PDFOC_NUM_COLUMNS);

  // These determine the locations of the labels, I think dimensions are (72 * inches)
  // 2.54 cm = 1 inch
  // note that (0, 0 ) = bottom left corner of page; y is maximum at TOP of page, x is maximum at RIGHT of page
  define('PDFOC_LABEL_WIDTH', '284'); // the horizontal space between the left edges of two consecutive labels
  define('PDFOC_LABEL_HEIGHT', '96'); // the vertical space between the bottom edges of two consecutive labels
  define('PDFOC_STARTX', '22'); //the horizontal space from left edge of label sheet to left edge of first label
  define('PDFOC_STARTY', '5'); // the vertical space from bottom edge of label sheet to bottom edge of first label

  // These control the little order_id text printed to the right of the address on the label
  define('PDFOC_ORDER_ID_FONT_SIZE', '6');
  define('PDFOC_ORDER_ID_X_OFFSET', '20'); // position from right side of label
  define('PDFOC_ORDER_ID_Y_OFFSET', '-2'); // position from top of label


  //if ($_POST['pull_status']){ $pull_w_status = " and o.orders_status = ". $_POST['pull_status']; }
  
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
  else { $endpos = PDFOC_NUM_LABELS_PER_PAGE; }                      // fill label sheet to end

  pdfoc_change_color(PDFOC_GENERAL_FONT_COLOR);

} else {      // pageloop != 0, time to actually print a label


  $currencies = new currencies();

  // Print out the labels for all orders that fit onto this label sheet.
  // Note that this code progresses through the $orders array until it either runs out
  // of orders or hits the end of the label sheet. In either case, the wrapper "while" in
  // batch_print.php will take over and either terminate (if it's run out of orders) or
  // start a new page in the pdf file (if it's hit the end of the current label sheet).
  // In this second case, $startpos must be reset to 1 in order to start from the top
  // of the new label sheet.
  //
  
  $numlabel = 0;  // initialize count of labels already printed on this sheet
  $pos = 1;       // initialize the pdf page to the first label

  if ($numpage > 0) { $startpos = 1; }  // if gone to a new label sheet, start printing at the first label
  
  for($y = $pdf->ez['pageHeight'] - PDFOC_STARTY; $y > PDFOC_LABEL_HEIGHT - PDFOC_STARTY; $y -= PDFOC_LABEL_HEIGHT) {

    for ($x = PDFOC_STARTX; $x < PDFOC_STARTX + PDFOC_NUM_COLUMNS * PDFOC_LABEL_WIDTH; $x += PDFOC_LABEL_WIDTH) {

      if ($startpos <= $pos && $numlabel <= $endpos-$startpos) { // if at a valid label position,

        if ($numlabel > 0) { $orders->MoveNext(); } // go to next order in this batch (not on first interation; we already have an order then
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

       // reduce font size as needed to fit this address onto specified label size
       $fontsize = PDFOC_GENERAL_FONT_SIZE;
        foreach($address_array as $address) {
	    while ($pdf->getTextWidth($fontsize, $address) > PDFOC_LABEL_WIDTH) {
		$fontsize--;
	    }
        } // EOFOREACH

        // now add address to pdf file
        $ypos = $y;
        foreach($address_array as $address) {
          $pdf->addText($x,$ypos -=PDFOC_GENERAL_LINE_SPACING,$fontsize,$address);
        }

        $pdf->addText($x + PDFOC_LABEL_WIDTH - PDFOC_ORDER_ID_X_OFFSET,$y + PDFOC_ORDER_ID_Y_OFFSET,PDFOC_ORDER_ID_FONT_SIZE,$orders->fields['orders_id']);
        
        $numlabel++;  // another label has been printed
      
      }  // EOIF $startpos
    
      $pos++;   // move to next free label
      

    } // EOFOR $x

    // Send fake header to avoid timeout, got this trick from phpMyAdmin
    $time1  = time();
    if ($time1 >= $time0 + 30) {
      $time0 = $time1;
      header('X-bpPing: Pong');
    }
  
  } // EOFOR $y

}  // EOIF $pageloop

?>
