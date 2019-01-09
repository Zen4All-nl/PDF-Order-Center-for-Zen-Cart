<?php

/**
 * PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 * By Grayson Morris, 2006
 * Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * admin/includes/extra_datafiles/pdfoc.php
 *
 */
// BPC directories
define('DIR_PDFOC_INCLUDE', DIR_WS_MODULES . 'pdfoc/');
define('DIR_PDFOC_PDF', DIR_PDFOC_INCLUDE . 'temp_pdf/');
define('DIR_PDFOC_FONTS', DIR_PDFOC_INCLUDE . 'fonts/');
define('DIR_PDFOC_TEMPLATES', DIR_PDFOC_INCLUDE . 'templates/');
// Main file
define('FILENAME_PDFOC', 'pdfoc');

// Temporary pdf file
define('FILENAME_PDFOC_PDF', 'batch_orders.pdf');

// Manual file
define('FILENAME_PDFOC_MANUAL', 'pdfoc_manual');

// Table for invoice numbers (OTFIN)
define('TABLE_ORDERS_INVOICES', DB_PREFIX . 'orders_invoices');

// RGB colors
define('PDFOC_BLACK', '0,0,0');
define('PDFOC_GREY', '0.9,0.9,0.9');
define('PDFOC_DARK_GREY', '0.7,0.7,0.7');

// PDF font sizes
define('PDFOC_GIANT_FONT_SIZE', '24');
define('PDFOC_COMPANY_HEADER_FONT_SIZE', '14');
define('PDFOC_SUB_HEADING_FONT_SIZE', '11');
define('PDFOC_GENERAL_FONT_SIZE', '11');
define('PDFOC_COMMENTS_FONT_SIZE', '9');
define('PDFOC_GENERAL_LEADING', '12');
define('PDFOC_PRODUCT_TOTALS_LEADING', '11');
define('PDFOC_PRODUCT_TOTALS_FONT_SIZE', '10');
define('PDFOC_PRODUCT_ATTRIBUTES_FONT_SIZE', '8');
define('PDFOC_GENERAL_FONT_COLOR', PDFOC_BLACK);
define('PDFOC_GENERAL_LINE_SPACING', '15');
