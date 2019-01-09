<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 *  Packing-Slip-and-Invoice.php
 *
 */

// call the individual invoice and packing slip scripts, with
// a newPage in between them.
//
// Note that this includes a bunch of stuff (everything in the
// "if $pageloop == 0" branch) in the second included file that
// is unnecessary, but c'est la vie. It's only traversed (uselessly)
// for the first order, anyway, when pageloop = 0.
//
// The plus is that you don't have to edit this file when you make
// changes to the invoice or packing slip formats.
//

// store the current $nextproduct for Packing-Slip, so it doesn't get
// overwritten below in Invoice
$nextproduct = $nextproduct1;
$nextcomment = $nextcomment1;
require(DIR_PDFOC_INCLUDE . 'templates/' . 'Packing-Slip.php');
$nextproduct1 = $nextproduct;
$nextcomment1 = $nextcomment;

// start a new page for invoice
//
$pdf->ezNewPage();

// store the current $nextproduct for Invoice, so it doesn't get
// overwritten above in Packing-Slip on subsequent call (for multipage invoices)
$nextproduct = $nextproduct2;
$nextcomment = $nextcomment2;
require(DIR_PDFOC_INCLUDE . 'templates/' . 'Invoice.php');
$nextproduct2 = $nextproduct;
$nextcomment2 = $nextcomment;
?>
