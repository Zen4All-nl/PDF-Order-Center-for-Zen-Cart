<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 *  pdfoc_manual.php
 *
 */

require('includes/application_top.php');
require(DIR_PDFOC_INCLUDE . 'pdfoc_header.php');

echo PDFOC_FORMATTED_TEXT_MANUAL;

require(DIR_PDFOC_INCLUDE . 'pdfoc_footer.php');
require('includes/application_bottom.php');
?>
