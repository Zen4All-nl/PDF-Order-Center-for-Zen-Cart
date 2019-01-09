<?php

/* PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 * By Grayson Morris, 2006
 * Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * admin/includes/functions/extra_functions/pdfoc.php
 *
 */

/** Function to clean up HTML strings for PDF files (in particular, invoices).
 * If you have funny characters showing up in your PDF file,
 * take a look at http://evolt.org/article/ala/17/21234/ ("A Simple Character Entity Chart")
 * and add your rewrite to the below
 * 
 * @param string $input
 * @return string
 */
function pdfoc_html_cleanup($input)
{

  // clean up money &euro; to €
  while (strstr($input, '&euro;'))
    $input = str_replace('&euro;', '€', $input);

  // clean up money &pound; to £
  while (strstr($input, '&pound;'))
    $input = str_replace('&pound;', '£', $input);

  // clean up spaces &nbsp; to ' '
  while (strstr($input, '&nbsp;'))
    $input = str_replace('&nbsp;', ' ', $input);

  // fix double quotes
  while (strstr($input, '&quot;'))
    $input = str_replace('&quot;', '"', $input);

  return $input;
}

/**
 * 
 * @param string $message
 */
function pdfoc_message_handler($message = '')
{ // reload the page, listing the error if specified
  if ($message) {
    header("Location: " . zen_href_link(FILENAME_PDFOC, 'mkey=' . $message));
  } else {
    header("Location: " . zen_href_link(FILENAME_PDFOC));
  }
  exit(0);
}

/**
 * 
 * @global type $pdf
 * @param type $color
 */
function pdfoc_change_color($color)
{
  global $pdf;

  list($r, $g, $b) = explode(',', $color);
  $pdf->setColor($r, $g, $b);
}

/**
 * 
 * @param date $date
 * @return int
 */
function pdfoc_verify_date($date)
{

  $error = 0;
  list($year, $month, $day) = explode('-', $date);

  if ((strlen($year) != 4) || !is_numeric($year)) {
    $error++;
  }
  if ((strlen($month) != 2) || !is_numeric($month)) {
    $error++;
  }
  if ((strlen($day) != 2) || !is_numeric($day)) {
    $error++;
  }

  return $error;
}
