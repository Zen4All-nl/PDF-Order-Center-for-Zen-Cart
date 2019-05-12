<?php
/**
 * PDF Order Center 1.0 for Zen Cart v1.2.6d
 * By Grayson Morris, 2006
 * Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * admin/pdfoc.php
 *
 * PHP values:
 *  $_GET params : action, page, oID, mkey, form
 *  $_POST params : file_type, status, use_selected_orders,
 *                  omit_selected_orders, customer_data, order_numbers, startdate, enddate,
 *                  pull_status, orders_begin, orders_end, notify_comments, notify,
 *                  show_comments, orderlist[] to hold checked orders
 *  $_SESSION : ['pdfoc']['order_query'], ['pdfoc'][x] where x is a $_POST key
 *
 *
 * Three forms:
 * 1) pdfoc_selection: submits orderID info for selection and display of orders.
 * 2) pdfoc_action: submits action paramters for acting on the current order selection.
 * 3) pdfoc_deletion: deletes the current order selection.
 */
require('includes/application_top.php');
require(DIR_WS_CLASSES . 'currencies.php');
require(DIR_WS_CLASSES . 'order.php');
// move to configure file or DB?
define('PDFOC_BLOCK_ALL_ORDERS_QUERY_UPDATE', 'true');

// bof PDFOC orders statuses
// bof if (isset($_GET['pull_status'])
if (isset($_GET['pull_status']) && $_GET['pull_status'] != '0') {
  // the $_GET['pull_status'] overrules al other parameters and selects all orders having a certain order status
  // links to select and set $_GET['pull_status'] are inserted into pdfoc_body.php

  $orders_query = "SELECT o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text AS order_total
                   FROM " . TABLE_ORDERS . " o
                   LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON o.orders_id = ot.orders_id
                   WHERE ot.class = 'ot_total'
                   AND o.orders_status = '" . zen_db_input(zen_db_prepare_input($_GET['pull_status'])) . "'
                   ORDER BY orders_id DESC";
  $_SESSION['pdfoc']['orders_query'] = $orders_query; // needed?
} else {
// eof PDFOC orders statuses

  $all_orders_query = "SELECT o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text AS order_total
                       FROM " . TABLE_ORDERS . " o
                       LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON o.orders_id = ot.orders_id
                       WHERE ot.class = 'ot_total'
                       ORDER BY orders_id DESC";

// Step 1 :: Error checking
// First check for an error message
//
  if ($_GET['mkey']) { // there is an error message; abort any intended action and display error message instead
    // Note: this still displays last customer-selected orders in orders list
    $key = $_GET['mkey'];
    $message = $pdfoc_error[$key];  // $pdfoc_error[] is defined in admin/includes/languages/<language>/pdfoc.php
    // want to redisplay the selected order list
    if (isset($_SESSION['pdfoc']['orders_query'])) {

      $orders_query = $_SESSION['pdfoc']['orders_query'];
    } else { // just get all orders
      $orders_query = $all_orders_query;
    }
    $_GET['form'] = ''; // null out any specified action
  }

// Step 2 :: Process the submitted form
//
  if (isset($_GET['form'])) {

    switch ($_GET['form']) {
      case 'selection':  // ------------- BOF selection -----------------------------------
        // Determine which orders have been selected; if none specified, let
        // the user know.
        // First initialize some query variables so they don't give problems in the
        // query if the corresponding selection method isn't set
        //
        $cdata_query = '1';
        $order_checked_query = '1';
        $order_numbers_query = '1';
        $date_query = '1';
        $status_query = '1';
        $orders_query = "SELECT o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text AS order_total
                         FROM " . TABLE_ORDERS . " o
                         LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON o.orders_id = ot.orders_id
                         WHERE ot.class = 'ot_total'";
        $order_checked_array = [];
        $order_unchecked_array = [];
        $checked_exist = 0;

        // Determine which orders (if any) have been checked in the orders list
        //
        foreach ((array)$_POST['orderlist'] as $key => $value) {
          $order_checked_array[] = zen_db_prepare_input($value);
          $checked_exist++;
        }

        // Option 0 :: the user has checked orders but pressed "submit" or "enter"
        // instead of "use" or "omit". In this case redisplay the previous query
        // and notify the user of the confusion. This prevents accidentally, say,
        // deleting orders that the user didn't intend.
        //
        if ($checked_exist > 0 && !array_key_exists('omit_selected_orders', $_POST) && !array_key_exists('use_selected_orders', $_POST)) {

          pdfoc_message_handler('PDFOC_ERROR_CONFLICTING_SPECIFICATION');
        }

        // Option 1 :: the user has pressed "use" or "omit".
        //
        if ($checked_exist == 0 && array_key_exists('omit_selected_orders', $_POST)) { // none checked, pressed Omit, so show all; "order by" clause gets added below
          $orders_query = "SELECT o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text AS order_total
                           FROM " . TABLE_ORDERS . " o
                           LEFT JOIN " . TABLE_ORDERS_TOTAL . " ot ON o.orders_id = ot.orders_id
                           WHERE ot.class = 'ot_total'";
        } elseif ($checked_exist == 0 && array_key_exists('use_selected_orders', $_POST)) { // none checked, pressed Use, so let user know
          pdfoc_message_handler('PDFOC_NO_ORDERS');
        } elseif ($checked_exist > 0 && array_key_exists('use_selected_orders', $_POST)) { // use checked orders
          $order_checked = implode(',', $order_checked_array);
          $order_checked_query = "o.orders_id IN (" . zen_db_input($order_checked) . ")";
          $orders_query .= " AND (" . $order_checked_query . ")";
        } elseif ($checked_exist > 0 && array_key_exists('omit_selected_orders', $_POST)) {  // use UNCHECKED orders
          // first get the previously selected orders so we don't
          // suddenly include things in the begin/end range that
          // weren't included before
          //
          if (isset($_SESSION['pdfoc']['orders_query'])) {  // get previous query if there was one
            $orders_query_check = $_SESSION['pdfoc']['orders_query'];
          } else { // just get all orders
            $orders_query = $all_orders_query;
          }
          $orders = $db->Execute($orders_query_check);

          foreach ($orders as $order) {
            $oNos[$order['orders_id']] = true;
          }

          $ocount = 0;

          for ($i = (int)$_POST['orders_begin']; $i <= (int)$_POST['orders_end']; $i++) {
            if (!in_array($i, $order_checked_array) && isset($oNos[$i])) {
              $order_unchecked_array[] = $i;
              $ocount++;
            }
          }
          // Just in case the user decided to omit everything....
          //
          if ($ocount == 0) {
            pdfoc_message_handler('PDFOC_NO_ORDERS');
          }

          $order_unchecked = implode(',', $order_unchecked_array);
          $order_unchecked_query = "o.orders_id in (" . zen_db_input($order_unchecked) . ")";
          $orders_query .= " and (" . $order_unchecked_query . ")";
        } else {  // didn't select orders from orders list
          // Option 2 :: the user has  pressed the "submit" button or the "enter" key.
          //
          if (isset($_POST['customer_data']) && $_POST['customer_data'] != '') {  // search for orders containing this data
            $cdata = zen_db_input(zen_db_prepare_input($_POST['customer_data']));
            $cdata_query = "(o.customers_city LIKE '%" . $cdata . "%') OR (o.customers_postcode LIKE '%" . $cdata . "%') OR (o.billing_name LIKE '%" . $cdata . "%') OR (o.billing_company LIKE '%" . $cdata . "%') OR (o.billing_street_address LIKE '%" . $cdata . "%') OR (o.delivery_city LIKE '%" . $cdata . "%') OR (o.delivery_postcode LIKE '%" . $cdata . "%') OR (o.delivery_name LIKE '%" . $cdata . "%') OR (o.delivery_company LIKE '%" . $cdata . "%') OR (o.delivery_street_address LIKE '%" . $cdata . "%') OR (o.billing_city LIKE '%" . $cdata . "%') OR (o.billing_postcode LIKE '%" . $cdata . "%') OR (o.customers_email_address LIKE '%" . $cdata . "%') OR (o.customers_name LIKE '%" . $cdata . "%') OR (o.customers_company LIKE '%" . $cdata . "%') OR (o.customers_street_address  LIKE '%" . $cdata . "%') OR (o.customers_telephone LIKE '%" . $cdata . "%') OR (o.ip_address LIKE '%" . $cdata . "%')";

            $orders_query .= " AND (" . $cdata_query . ")";
          }  // EOIF isset($_POST['customer_data'])


          if (isset($_POST['order_numbers']) && $_POST['order_numbers'] != '') {  // use the orders whose ids are listed
            // Check invoice number(s) entered and convert to comma-separated list.
            $order_numbers = zen_db_prepare_input($_POST['order_numbers']);
            $arr_no = explode(',', $order_numbers);

            foreach ($arr_no as $key => $value) { // if ranges were entered, convert them to comma-separated list
              $arr_no[$key] = trim($value);

              // error check for stray characters in the order #'s
              if (strspn($value, "1234567890-") != strlen($value)) {
                pdfoc_message_handler('PDFOC_ERROR_BAD_ORDER_NUMBERS');
              }

              if (substr_count($arr_no[$key], '-') > 0) { // this was a range of values, so get them all
                // error check for too many dashes
                if (substr_count($arr_no[$key], '-') > 1) {
                  pdfoc_message_handler('PDFOC_ERROR_BAD_ORDER_NUMBERS');
                }

                $temp_range = explode('-', $arr_no[$key]);
                $arr_no[$key] = implode(',', range((int)$temp_range[0], (int)$temp_range[1]));
              } // EOIF substr_count
            }  // EOFOREACH $arr_no

            $order_numbers = implode(',', $arr_no);
            $order_numbers_query = "o.orders_id IN (" . zen_db_input($order_numbers) . ")";

            $orders_query .= " AND (" . $order_numbers_query . ")";
          }  // EOIF isset($_POST['order_numbers'])


          if (isset($_POST['startdate']) && $_POST['startdate'] != '' && isset($_POST['enddate']) && $_POST['enddate'] != '') { // use orders between the specified dates
            if ((strlen($_POST['startdate']) != 10) || pdfoc_verify_date($_POST['startdate'])) {
              pdfoc_message_handler('PDFOC_ERROR_BAD_DATE');
            }
            if ((strlen($_POST['enddate']) != 10) || pdfoc_verify_date($_POST['enddate'])) {
              pdfoc_message_handler('PDFOC_ERROR_BAD_DATE');
            }

            $startdate = zen_db_prepare_input($_POST['startdate']);
            $enddate = zen_db_prepare_input($_POST['enddate']);
            $date_query = "o.date_purchased BETWEEN '" . zen_db_input($startdate) . "' AND '" . zen_db_input($enddate) . " 23:59:59'";

            $orders_query .= " AND (" . $date_query . ")";
          } // EOIF isset($_POST['startdate'])


          if (isset($_POST['pull_status']) && $_POST['pull_status'] != '0') {

            $status_query = "o.orders_status = '" . zen_db_input(zen_db_prepare_input($_POST['pull_status'])) . "'";

            $orders_query .= " AND " . $status_query;
          } // EOIF isset($_POST['pull_status'])
        } // EOIF $checked_exist
        // however we got them, we have our query conditions; now
        // finish off the query and check to see if any orders have
        // been selected.
        //
        $orders_query .= " ORDER BY orders_id DESC";

        $orders = $db->Execute($orders_query);
        if (!$orders->RecordCount() > 0) {
          pdfoc_message_handler('PDFOC_NO_ORDERS');
        }

        // save the orders query so it can be
        // retrieved when reloading the page (on an error, on a refresh, or
        // on delete_confirm, for example)
        //
        $_SESSION['pdfoc']['orders_query'] = $orders_query;

        break;   // ------------------- EOF selection --------------------------------------

      case 'action':  // ------------- BOF action -----------------------------------------
        // get the currently selected orders to act upon
        //
        if (isset($_SESSION['pdfoc']['orders_query'])) {
          $orders_query = $_SESSION['pdfoc']['orders_query'];
        } else { // just get all orders
          $orders_query = $all_orders_query;
        }

        // determine what action to take
        // order of precedence if many things are selected (the first that matches will be the winner):
        //
        // 1. refresh
        // 2. print credit if credit is confirmed (only occurs if user clicks on "yes" link at top); will also change status if was provided initially
        // 3. ask for credit confirmation if credit has been checked and file type is "Credit"
        // 4. if a file type (not "Credit") and/or status has been specified, print and/or update status

        if (isset($_GET['action']) && ($_GET['action'] == 'refresh' || $_GET['action'] == 'credit_confirm_no')) {
          $action = 'refresh';
        } elseif (isset($_GET['action']) && $_GET['action'] == 'credit_confirm') {

          $action = 'credit_confirm';

          // restore the post vars (whether to print order number, telephone, etc.)
          //
          foreach ($_SESSION['pdfoc'] as $key => $value) {
            $_POST[$key] = $value;
          }
        } elseif (isset($_POST['file_type']) && $_POST['file_type'] == 'Credit.php') {
          $action = 'credit_request';
        } elseif ((isset($_POST['file_type']) && $_POST['file_type'] != '0' && $_POST['file_type'] != 'Credit.php') || (isset($_POST['status']) && $_POST['status'] != '0')) {
          $action = 'status_andor_print';
        }

        // Now go do it
        //
        if (isset($action)) {
          switch ($action) {

            case 'refresh':   // this is just a refresh from selecting an order in orders list, or
              // aborting a delete or credit

              break;

            case 'credit_request':   // Have user confirm the credit
              // save out all post vars so they'll be available
              // to the Credit template
              //
              foreach ($_POST as $key => $value) {
                $_SESSION['pdfoc'][$key] = $value;
              }
              // check to see if any order(s) does not yet have a
              // credit number. If there is one, pop up a confirmation
              // message. If all selected orders already have
              // credit numbers, skip the message.
              //
              $orders = $db->Execute($orders_query);
              $no_credit = 0;

              foreach ($orders as $order) {

                $verify_credit = $db->Execute("SELECT *
                                               FROM " . TABLE_ORDERS_INVOICES . "
                                               WHERE invoice_type = 'CR'
                                               AND orders_id = " . (int)$order['orders_id']);

                if ($verify_credit->EOF) { // this order doesn't have a credit yet
                  $no_credit++;
                  break;
                }
              }
              if ($no_credit > 0) {
                $message = PDFOC_MESSAGE_CREDIT_ARE_YOU_SURE .
                    '<br><a href="' . zen_href_link(FILENAME_PDFOC, 'form=action&action=credit_confirm') . '">' . PDFOC_TEXT_YES . '</a>' .
                    '<br><a href="' . zen_href_link(FILENAME_PDFOC, 'form=action&action=credit_confirm_no') . '">' . PDFOC_TEXT_NO . '</a>';
              } else {
                zen_redirect(FILENAME_PDFOC . '?form=action&action=credit_confirm');
              }
              break;
            case 'status_andor_print':   // Status update and/or printing options were specified
            case 'credit_confirm':

              // --- BEGIN STATUS UPDATE AND NOTIFICATION --------------------------------------------------
              if (isset($_POST['status']) && $_POST['status'] != '0') { // need to update status
                // reset orders query result to beginning
                //
                $orders = $db->Execute($orders_query);
                $status = zen_db_prepare_input($_POST['status']);
                $comments = zen_db_prepare_input($_POST['comments']);

                foreach ($orders as $item) { // loop over all specified orders
                  $order = new order($item['orders_id']);
                  $oID = $item['orders_id'];

                  $customer_notified = 0;

                  if (($_POST['notify_comments']) && ($comments != '')) {
                    $notify_comments = sprintf(PDFOC_EMAIL_TEXT_COMMENTS_UPDATE, pdfoc_html_cleanup($comments));
                  } else {
                    $notify_comments = '';
                  }

                  if ($_POST['notify']) { // send e-mail to customer informing him/her of new order status
                    $email_text_subject = sprintf($pdfoc_subject[(int)$status], $oID);  // $pdfoc_subject[] is defined in admin/includes/languages/<language>/pdfoc.php
                    $email = sprintf(PDFOC_EMAIL_SALUTATION, $order->customer['name']);
                    $email .= "\n\n" .
                        $email_text_subject . ".\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $item['orders_id'], 'SSL') . "\n" .
                        PDFOC_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($order->info['date_purchased']) . "\n\n" . $notify_comments . PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_IF_QUESTIONS . PDFOC_EMAIL_SIGNOFF . STORE_NAME . "\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n";

                    $email_html['EMAIL_SUBJECT'] = "\n\n";
                    $email_html['EMAIL_MESSAGE_HTML'] = sprintf(PDFOC_EMAIL_SALUTATION, $order->customer['name']) . "\n\n" .
                        $email_text_subject . ".\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_TEXT_INVOICE_URL . ' <a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $item['orders_id'], 'SSL') . '">' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $item['orders_id'], 'SSL') . '</a>' . "\n" .
                        PDFOC_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($order->info['date_purchased']) . "\n\n" . $notify_comments . PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_IF_QUESTIONS . PDFOC_EMAIL_SIGNOFF . STORE_NAME . "\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n";

                    zen_mail($order->customer['name'], $order->customer['email_address'], $email_text_subject, $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $email_html);

                    if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
                      zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . $email_text_subject, $email, STORE_NAME, EMAIL_FROM);
                    }
                    $customer_notified = '1';
                  }  // EOIF $_POST['notify']

                  if ($status != $order->info['orders_status'] || $customer_notified == '1') { // update order status if new status specified or if customer notified
                    $db->Execute("UPDATE " . TABLE_ORDERS . "
                                  SET orders_status = " . zen_db_input($status) . ",
                                      last_modified = now()
                                  WHERE orders_id = " . (int)$item['orders_id']);
                    $db->Execute("INSERT INTO " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments)
                                  VALUES (" . (int)$item['orders_id'] . ", " . zen_db_input($status) . ", now(), " . (int)$customer_notified . ", '" . $comments . "')");
                  } // EOIF $_POST['status'] != $order->info['orders_status']
                } // EOWHILE (!$orders->EOF)
              } // EOIF isset($_POST['status'])
              // --- END STATUS UPDATE AND NOTIFICATION --------------------------------------------
              // --- BEGIN PRINTING ----------------------------------------------------------------
              if (isset($_POST['file_type']) && $_POST['file_type'] != "0") { // time to print
                // Basic error handling, initialization, and avoid-a-timeout setup
                //
                if (!is_writeable(DIR_PDFOC_PDF)) {
                  pdfoc_message_handler('PDFOC_SET_PERMISSIONS');
                }

                $pageloop = '0';
                $time0 = time();

                require(DIR_PDFOC_INCLUDE . 'Cezpdf.php');

                // Load the specified template (e.g. invoice or label). Note: $pdf is instantiated in
                // the template file when $pageloop = 0, which is the case here.
                require(DIR_PDFOC_INCLUDE . 'templates/' . $_POST['file_type']);

                $pageloop = '1';


                $numpage = 0;
                $numrecords = 0;
                $nexttotal = $nextproduct = $nextproduct1 = $nextproduct2 = 0; // last two are used in combined Packing Slip and Invoice template
                $nextcomment = $nextcomment1 = $nextcomment2 = 0; // last two are used in combined Packing Slip and Invoice template
                $secondpage = false;

                if ($_POST['show_comments']) {
                  $show_comments = true;
                } else {
                  $show_comments = false;
                }

                // reset orders query result to beginning
                //
                $orders = $db->Execute($orders_query);
                foreach ($orders as $item) { // loop over all specified orders
                  $order = new order($item['orders_id']);
                  $oID = $item['orders_id'];

                  if ($numpage != 0) {
                    $pdf->EzNewPage();
                  }    // insert a new page into the existing pdf document (not on the first iteration)
                  // for each order, reload the specified template. The template file
                  // contains the order-specific instructions, such as printing the invoice number
                  // and order date. That's why it has to be reloaded for every order.
                  //
                  require(DIR_PDFOC_INCLUDE . 'templates/' . $_POST['file_type']);

                  $numpage++;
                  if ($_POST['file_type'] == 'Labels.php') {
                    $numrecords += $numlabel;
                  } elseif ($secondpage === false) {
                    $numrecords++;
                  }

                  if ($_POST['file_type'] == 'Packing-Slip-and-Invoice.php') {
                    $numpage++;
                  }

                  // Send fake header to avoid timeout, got this trick from phpMyAdmin
                  //
                  $time1 = time();
                  if ($time1 >= $time0 + 30) {
                    $time0 = $time1;
                    header('X-bpPing: Pong');
                  }

                  if ($secondpage === true) {    // continue printing this order
                    continue;
                  }
                } // EOWHILE !$orders->EOF

                $pdf_code = $pdf->output();  // get pdf stream for this order as a string
                // append the pdf page for this order to the pdf file
                $fname = DIR_PDFOC_PDF . FILENAME_PDFOC_PDF;
                if ($fp = fopen($fname, 'w')) {
                  fwrite($fp, $pdf_code);
                  fclose($fp);
                } else {
                  pdfoc_message_handler('PDFOC_FAILED_TO_OPEN');
                }
              } // EOIF isset($_POST['file_type'])
              // --- END PRINTING ----------------------------------------------------------------
              // Notify admin of print/update success.
              //
              if (isset($_POST['status']) && $_POST['status'] != '0') {
                $message .= PDFOC_MESSAGE_STATUS_WAS_UPDATED;
                if ($customer_notified == '1') {
                  $message .= PDFOC_MESSAGE_EMAIL_SENT;
                } else { // add period
                  $message .= '.';
                }
              }
              if (isset($_POST['file_type']) && $_POST['file_type'] != '0') {
                $message .= '<br>' . sprintf(PDFOC_MESSAGE_SUCCESS, $numpage, $numrecords, $fname);
              }
              break;
          } // EOSWITCH $action
        } // EOIF isset($action)
        break;   // ------------- EOF action -----------------------------------------
      case 'deletion':   // ------------- BOF deletion -------------------------------
        // get the currently selected orders to act upon
        //
        if (isset($_SESSION['pdfoc']['orders_query'])) {

          $orders_query = $_SESSION['pdfoc']['orders_query'];
        } else { // surely you don't want to delete ALL orders......note this doesn't
          // protect the user if all orders really have been selected
          pdfoc_message_handler('PDFOC_ALL_SELECTED_FOR_DELETE');
        }

        $orders = $db->Execute($orders_query);
        if ((PDFOC_BLOCK_ALL_ORDERS_QUERY_UPDATE == 'true') && ($orders_query == $all_orders_query)) {
          exit('Blocked all orders query update! Press the browser back button and check your query.');
        }
        foreach ($orders as $order) {
          $oID = zen_db_prepare_input($order['orders_id']);
          zen_remove_order($oID, $_POST['restock']);
        } // EOWHILE !orders->EOF
        // Notify admin of delete success.
        //
        $message = PDFOC_MESSSAGE_ORDERS_WERE_DELETED;

        // Now reset the orders list to display all orders
        //
        $orders_query = $all_orders_query;
        $_SESSION['pdfoc']['orders_query'] = $orders_query;

        break;    // ------------- EOF deletion --------------------------------------
    }  // EOSWITCH
  } else { // no form submitted, just get all orders
    $orders_query = $all_orders_query;
    $_SESSION['pdfoc']['orders_query'] = $orders_query;
  } // EOIF isset($_GET['form']
// bof PDFOC orders statuses
}
// eof if (isset($_GET['pull_status'])
// eof PDFOC orders statuses
// TESTING
//
//$message .= '<br><br/>file_type:' . $_POST['file_type'] . '<br><br>';
//foreach ($_POST['orderlist'] as $k => $v ) { $message .= "POST[orderlist][$k]=$v  ";  }
if ($orders_query == $all_orders_query) {
  $messageStack->add($pdfoc_error['PDFOC_NO_SELECTION'], 'error');
}
// output the html for the batch_print admin page -- note this includes php for order listing
//
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/css/pdfoc.css">
    <link rel="stylesheet" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
    <link rel="stylesheet" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
    <script src="includes/menu.js"></script>
    <script src="includes/general.js"></script>
    <script>
      function init() {
          cssjsmenu('navbar');
          if (document.getElementById) {
              var kill = document.getElementById('hoverJS');
              kill.disabled = true;
          }
      }
    </script>
    <script>
      function addAutoComment(frmobj) {
          frmobj.comments.value = ""
          for (i = 0; i < frmobj.autocomment.length; i++) {
              if (frmobj.autocomment[i].checked) {
                  frmobj.comments.value += frmobj.autocomment[i].value;
              }
          }
      }
    </script>
    <script src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
    <script>
      var dateAvailable = new ctlSpiffyCalendarBox("dateAvailable", "pdfoc_selection", "startdate", "btnDate1", "", scBTNMODE_CUSTOMBLUE);
      var dateAvailable1 = new ctlSpiffyCalendarBox("dateAvailable1", "pdfoc_selection", "enddate", "btnDate2", "", scBTNMODE_CUSTOMBLUE);
    </script>
  </head>
  <body onload="init()">
    <!-- header //-->
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <!-- header_eof //-->
    <div id="spiffycalendar" class="text"></div>
    <!-- body //-->
    <div class="container-fluid">
      <!-- body_text //-->
      <h1><?php echo PDFOC_HEADING_TITLE; ?></h1>
      <div class="row">
        <span class="dataTableHelptext"><a class="helptextPopup" id="pdfocGeneralHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_GENERAL_HELPTEXT; ?></span></a></span>
        <?php echo PDFOC_TEXT_HOVER_FOR_HELP; ?>
      </div>
      <div class="row text-right"><a href="<?php echo zen_href_link(FILENAME_PDFOC_MANUAL); ?>" class="btn btn-default" role="button"><?php echo PDFOC_LINK_MANUAL; ?></a></div>
      <?php
      $orders_statuses = [];
      $orders_status_array = [];
      $orders_status = $db->Execute("SELECT orders_status_id, orders_status_name
                                     FROM " . TABLE_ORDERS_STATUS . "
                                     WHERE language_id = " . (int)$_SESSION['languages_id']);
      $orders_statuses[] = [
        'id' => 0,
        'text' => 'None'];

      foreach ($orders_status as $item) {
        $orders_statuses[] = [
          'id' => $item['orders_status_id'],
          'text' => $item['orders_status_name'] . ' [' . $item['orders_status_id'] . ']'];
        $orders_status_array[$item['orders_status_id']] = $item['orders_status_name'];
      }

      $directory = DIR_PDFOC_TEMPLATES;
      $resc = opendir($directory);

      if (!$resc) {
        echo "Problem opening directory $directory. Error: $php_errormsg";
        exit;
      }

      $file_type_array[] = [
        'id' => '0',
        'text' => PDFOC_TEXT_NONE];  // This constant is defined in admin/includes/languages/english/extra_definitions/pdfoc.php
      while ($file = readdir($resc)) {

        $ext = strrchr($file, '.');

        if ($ext == '.php') {

          $filename = str_replace('-', '_', $file);
          $filename = str_replace($ext, '', $filename);
          $fileconst = 'PDFOC_TEMPLATE_NAME_' . strtoupper($filename);
          /* look for a constant for that filename; if exists, use it, otherwise use filename */
          /* (allows language-specific names to be displayed in the dropdown menus) */
          if (defined("$fileconst")) {
            $filename = constant($fileconst);
          } else {
            $filename = 'MISTAKE! ' . $filename;        // debugging code
//      $filename = str_replace('_', " ", $filename);
          }
          $file_type_array[] = [
            'id' => $file,
            'text' => $filename];
        } // EOIF $ext
      }  // EOWHILE $file
      ?>
      <div class="row">

        <!--// This is the options/actions section on LH half of page //-->

        <div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 configurationColumnLeft">
            <?php if ($message) { ?>
            <div class="row pdfocMessageHeaderRow pdfocMessageHeaderContent" id="pdfocProgramMessage"><?php echo PDFOC_PROGRAM_MESSAGE; ?></div>
            <div class="row pdfocMessageRow pdfocMessageContent"><?php echo $message; ?></div>
            <div class="row"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></div>
            <?php
          }
          if ((PDFOC_BLOCK_ALL_ORDERS_QUERY_UPDATE == 'true') && ($_SESSION['pdfoc']['orders_query'] == $all_orders_query)) {
            // protect admin from updating all orders (when no selection was made)
            $pdfoc_buttons_disabled = 'disabled="disabled"';
          } else {
            $pdfoc_buttons_disabled = '';
          }
          ?>
          <table class="table table-condensed">
              <?php echo zen_draw_form('pdfoc_deletion', FILENAME_PDFOC, 'form=deletion', 'post', 'class="form-horizontal"', true); ?>
            <tr class="dataTableRowSelected">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocDeleteOrdersHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_DELETE_ORDERS_HELPTEXT; ?></span></a></td>
              <td><input <?php echo $pdfoc_buttons_disabled; ?> type="submit" name="delete" value="<?php echo PDFOC_DELETE_ORDERS; ?>" onclick="return confirm('<?php echo PDFOC_MESSAGE_DELETE_ARE_YOU_SURE; ?>')" class="btn btn-default btn-sm"></td>
              <td>
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('restock', true, (PDFOC_RESTOCK_DEFAULT == 'true')); ?><?php echo PDFOC_TEXT_RESTOCK; ?></label>
                </div>
              </td>
            </tr>
            <?php echo '</form>'; ?>
            <?php echo zen_draw_form('pdfoc_action', FILENAME_PDFOC, 'form=action', 'post', 'class="form-horizontal"', true); ?>
            <tr class="dataTableRowSelected">
              <td class="dataTableHelptext text-right"><a class="helptextPopup" id="pdfocTextSubmitHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_TEXT_SUBMIT_HELPTEXT; ?></span></a></td>
              <td colspan="2"><input <?php echo $pdfoc_buttons_disabled; ?> type="submit" name="submit_action" value="<?php echo PDFOC_TEXT_SUBMIT; ?>" <?php echo defined('PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE') ? 'onclick="return confirm(\'' . PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE . '\')"' : ''; ?> class="btn btn-default btn-sm"></td>
            </tr>
          </table>
          <table id="pdfocPrintingOptions" class="table table-condensed<?php echo PDFOC_HIDE_PRINTING_OPTIONS == 'true' ? ' pdfocHidden' : ''; ?>">
            <tr class="dataTableRowSelected">
              <td class="dataTableContent" colspan="7"><b><?php echo PDFOC_PRINTING_OPTIONS; ?></b></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocChooseTemplateHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_CHOOSE_TEMPLATE_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_CHOOSE_TEMPLATE, 'file_type', 'class="control-label"'); ?></td>
              <td colspan="5"><?php echo zen_draw_pull_down_menu('file_type', $file_type_array, PDFOC_TEMPLATE_DEFAULT, 'class="form-control"'); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocInvoiceFinalOrPreviewHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_INVOICE_FINAL_OR_PREVIEW_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_INVOICE_FINAL_OR_PREVIEW, 'invoice_mode', 'class="control-label"'); ?></td>
              <td colspan="5" class="dataTableContent">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('invoice_mode', 'preview', (PDFOC_FINAL_PREVIEW_DEFAULT == 'preview')); ?><?php echo PDFOC_INVOICE_PREVIEW; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('invoice_mode', 'final', (PDFOC_FINAL_PREVIEW_DEFAULT == 'final')); ?><?php echo PDFOC_INVOICE_FINAL; ?></label>
                </div>
              </td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocBillingOrDeliveryHelptex" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_BILLING_OR_DELIVERY_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_BILLING_OR_DELIVERY, 'address', 'class="control-label"'); ?></td>
              <td colspan="5" class="dataTableContent">
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('address', 'delivery', (PDFOC_LABELADDRESS_DEFAULT == 'delivery')) . PDFOC_DELIVERY; ?></label>
                </div>
                <div class="radio">
                  <label><?php echo zen_draw_radio_field('address', 'billing', (PDFOC_LABELADDRESS_DEFAULT == 'billing')) . PDFOC_BILLING; ?></label>
                </div>
              </td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocLabelToStartOnHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_LABEL_TO_START_ON_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_LABEL_TO_START_ON, 'startpos', 'class="control-label"'); ?></td>
              <td colspan="5" class="dataTableContent"><?php echo zen_draw_input_field('startpos', '1', 'class="form-control"'); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintOrderDateHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_ORDER_DATE_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_ORDER_DATE, 'show_order_date', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_order_date', true, (PDFOC_ORDERDATE_DEFAULT == 'true')); ?></td>
              <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_COMMENTS_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_COMMENTS, 'show_comments', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_comments', true, (PDFOC_PRINTCOMMENTS_DEFAULT == 'true')); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintTelNoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_TEL_NO_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_TEL_NO, 'show_phone', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_phone', true, (PDFOC_TELEPHONE_DEFAULT == 'true')); ?></td>
              <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintEmailAddressHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_EMAIL_ADDRESS_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_EMAIL_ADDRESS, 'show_email', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_email', true, (PDFOC_EMAIL_DEFAULT == 'true')); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintPaymentInfoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_PAYMENT_INFO_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_PAYMENT_INFO, 'show_pay_method', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_pay_method', true, (PDFOC_PAYMENTINFO_DEFAULT == 'true')); ?></td>
              <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintCcNoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_CC_NO_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_PRINT_CC_NO, 'show_cc', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('show_cc', true, (PDFOC_CCNO_DEFAULT == 'true')); ?></td>
            </tr>
            <tr>
              <td colspan="7"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
            </tr>
            <tr class="dataTableRowSelected">
              <td class="dataTableContent" colspan="7"><b><?php echo PDFOC_ORDER_STATUS_OPTIONS; ?></b></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocAutoChangeStatusHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_AUTO_CHANGE_STATUS_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_AUTO_CHANGE_STATUS, 'status', 'class="control-label"'); ?></td>
              <td colspan="5"><?php echo zen_draw_pull_down_menu('status', $orders_statuses, (PDFOC_NEWSTATUS_DEFAULT == '' ? 0 : PDFOC_NEWSTATUS_DEFAULT), 'class="form-control"'); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td></td>
              <td class="dataTableContent" colspan="6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('autocomment', PDFOC_COMMENTS_AUTO_SHIPPED_TEXT, false, '', 'onClick="addAutoComment(this.form)"'); ?><?php echo PDFOC_COMMENTS_AUTO_SHIPPED; ?></label>
                </div>
              </td>
            </tr>
            <tr class="dataTableRow">
              <td></td>
              <td class="dataTableContent" colspan="6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('autocomment', PDFOC_COMMENTS_AUTO_CC_DECLINED_TEXT, false, '', 'onClick="addAutoComment(this.form)"'); ?><?php echo PDFOC_COMMENTS_AUTO_CC_DECLINED; ?></label>
                </div>
              </td>
            </tr>
            <tr class="dataTableRow">
              <td></td>
              <td class="dataTableContent" colspan="6">
                <div class="checkbox">
                  <label><?php echo zen_draw_checkbox_field('autocomment', PDFOC_COMMENTS_AUTO_BACKORDER_TEXT, false, '', 'onClick="addAutoComment(this.form)"'); ?><?php echo PDFOC_COMMENTS_AUTO_BACKORDER; ?></label>
                </div>
              </td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_COMMENTS_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_COMMENTS, 'comments', 'class="control-label"'); ?></td>
              <td colspan="5"><?php echo zen_draw_textarea_field('comments', 'soft', '30', '5', '', 'class="form-control"', false); ?></td>
            </tr>
            <tr class="dataTableRow">
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocNotifyCustomerHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_NOTIFY_CUSTOMER_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_NOTIFY_CUSTOMER, 'notify', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('notify', true, (PDFOC_NOTIFYCUSTOMER_DEFAULT == 'true')); ?></td>
              <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocNotifyCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_NOTIFY_COMMENTS_HELPTEXT; ?></span></a></td>
              <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_NOTIFY_COMMENTS, 'notify_comments', 'class="control-label"'); ?></td>
              <td><?php echo zen_draw_checkbox_field('notify_comments', true, (PDFOC_NOTIFYCOMMENTS_DEFAULT == 'true')); ?></td>
            </tr>
          </table>
          <?php echo '</form>'; ?>
        </div>

        <!--// This is the order list section on RH half of page //-->

        <div class="col-xs-12 col-sm-12 col-md-8 col-lg-8 configurationColumnRight"><!--bof PDFOC orders statuses //-->
            <?php if (is_array(zen_get_orders_status())) { ?>
            <div id="orderstatuses">
              <strong>&lt;=</strong> <a class="btn btn-xs btn-default<?php echo (((int)$_GET['pull_status'] == 0) ? ' selected' : ''); ?>" href="<?php echo zen_href_link(FILENAME_PDFOC, 'pull_status=0'); ?>"><?php echo PDFOC_TEXT_RESET; ?></a>
              <?php foreach (zen_get_orders_status() as $value) { ?>
                <span>|</span><a class="btn btn-xs btn-default<?php echo(((int)$_GET['pull_status'] == $value['id']) ? ' selected' : ''); ?>" href="<?php echo zen_href_link(FILENAME_PDFOC, 'pull_status=' . $value['id']); ?>"><?php echo $value['text']; ?></a>
              <?php } ?>
            </div>
          <?php } ?>
          <!--eof PDFOC orders statuses //-->
          <?php echo zen_draw_form('pdfoc_selection', FILENAME_PDFOC, 'form=selection', 'post', '', true);
          ?>
          <table class="table table-condensed">

            <?php
// Split page when more results than fit on one page
//
// First: reset page when page is unknown
//
            if (($_GET['page'] == '' || $_GET['page'] <= 1) && $_GET['oID'] != '') {
              $check_page = $db->Execute($orders_query);
              $check_count = 1;
              if ($check_page->RecordCount() > PDFOC_ORDERLISTINGMAX_DEFAULT) {
                foreach ($check_page as $item) {
                  if ($item['orders_id'] == $_GET['oID']) {
                    break;
                  }
                  $check_count++;
                }
                $_GET['page'] = round((($check_count / PDFOC_ORDERLISTINGMAX_DEFAULT) + (fmod_round($check_count, PDFOC_ORDERLISTINGMAX_DEFAULT) != 0 ? .5 : 0)), 0);
              } else {
                $_GET['page'] = 1;
              }
            }
// get db results for the current page
// and display selected order row as such
//
            $orders_split = new splitPageResults($_GET['page'], PDFOC_ORDERLISTINGMAX_DEFAULT, $orders_query, $orders_query_numrows);
            $orders = $db->Execute($orders_query);

// Set up the top box displaying order information for the selected order in
// the orders list below
// Get the selected order
//
            foreach ($orders as $item) {
              if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $item['orders_id']))) && !isset($oInfo)) {
                $oInfo = new objectInfo($item);
              }
            }

            if (isset($oInfo) && is_object($oInfo)) {

              $order = new order($oInfo->orders_id);

              // add in OTFIN stuff here: verify_credit, verify_debit
              //
              $verify_debit = $db->Execute("SELECT *
                                            FROM " . TABLE_ORDERS_INVOICES . "
                                            WHERE invoice_type = 'DB'
                                            AND orders_id = " . (int)$oInfo->orders_id);
              $verify_credit = $db->Execute("SELECT *
                                             FROM " . TABLE_ORDERS_INVOICES . "
                                             WHERE invoice_type = 'CR'
                                             AND orders_id = " . (int)$oInfo->orders_id);
              // check if order has open gv
              $gv_check = $db->Execute("SELECT order_id, unique_id
                                        FROM " . TABLE_COUPON_GV_QUEUE . "
                                        WHERE order_id = " . (int)$oInfo->orders_id . "
                                        AND release_flag = 'N'
                                        LIMIT 1");
              // indicate if comments exist
              $orders_history_query = $db->Execute("SELECT orders_status_id, date_added, customer_notified, comments
                                                    FROM " . TABLE_ORDERS_STATUS_HISTORY . "
                                                    WHERE orders_id = " . (int)$oInfo->orders_id . "
                                                    AND comments != ''");
              ?>
              <thead>
                <tr class="dataTableHeadingRow">
                  <th class="dataTableContent" colspan="2">[<?php echo $oInfo->orders_id; ?>]&nbsp;&nbsp;<?php echo zen_datetime_short($order->info['date_purchased']); ?></th>
                </tr>
              </thead>
              <tbody>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_DATE_ORDER_LAST_MODIFIED; ?></td>
                  <td class="dataTableContent"><?php echo (zen_not_null($order->info['last_modified']) ? zen_date_short($order->info['last_modified']) : 'n/a'); ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_DATE_INVOICE_CREATED; ?></td>
                  <td class="dataTableContent"><?php echo (!$verify_debit->EOF ? zen_date_short($verify_debit->fields['invoice_date']) . ' #' . $verify_debit->fields['orders_invoices_id'] : 'n/a'); ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_DATE_ORDER_LAST_MODIFIED; ?></td>
                  <td class="dataTableContent"><?php echo (!$verify_credit->EOF ? zen_date_short($verify_credit->fields['invoice_date']) . ' #' . $verify_credit->fields['orders_invoices_id'] : 'n/a'); ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"></td>
                  <td class="dataTableContent"><?php echo $order->customer['email_address']; ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_INFO_IP_ADDRESS; ?></td>
                  <td class="dataTableContent"><?php echo $order->info['ip_address']; ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_INFO_PAYMENT_METHOD; ?></td>
                  <td class="dataTableContent"><?php echo $order->info['payment_method']; ?></td>
                </tr>
                <tr class="dataTableContent">
                  <td class="dataTableContent"><?php echo PDFOC_TEXT_INFO_SHIPPING_METHOD; ?></td>
                  <td class="dataTableContent"><?php echo $order->info['shipping_method']; ?></td>
                </tr>
                <?php if ($gv_check->RecordCount() > 0) { ?>
                  <tr class="dataTableContent">
                    <td class="dataTableContent" colspan="2"><?php echo zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '3'); ?></td>
                  </tr>
                  <tr class="dataTableContent">
                    <td class="dataTableContent" colspan="2"><a href="<?php echo zen_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id); ?>" class="btn btn-xs btn-default"><?php echo IMAGE_GIFT_QUEUE; ?></a></td>
                  </tr>
                <?php } ?>
                <?php if ($orders_history_query->RecordCount() > 0) { ?>
                  <tr class="dataTableContent">
                    <td class="dataTableContent" colspan="2"><?php echo ($orders_history_query->RecordCount() > 0 ? PDFOC_TEXT_COMMENTS_YES : PDFOC_TEXT_COMMENTS_NO); ?></td>
                  </tr>
                <?php } ?>
                <tr class="dataTableContent">
                  <td class="dataTableContent" colspan="2"><b><?php echo PDFOC_TEXT_PRODUCTS_ORDERED . sizeof($order->products); ?></b></td>
                </tr>
                <?php
                for ($i = 0; $i < sizeof($order->products); $i++) {
                  ?>
                  <tr class="dataTableContent">
                    <td class="dataTableContent" colspan="2"><?php echo $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']; ?></td>
                  </tr>
                  <?php
                  if (sizeof($order->products[$i]['attributes']) > 0) {
                    for ($j = 0; $j < sizeof($order->products[$i]['attributes']); $j++) {
                      ?>
                      <tr class="dataTableContent">
                        <td class="dataTableContent" colspan="2">&nbsp;<i> - <?php echo $order->products[$i]['attributes'][$j]['option']; ?>: <?php echo $order->products[$i]['attributes'][$j]['value']; ?></i></td>
                      </tr>
                      <?php
                    }
                  }
                  if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
                    $contents[] = array('align' => 'left', 'text' => TEXT_MORE);
                    break;
                  }
                }
                ?>
              </tbody>
            <?php } ?>
          </table>
          <div class="row"><?php echo zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '2'); ?></div>
          <!--// set up form for checkboxes for orders to select
              //-->
          <div class="table-responsive">
            <table class="table">
              <tr class="dataTableHeadingRow">
                <td class="dataTableContent" colspan="3"><?php echo PDFOC_ORDERS_SELECT_OPTIONS; ?></td>
                <td class="dataTableContent" align="right" colspan="3">
                  <button type="submit" class="btn btn-xs btn-primary"><?php echo PDFOC_TEXT_SUBMIT; ?></button>
                </td>
              </tr>
              <tr>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterNumbersHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_NUMBERS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_ENTER_NUMBERS, 'order_numbers', 'class="control-label"'); ?></td>
                <td class="dataTableContent"><?php echo zen_draw_input_field('order_numbers', '', 'class="form-control"'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterDatesHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_DATES_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_DATE_FROM, 'startdate', 'class="control-label"'); ?></td>
                <td class="dataTableContent"><script>dateAvailable.writeControl(); dateAvailable.dateFormat = "yyyy-MM-dd";</script></td>
              </tr>
              <tr>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterCustomerDataHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_CUSTOMER_DATA_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_ENTER_CUSTOMER_DATA, 'customer_data', 'class="control-label"'); ?></td>
                <td class="dataTableContent"><?php echo zen_draw_input_field('customer_data', '', 'class="form-control"'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterDatesHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_DATES_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_DATE_TO, 'enddate', 'class="control-label"'); ?></td>
                <td class="dataTableContent"><script>dateAvailable1.writeControl(); dateAvailable1.dateFormat = "yyyy-MM-dd";</script></td>
              </tr>
              <tr>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterStatusHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_STATUS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo zen_draw_label(PDFOC_ENTER_STATUS, 'pull_status', 'class="control-label"'); ?></td>
                <td class="dataTableContent"><?php echo zen_draw_pull_down_menu('pull_status', $orders_statuses, (PDFOC_SELECTIONSTATUS_DEFAULT == '' ? 0 : PDFOC_SELECTIONSTATUS_DEFAULT), 'class="form-control"'); ?></td>
                <td colspan="3"></td>
              </tr>
            </table>
          </div>
          <div class="row"><?php echo zen_image(DIR_WS_IMAGES . 'pixel_black.gif', '', '100%', '2'); ?></div>
          <div class="table-responsive">
            <table class="table table-striped">
              <tr>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocSubmitSelectedHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_SUBMIT_SELECTED_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><input type="submit" name="use_selected_orders" class="btn btn-xs btn-default" value="<?php echo PDFOC_SUBMIT_USE_SELECTED; ?>" /></td>
                <td class="dataTableContent"><input type="submit" name="omit_selected_orders" class="btn btn-xs btn-default" value="<?php echo PDFOC_SUBMIT_OMIT_SELECTED; ?>" /></td>
                <td class="text-right" colspan="3"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . ' ' . PDFOC_LEGEND_BILLING_SHIPPING_MISMATCH . '&nbsp;&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . ' ' . PDFOC_LEGEND_INVOICE . '&nbsp;&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_YELLOW, 10, 10) . ' ' . PDFOC_LEGEND_CREDIT; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent text-center"><?php echo ''; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo PDFOC_TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo PDFOC_TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo PDFOC_TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent text-center"><?php echo PDFOC_TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent text-right"><?php echo PDFOC_TABLE_HEADING_STATUS; ?></td>
              </tr>

              <?php
// reset orders query result to beginning
//
              $orders = $db->Execute($orders_query);
              $list_orders_end = $orders->fields['orders_id'];

              foreach ($orders as $order) {

                $show_status_dots = '';

                // ----- BOF OTFIN -----
                // Check if a final invoice and/ or credit has been created
                //
                $verify_debit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'DB' AND orders_id = '" . $order['orders_id'] . "'");
                $verify_credit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'CR' AND orders_id = '" . $order['orders_id'] . "'");

                if (!$verify_credit->EOF) {
                  $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_YELLOW, 10, 10) . '&nbsp;';
                }
                if (!$verify_debit->EOF) {
                  $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;';
                }

                // ----- EOF OTFIN -----
                // Check if billing != shipping
                //
                if (($order['delivery_name'] != $order['billing_name'] and $order['delivery_name'] != '')) {
                  $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
                }
                if (($order['delivery_street_address'] != $order['billing_street_address'] and $order['delivery_street_address'] != '')) {
                  $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
                }

                // only show orders with non-zero status (those are premature orders created by some payment methods, and are not valid orders)
                //
                if (isset($order['orders_status']) && $order['orders_status'] > 0) {
                  ?>
                  <tr <?php echo (isset($oInfo) && is_object($oInfo) && ($order['orders_id'] == $oInfo->orders_id) ? 'id="defaultSelected" class="dataTableRowSelected"' : 'class="dataTableRow"'); ?>>
                    <td class="dataTableContent text-right"><?php echo zen_draw_checkbox_field("orderlist[]", $order['orders_id']); ?></td>
                    <td class="dataTableContent text-right"><?php echo $show_status_dots . $order['orders_id']; ?></td>
                    <td class="dataTableContent"><a href="<?php echo zen_href_link(FILENAME_PDFOC, 'oID=' . $order['orders_id'] . '&action=refresh', 'NONSSL'); ?>"><?php echo zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS); ?></a>&nbsp;<?php echo $order['customers_name'] . ($order['customers_company'] != '' ? '<br>' . $order['customers_company'] : ''); ?></td>
                    <td class="dataTableContent text-right"><?php echo strip_tags($order['order_total']); ?></td>
                    <td class="dataTableContent text-center"><?php echo zen_date_short($order['date_purchased']); ?></td>
                    <td class="dataTableContent text-right"><?php echo $orders_status_array[$order['orders_status']]; ?></td>
                  </tr>
                  <?php
                }
                ?>
                <?php
                $list_orders_begin = $order['orders_id'];
              }
              ?>
              <!--// show links to prev/next page of results
                  //-->
            </table>
          </div>
          <input type="hidden" name="orders_begin" value="<?php echo $list_orders_begin; ?>" />
          <input type="hidden" name="orders_end" value="<?php echo $list_orders_end; ?>" />
          <?php echo '</form>'; ?>
          <div class="row">
            <table class="table">
              <tr>
                <td><?php echo $orders_split->display_count($orders_query_numrows, PDFOC_ORDERLISTINGMAX_DEFAULT, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                <td class="text-right"><?php echo $orders_split->display_links($orders_query_numrows, PDFOC_ORDERLISTINGMAX_DEFAULT, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>