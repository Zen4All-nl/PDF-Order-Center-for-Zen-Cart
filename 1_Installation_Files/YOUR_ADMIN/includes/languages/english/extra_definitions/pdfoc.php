<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * admin/includes/languages/english/pdfoc.php
 *
 */

// Admin menu settings
//
define('BOX_PDFOC', 'PDF Order Center');

// General settings for all PDFOC pages
//
define('PDFOC_HEADING_TITLE', 'PDF Order Center');
define('PDFOC_GENERAL_HELPTEXT', 'Select orders by using the selection control panel on the right-hand side of the page. Either specify selection criteria
                               and click "Submit Options", or check orders in the list and click "Use" (to select the checked orders) or "Omit" (to select
                               the UNCHECKED orders). The new set of selected orders will then be displayed in the orders list. <br /><br />
                               You can view details about an order by clicking somewhere in its row in the orders list. The details will be displayed
                               in the infobox at the top of the page.<br /><br />
                               Once the orders list contains the desired orders, select action criteria in the left-hand action control panel. To delete all the orders in the
                               orders list, click "Delete Selected Orders". To print documents for and/or change the status of the orders in the orders list,
                               select the desired printing and status update options and click "Submit Options". <br /><br />
                               Consult the PDFOC manual for more information.');
define('PDFOC_HELPTEXT_ICON', '?');
define('PDFOC_LINK_MAINPAGE','PDFOC Main Page');
define('PDFOC_LINK_MANUAL','Read the PDFOC Manual');
define('PDFOC_LINK_SUPER_ORDERS','To Super Orders');
define('PDFOC_TEXT_BATCH_PDF_PRINT', 'Batch PDF Print');
define('PDFOC_TEXT_HOVER_FOR_HELP','Hover the mouse over the yellow question mark fields to see help texts.<br />
                                    Click on any question mark to reset the forms.');
define('PDFOC_TEXT_NONE', 'None');


// This next section creates constant names for the template files, to allow for language-specific
// names in the dropdown menu in PDFOC
// YOU MUST MANUALLY INSURE THAT YOU HAVE NAMES HERE FOR ALL YOUR TEMPLATES, OR THEY WILL SHOW
// UP STRANGELY IN THE DROPDOWN MENU. The constant names are formed by uppercasing the template .php filename
// and converting any -'s into _'s.
define('PDFOC_TEMPLATE_NAME_CHRISTMASCARD','Christmas Card');
define('PDFOC_TEMPLATE_NAME_CREDIT','Credit');
define('PDFOC_TEMPLATE_NAME_GRID','Grid');
define('PDFOC_TEMPLATE_NAME_INVOICE','Invoice');
define('PDFOC_TEMPLATE_NAME_LABELS','Labels');
define('PDFOC_TEMPLATE_NAME_LABELWRITER','Labelwriter');
define('PDFOC_TEMPLATE_NAME_LABELWRITER_SHIPPING','Labelwriter Shipping');
define('PDFOC_TEMPLATE_NAME_NONE','None');
define('PDFOC_TEMPLATE_NAME_PACKING_SLIP','Packing Slip');
define('PDFOC_TEMPLATE_NAME_PACKING_SLIP_AND_INVOICE','Packing Slip & Invoice');
define('PDFOC_TEMPLATE_NAME_PRICE_QUOTE','Price Quote');


?>