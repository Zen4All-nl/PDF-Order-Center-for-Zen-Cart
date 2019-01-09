<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 *  Packing-Slip.php
 *
 */

if ($pageloop == "0") {   // initialize pdf settings

  $pdf = new Cezpdf(A4,portrait); // change for your desired paper / orientation

  $pdf->selectFont(DIR_PDFOC_FONTS . 'Helvetica.afm');
  $pdf->setFontFamily(DIR_PDFOC_FONTS . 'Helvetica.afm');

  // Note: units are 72 * inches
  
  define('PDFOC_LEFT_MARGIN','30');
  define('PDFOC_BOTTOM_MARGIN','100');

  // The small indents in the Sold to: & Ship to: text blocks
  define('PDFOC_TEXT_BLOCK_INDENT', '10');
  define('PDFOC_SOLD_TO_COLUMN_START','198');
  define('PDFOC_SHIP_TO_COLUMN_START','388');

  // This changes the 'Total - Subtotal - Tax - Shipping' text block
  // position. For example, if you increase the font size, you'll need to
  // tweak this value in order to prevent the text from clashing together.
  define('PDFOC_PRODUCT_TOTAL_TITLE_COLUMN_START','400');
  define('PDFOC_RIGHT_MARGIN','30');

  define('PDFOC_LINE_LENGTH', '552');

  // If you have attributes for certain products, you can have the text wrap
  // or force it onto on one line. Set to true for wrap, false for single line.
  define('PDFOC_PRODUCT_ATTRIBUTES_TEXT_WRAP', false);

  // Vertical spacing between sections
  define('PDFOC_SECTION_DIVIDER', '15');

  // Product table settings
  define('PDFOC_TABLE_HEADER_FONT_SIZE', '10');
  define('PDFOC_TABLE_HEADER_BKGD_COLOR', PDFOC_DARK_GREY);
  define('PDFOC_PRODUCT_TABLE_HEADER_WIDTH', '517');

  // Simulate cell padding in HTML tables
  define('PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN', '2');
  define('PDFOC_PRODUCT_TABLE_LEFT_MARGIN', '2');

  // Height of the product listing rectangles
  define('PDFOC_PRODUCT_TABLE_ROW_HEIGHT', '13');

  // The column sizes are where the product listing columns start on the
  // PDF page. If you increase TABLE HEADER FONT SIZE, you'll
  // need to tweak these values to prevent text from clashing together.
  define('PDFOC_PRODUCTS_COLUMN_SIZE', '165');
  define('PDFOC_PRODUCT_LISTING_BKGD_COLOR',PDFOC_GREY);
  define('PDFOC_MODEL_COLUMN_SIZE', '40');
  define('PDFOC_PRICING_COLUMN_SIZES', '64');


} else {  // print out an invoice


  $currencies = new currencies();

  // company name and details pulled from the my store address and phone number
  // in admin configuration mystore
  $y = $pdf->ezText(STORE_NAME_ADDRESS,PDFOC_COMPANY_HEADER_FONT_SIZE);
  $y -= 10;

  // logo image (x offset from left, y offset from bottom, width, height)
  $pdf->addJpegFromFile(DIR_PDFOC_TEMPLATES . 'invoicelogo.jpg',265,730,300,95);

  // extra info boxes to be used by staff (comment out if not desired)
  //$pdf->setStrokeColor(0,0,0);
  //$pdf->setLineStyle(1);
  //$pdf->roundedRectangle(470,730,85,85,10,$f=0);
  //$pdf->rectangle(535,748,10,10);
  //$pdf->rectangle(535,769,10,10);
  //$pdf->rectangle(535,790,10,10);
  //$pdf->addText(310,790,PDFOC_GENERAL_FONT_SIZE,'In Stock');
  //$pdf->addText(310,769,PDFOC_GENERAL_FONT_SIZE,'Packed By ______________________');
  //$pdf->addText(310,748,PDFOC_GENERAL_FONT_SIZE,'Verified By ______________________');


  // line after header
  //
  $pdf->setLineStyle(1);
  $pdf->line(PDFOC_LEFT_MARGIN,$y,PDFOC_LINE_LENGTH,$y);

  // document identifier
  //
  $pdf->ezSetY($y - 45);
  $cx = (PDFOC_SOLD_TO_COLUMN_START-10-$pdf->getTextWidth(PDFOC_GIANT_FONT_SIZE,PDFOC_ENTRY_PACKING_SLIP))*0.5;
  $pdf->ezText(PDFOC_ENTRY_PACKING_SLIP,PDFOC_GIANT_FONT_SIZE, array('aleft'=>$cx));

  //left rounded rectangle around "sold to" info
  //
  $pdf->ezSetY($y);
  $pdf->setStrokeColor(0,0,0);
  $pdf->setLineStyle(1);
  $pdf->roundedRectangle(PDFOC_SOLD_TO_COLUMN_START-10,595,180,120,10,$f=0);


  // move down into rectangle
  //
  $y = $y - 30;
  
  // "sold to" info in left rectangle
  $pdf->addText(PDFOC_SOLD_TO_COLUMN_START,$y,PDFOC_SUB_HEADING_FONT_SIZE,"<b>" . PDFOC_ENTRY_SOLD_TO . "</b>");

  $pos = $y-10;
  $indent = PDFOC_SOLD_TO_COLUMN_START + PDFOC_TEXT_BLOCK_INDENT;

  // print billing address in "sold to" box
  $addressparts = explode("\n", zen_address_format($order->customer['format_id'], $order->billing, 1, '', " \n"));
  if (PDFOC_SHIP_FROM_COUNTRY == $addressparts[count($addressparts)-1]) { // don't print country if national delivery; only works for address formats #2, #4, and #5
     $addressparts[count($addressparts)-1] = '';
  }
  foreach($addressparts as $addresspart) {
    $pdf->addText($indent,$pos -=PDFOC_GENERAL_LEADING,PDFOC_GENERAL_FONT_SIZE,$addresspart);
  }

  // right rounded rectangle around "ship to" info
  $pdf->setStrokeColor(0,0,0);
  $pdf->setLineStyle(1);
  $pdf->roundedRectangle(PDFOC_SHIP_TO_COLUMN_START-10,595,180,120,10,$f=0);

  // ship to info in right rectangle
  $pdf->addText(PDFOC_SHIP_TO_COLUMN_START,$y,PDFOC_SUB_HEADING_FONT_SIZE,"<b>" . PDFOC_ENTRY_SHIP_TO . "</b>");

  $pos = $y-10;
  $indent = PDFOC_SHIP_TO_COLUMN_START + PDFOC_TEXT_BLOCK_INDENT;

  // print delivery address in "ship to" box
  $addressparts = explode("\n", zen_address_format($order->delivery['format_id'], $order->delivery, 1, '', " \n"));
  if (PDFOC_SHIP_FROM_COUNTRY == $addressparts[count($addressparts)-1]) { // don't print country if national delivery; only works for address formats #2, #4, and #5
     $addressparts[count($addressparts)-1] = '';
  }
  foreach($addressparts as $addresspart) {
    $pdf->addText($indent,$pos -=PDFOC_GENERAL_LEADING,PDFOC_GENERAL_FONT_SIZE,$addresspart);
  }

  // divider between addresses and order information
  $pos -= PDFOC_SECTION_DIVIDER;
  $pdf->ezSetY($pos - 60 );

  // order number
  $pos = $pdf->ezText("<b>" . PDFOC_TEXT_ORDER_NUMBER . " </b>" . $orders->fields['orders_id'],PDFOC_SUB_HEADING_FONT_SIZE);

  // order date
  if ($_POST['show_order_date']) {
  $pos = $pdf->ezText("<b>" . PDFOC_TEXT_ORDER_DATE . " </b>" . zen_date_short($order->info['date_purchased']),PDFOC_SUB_HEADING_FONT_SIZE);
  }

  // phone and e-mail: displays blank lines if turned off so as to maintain layout
  if ($_POST['show_phone'] || $_POST['show_email'] ) {

    if ($_POST['show_phone']) {
      if ($order->customer['telephone']!='') {
        $pos = $pdf->ezText("<b>" . PDFOC_ENTRY_PHONE . "</b> " . $order->customer['telephone'],PDFOC_GENERAL_FONT_SIZE);
      } else {
        $pos = $pdf->ezText("");
      }
    }
    if ($_POST['show_email']) {
      if ($order->customer['email_address']!='') {
        $pos = $pdf->ezText("<b>" . PDFOC_ENTRY_EMAIL . "</b> " .$order->customer['email_address'],PDFOC_GENERAL_FONT_SIZE);
      } else {
        $pos = $pdf->ezText("");
      }
    }

  } else {

    $pos = $pdf->ezText("");
    $pos = $pdf->ezText("");

  } // EOIF $_POST['show_phone']


  // divider between email and payment method
  $pos -= PDFOC_SECTION_DIVIDER;
  $pdf->ezSetY($pos);
 

  $pos -= PDFOC_SECTION_DIVIDER;
 
  // products , model etc table layout
  PDFOC_change_color(PDFOC_TABLE_HEADER_BKGD_COLOR);
  $pdf->filledRectangle(PDFOC_LEFT_MARGIN,$pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT,PDFOC_PRODUCT_TABLE_HEADER_WIDTH,PDFOC_PRODUCT_TABLE_ROW_HEIGHT);

  $x = PDFOC_LEFT_MARGIN + PDFOC_PRODUCT_TABLE_LEFT_MARGIN;
  $pos = ($pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT) + PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;

  PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);

  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
  $pdf->ezText(PDFOC_TABLE_HEADING_PRODUCTS,PDFOC_TABLE_HEADER_FONT_SIZE, array('aleft'=>$x));
  $x += PDFOC_PRODUCTS_COLUMN_SIZE;
  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
  $pdf->ezText(PDFOC_TABLE_HEADING_PRODUCTS_MODEL,PDFOC_TABLE_HEADER_FONT_SIZE, array('aleft'=>$x));
//  $x += PDFOC_MODEL_COLUMN_SIZE;
//  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
//  $pdf->ezText(PDFOC_TABLE_HEADING_TAX . " (%)",PDFOC_TABLE_HEADER_FONT_SIZE, array('aleft'=>$x));

//  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
//  $x += PDFOC_MODEL_COLUMN_SIZE+PDFOC_PRICING_COLUMN_SIZES;
//  $pdf->ezText(PDFOC_TABLE_HEADING_PRICE_EXCLUDING_TAX,PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
//  $x += PDFOC_PRICING_COLUMN_SIZES;
//  $pdf->ezText(PDFOC_TABLE_HEADING_PRICE_INCLUDING_TAX,PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
//  $x += PDFOC_PRICING_COLUMN_SIZES;
//  $pdf->ezText(PDFOC_TABLE_HEADING_TOTAL_EXCLUDING_TAX,PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//  $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
//  $x += PDFOC_PRICING_COLUMN_SIZES;
//  $pdf->ezText(PDFOC_TABLE_HEADING_TOTAL_INCLUDING_TAX,PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));

  $pos -= PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;

  // Sort through the products

  for ($i = $nextproduct, $n = sizeof($order->products); $i < $n; $i++) {

    // check whether too far down page to print more products; assume enough margin
    // to account for a product with wrapped text and a couple of attributes
    //
    if ($pos < PDFOC_BOTTOM_MARGIN) {
       $secondpage = true;
       return;
    }

    $prod_str = $order->products[$i]['qty'] . " x " . $order->products[$i]['name'];

    PDFOC_change_color(PDFOC_PRODUCT_LISTING_BKGD_COLOR);
    $pdf->filledRectangle(PDFOC_LEFT_MARGIN,$pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT,PDFOC_PRODUCT_TABLE_HEADER_WIDTH,PDFOC_PRODUCT_TABLE_ROW_HEIGHT);

    $x = PDFOC_LEFT_MARGIN + PDFOC_PRODUCT_TABLE_LEFT_MARGIN;
    $pos = ($pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT) + PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;

    PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);
    $truncated_str = $pdf->addTextWrap($x,$pos,PDFOC_PRODUCTS_COLUMN_SIZE,PDFOC_TABLE_HEADER_FONT_SIZE,$prod_str);

    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
    $x += PDFOC_PRODUCTS_COLUMN_SIZE;
    $pdf->ezText(pdfoc_html_cleanup($order->products[$i]['model']),PDFOC_TABLE_HEADER_FONT_SIZE,array('aleft'=>$x));
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
//    $x += PDFOC_MODEL_COLUMN_SIZE;
//    $pdf->ezText("   " . pdfoc_html_cleanup(zen_display_tax_value($order->products[$i]['tax'])),PDFOC_TABLE_HEADER_FONT_SIZE,array('aleft'=>$x));

//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
//    $x += PDFOC_MODEL_COLUMN_SIZE+ PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezText(pdfoc_html_cleanup($currencies->format($order->products[$i]['final_price'], true, $order->info['currency'], $order->info['currency_value'])),PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
//    $x += PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezText(pdfoc_html_cleanup($currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']), true, $order->info['currency'], $order->info['currency_value'])),PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right', 'aright'=>$x));
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
//    $x += PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezText(pdfoc_html_cleanup($currencies->format($order->products[$i]['final_price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value'])),PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TABLE_ROW_HEIGHT-PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN);
//    $x += PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezText(pdfoc_html_cleanup($currencies->format(zen_add_tax($order->products[$i]['final_price'], $order->products[$i]['tax']) * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value'])),PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));

    $pos -= PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;

    if ($truncated_str) {
	
	PDFOC_change_color(PDFOC_PRODUCT_LISTING_BKGD_COLOR);
	$pdf->filledRectangle(PDFOC_LEFT_MARGIN,$pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT,PDFOC_PRODUCT_TABLE_HEADER_WIDTH,PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
	$pos = ($pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT) + PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);
	$reset_x = PDFOC_LEFT_MARGIN + PDFOC_PRODUCT_TABLE_LEFT_MARGIN;
	$pdf->addText($reset_x,$pos,PDFOC_TABLE_HEADER_FONT_SIZE,$truncated_str);
	$pos -= PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	
    } // EOIF $truncated_str
	
    if ( ($k = sizeof($order->products[$i]['attributes'])) > 0) {

      for ($j = 0; $j < $k; $j++) {

        $attrib_string = '<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'];
        
        if ($order->products[$i]['attributes'][$j]['price'] != '0') {
          $attrib_string .= ' (' . $order->products[$i]['attributes'][$j]['prefix'] .
          pdfoc_html_cleanup($currencies->format($order->products[$i]['attributes'][$j]['price'] * $order->products[$i]['qty'], true, $order->info['currency'], $order->info['currency_value'])) . ')';
        }
		
        $attrib_string .= '</i>';
	  PDFOC_change_color(PDFOC_PRODUCT_LISTING_BKGD_COLOR);
	  $pdf->filledRectangle(PDFOC_LEFT_MARGIN,$pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT,PDFOC_PRODUCT_TABLE_HEADER_WIDTH,PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
	  $pos = ($pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT) + PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	  PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);
	  $reset_x = PDFOC_LEFT_MARGIN + PDFOC_PRODUCT_TABLE_LEFT_MARGIN;

        if (PDFOC_PRODUCT_ATTRIBUTES_TEXT_WRAP) {
          $wrapped_str = $pdf->addTextWrap($reset_x,$pos,PDFOC_PRODUCTS_COLUMN_SIZE,PDFOC_PRODUCT_ATTRIBUTES_FONT_SIZE,$attrib_string);
        } else {
          $pdf->addText($reset_x,$pos,PDFOC_PRODUCT_ATTRIBUTES_FONT_SIZE,$attrib_string);
        }

        $pos -= PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	  			
        if ($wrapped_str) {
        
	    PDFOC_change_color(PDFOC_PRODUCT_LISTING_BKGD_COLOR);
	    $pdf->filledRectangle(PDFOC_LEFT_MARGIN,$pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT,PDFOC_PRODUCT_TABLE_HEADER_WIDTH,PDFOC_PRODUCT_TABLE_ROW_HEIGHT);
	    $pos = ($pos-PDFOC_PRODUCT_TABLE_ROW_HEIGHT) + PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	    PDFOC_change_color(PDFOC_GENERAL_FONT_COLOR);
	    $pdf->addText($reset_x,$pos,PDFOC_PRODUCT_ATTRIBUTES_FONT_SIZE,$wrapped_str);
	    $pos -= PDFOC_PRODUCT_TABLE_BOTTOM_MARGIN;
	    
	  } // EOIF $wrapped_str
	  
	} // EOFOR $j = 0
	
    } // EOIF $k = sizeof(...
    
    $nextproduct++;

  } // EOFOR $i = 0

//  $pos -= 1.5; // to match LineStyle below

  // line under Totals column
  //
//  $pdf->setLineStyle(1.5);
//  $tx = $x -  PDFOC_PRICING_COLUMN_SIZES + 15;
//  $pdf->line($tx,$pos,$x+13,$pos);  // tweak this value to match end of your table

//  $pos -= PDFOC_SECTION_DIVIDER;

//  for ($i = 0, $n = sizeof($order->totals); $i < $n; $i++) {
	
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TOTALS_LEADING);
//    $x -= PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezText("<b>" . pdfoc_html_cleanup($order->totals[$i]['title']) . "</b>",PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));
//    $x += PDFOC_PRICING_COLUMN_SIZES;
//    $pdf->ezSetY($pos+PDFOC_PRODUCT_TOTALS_LEADING);
//    $pdf->ezText(pdfoc_html_cleanup($order->totals[$i]['text']),PDFOC_TABLE_HEADER_FONT_SIZE,array('justification'=> 'right','aright'=>$x));

//    $pdf->addText($x,$pos,PDFOC_PRODUCT_TOTALS_FONT_SIZE,pdfoc_html_cleanup($order->totals[$i]['text']), $order->info['currency_value']);

//    $pos -=PDFOC_PRODUCT_TOTALS_LEADING;
    
//  } // EOFOR $i = 0

  $pos -= 3*PDFOC_SECTION_DIVIDER;


  if ($show_comments) {  // print out all comments for this order
  
    $innum = $orders->fields['orders_id'];
    $orders_comments = $db->Execute("select comments,date_added from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . (int)$innum . "' order by date_added");

    if ($orders_comments->RecordCount()>0) {
  
	  // resume printing comments where we left off,
	  // if page was split 	
	  for ($i=0; $i<$nextcomment; $i++)
	  {
		  $orders_comments->MoveNext();
	  }
      while (!$orders_comments->EOF) {
    
        if(zen_not_null($orders_comments->fields['comments'])) {
      
			// check whether too far down page to print more comments
		    //
		    if ($pos < PDFOC_BOTTOM_MARGIN) {
		       $secondpage = true;
		       return;
		    }
          $pdf->ezSetY($pos);
          $cy = $pdf->ezText(zen_date_short($orders_comments->fields['date_added']) ,7); // 7 is font size here
          $pdf->ezText("<b>". PDFOC_TEXT_COMMENTS . "</b>",PDFOC_COMMENTS_FONT_SIZE);
          $cx = $pdf->getTextWidth(PDFOC_COMMENTS_FONT_SIZE,PDFOC_TEXT_COMMENTS) + PDFOC_LEFT_MARGIN;
          $pdf->ezSetY($cy);
          $y = $pdf->ezText(pdfoc_html_cleanup($orders_comments->fields['comments']),PDFOC_COMMENTS_FONT_SIZE, array('aleft'=>$cx+10));
          $pos = ($y -5);
        
        }  // EOIF zen_not_null
      
        $orders_comments->MoveNext();
        $nextcomment++;
      } // EOWHILE $orders_comments
    
    } // EOIF $orders_comments->RecordCount()

  } // EOIF $show_comments

  // this invoice has been completed, so restore $secondpage and $nextproduct
  $secondpage = false;
  $nextproduct = $nexttotal = $nextcomment = 0;

  // To help you see how elements line up, uncomment the line below to print out a
  // grid over the invoice.
  //require(DIR_PDFOC_INCLUDE . 'templates/' . 'grid.php');

} // EOIF $pageloop
?>
