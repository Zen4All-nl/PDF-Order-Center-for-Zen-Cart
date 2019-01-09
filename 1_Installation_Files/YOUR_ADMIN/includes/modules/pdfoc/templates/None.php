<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 *  None.php
 *
 */

// Used when you don't want to print

if ($pageloop == "0") {   // initialize pdf settings

  $pdf = new Cezpdf(A4,portrait);

} else {

}

?>
