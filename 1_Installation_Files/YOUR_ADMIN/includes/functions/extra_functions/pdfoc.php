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

/**
 * 
 * @global array $db
 * @param int $order_status_id
 * @param int $key
 * @return string
 */
function zen_cfg_pull_down_order_statuses_plus_none($order_status_id, $key = '')
{
  global $db;
  $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

  $statuses_array[]= [
    'id' => '0',
    'text' => PDFOC_TEXT_NONE];

  $statuses = $db->Execute("SELECT orders_status_id, orders_status_name
                            FROM " . TABLE_ORDERS_STATUS . "
                            WHERE language_id = " . (int)$_SESSION['languages_id'] . "
                            ORDER BY orders_status_name");

  foreach ($statuses as $status) {
    $statuses_array[] = [
      'id' => $status['orders_status_id'],
      'text' => $status['orders_status_name'] . ' [' . $status['orders_status_id'] . ']'];
  }

  return zen_draw_pull_down_menu($name, $statuses_array, $order_status_id);
}

/**
 * 
 * @global array $db
 * @param int $order_status_id
 * @param int $language_id
 * @return string
 */
function zen_get_order_status_name_plus_none($order_status_id, $language_id = '')
{
  global $db;

  if ($order_status_id < 1) {
    return PDFOC_TEXT_NONE;  // This is defined in admin/includes/languages/english/extra_definitions/pdfoc.php
  }
  if (!is_numeric($language_id)) {
    $language_id = $_SESSION['languages_id'];
  }

  $status = $db->Execute("SELECT orders_status_name
                          FROM " . TABLE_ORDERS_STATUS . "
                          WHERE orders_status_id = " . (int)$order_status_id . "
                          AND language_id = " . (int)$language_id);

  return $status->fields['orders_status_name'] . ' [' . (int)$order_status_id . ']';
}

/**
 * 
 * @param int $template_id
 * @param int $key
 * @return string
 */
function pdfoc_cfg_pull_down_templates($template_id, $key = '')
{

  $name = (($key) ? 'configuration[' . $key . ']' : 'configuration_value');

  $directory = DIR_PDFOC_TEMPLATES;
  $resc = opendir($directory);

  $file_type_array[] = [
    'id' => '0',
    'text' => PDFOC_TEXT_NONE];
  while ($file = readdir($resc)) {

    $ext = strrchr($file, ".");

    if ($ext == ".php") {

//      $filename = str_replace('_', " ",$file);
      $filename = str_replace('-', '_', $file);
      $filename = str_replace($ext, "", $filename);
      $fileconst = 'PDFOC_TEMPLATE_NAME_' . strtoupper($filename);
      /* look for a constant for that filename; if exists, use it, otherwise use filename */
      /* (allows language-specific names to be displayed in the dropdown menus) */
      if (defined("$fileconst")) {
        $filename = constant($fileconst);
      } else {
        $filename = "MISTAKE! " . $filename;    // debugging code
      }
      $file_type_array[] = array('id' => $file, 'text' => $filename);
    } // EOIF $ext
  }  // EOWHILE $file

  return zen_draw_pull_down_menu($name, $file_type_array, $template_id);
}

/**
 * 
 * @param int $template_id
 * @return string
 */
function pdfoc_get_template_name($template_id)
{

  $directory = DIR_PDFOC_TEMPLATES;
  $resc = opendir($directory);

  if ($template_id == '0') {
    return PDFOC_TEXT_NONE;  // This is defined in admin/includes/languages/english/extra_definitions/pdfoc.php
  }

  while ($file = readdir($resc)) {

    $ext = strrchr($file, ".");

    if ($ext == ".php") {

//      $filename = str_replace('_', " ",$file);
      $filename = str_replace('-', '_', $file);
      $filename = str_replace($ext, "", $filename);
      $fileconst = 'PDFOC_TEMPLATE_NAME_' . strtoupper($filename);
      /* look for a constant for that filename; if exists, use it, otherwise use filename */
      /* (allows language-specific names to be displayed in the dropdown menus) */
      if (defined("$fileconst")) {
        $filename = constant($fileconst);
      } else {
        $filename = "MISTAKE! " . $filename;    // debugging code
      }

      if ($file == $template_id) {
        break;
      }
    } // EOIF $ext
  }  // EOWHILE $file

  return $filename;
}
