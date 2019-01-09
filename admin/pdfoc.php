<?php
/*  PDF Order Center 1.0 for Zen Cart v1.2.6d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * admin/pdfoc.php
 *
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
    if (isset($_GET['pull_status']) && $_GET['pull_status']!='0') {
        // the $_GET['pull_status'] overrules al other parameters and selects all orders having a certain order status
        // links to select and set $_GET['pull_status'] are inserted into pdfoc_body.php

        $orders_query = "select o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot on (o.orders_id = ot.orders_id) where (ot.class = 'ot_total') and (o.orders_status = '". zen_db_input(zen_db_prepare_input($_GET['pull_status'])) . "') order by orders_id DESC";

        $_SESSION['pdfoc']['orders_query'] = $orders_query; // needed?
    } else {
// eof PDFOC orders statuses

$all_orders_query = "select o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot  on (o.orders_id = ot.orders_id) where (ot.class = 'ot_total') order by orders_id DESC";

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
    $cdata_query = "1";
    $order_checked_query = "1";
    $order_numbers_query = "1";
    $date_query = "1";
    $status_query = "1";
    $orders_query = "select o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot  on (o.orders_id = ot.orders_id) where (ot.class = 'ot_total')";
    $order_checked_array=array();
    $order_unchecked_array=array();
    $checked_exist = 0;

    // Determine which orders (if any) have been checked in the orders list
    //
    foreach ((array)$_POST['orderlist'] as $k => $v ) {

        $order_checked_array[] = zen_db_prepare_input($v);
        $checked_exist++;

    }

    // Option 0 :: the user has checked orders but pressed "submit" or "enter"
    // instead of "use" or "omit". In this case redisplay the previous query
    // and notify the user of the confusion. This prevents accidentally, say,
    // deleting orders that the user didn't intend.
    //
    if ($checked_exist > 0 && !array_key_exists('omit_selected_orders',$_POST) && !array_key_exists('use_selected_orders',$_POST)) {

      pdfoc_message_handler('PDFOC_ERROR_CONFLICTING_SPECIFICATION');

    }

    // Option 1 :: the user has pressed "use" or "omit".
    //
    if ($checked_exist==0 && array_key_exists('omit_selected_orders',$_POST)) { // none checked, pressed Omit, so show all; "order by" clause gets added below

      $orders_query = "select o.orders_id, o.customers_id, o.customers_name, o.date_purchased, o.orders_status, ot.text as order_total from " . TABLE_ORDERS . " o left join " . TABLE_ORDERS_TOTAL . " ot  on (o.orders_id = ot.orders_id) where (ot.class = 'ot_total')";

    } elseif ($checked_exist==0 && array_key_exists('use_selected_orders',$_POST)) { // none checked, pressed Use, so let user know

      pdfoc_message_handler('PDFOC_NO_ORDERS');

    } elseif ($checked_exist > 0 && array_key_exists('use_selected_orders',$_POST)) { // use checked orders

      $order_checked = implode(',',$order_checked_array);
      $order_checked_query = "o.orders_id in (" . zen_db_input($order_checked) . ")";
      $orders_query .= " and (" . $order_checked_query . ")";

    } elseif ($checked_exist > 0 && array_key_exists('omit_selected_orders',$_POST)) {  // use UNCHECKED orders

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

      while (!$orders->EOF) {
        $oNos[$orders->fields['orders_id']] = true;
        $orders->MoveNext();
      }

      $ocount=0;

      for ($i=(int)$_POST['orders_begin']; $i<= (int)$_POST['orders_end']; $i++) {

        if (!in_array($i, $order_checked_array) && isset($oNos[$i])) {

          $order_unchecked_array[] = $i;
          $ocount++;

        }

      }
      // Just in case the user decided to omit everything....
      //
      if ($ocount==0) { pdfoc_message_handler('PDFOC_NO_ORDERS'); }

      $order_unchecked = implode(',',$order_unchecked_array);
      $order_unchecked_query = "o.orders_id in (" . zen_db_input($order_unchecked) . ")";
      $orders_query .= " and (" . $order_unchecked_query . ")";


    } else {  // didn't select orders from orders list

      // Option 2 :: the user has  pressed the "submit" button or the "enter" key.
      //
      if (isset($_POST['customer_data']) && $_POST['customer_data']!='') {  // search for orders containing this data

        $cdata = zen_db_input(zen_db_prepare_input($_POST['customer_data']));
        $cdata_query = "(o.customers_city like '%" . $cdata . "%') or (o.customers_postcode like '%" . $cdata . "%') or (o.billing_name like '%" . $cdata . "%') or (o.billing_company like '%" . $cdata . "%') or (o.billing_street_address like '%" . $cdata . "%') or (o.delivery_city like '%" . $cdata . "%') or (o.delivery_postcode like '%" . $cdata . "%') or (o.delivery_name like '%" . $cdata . "%') or (o.delivery_company like '%" . $cdata . "%') or (o.delivery_street_address like '%" . $cdata . "%') or (o.billing_city like '%" . $cdata . "%') or (o.billing_postcode like '%" . $cdata . "%') or (o.customers_email_address like '%" . $cdata . "%') or (o.customers_name like '%" . $cdata . "%') or (o.customers_company like '%" . $cdata . "%') or (o.customers_street_address  like '%" . $cdata . "%') or (o.customers_telephone like '%" . $cdata . "%') or (o.ip_address like '%" . $cdata . "%')";

        $orders_query .= " and (" . $cdata_query . ")";

      }  // EOIF isset($_POST['customer_data'])


      if (isset($_POST['order_numbers']) && $_POST['order_numbers']!='') {  // use the orders whose ids are listed

        // Check invoice number(s) entered and convert to comma-separated list.
        $order_numbers = zen_db_prepare_input($_POST['order_numbers']);
        $arr_no = explode(',',$order_numbers);

        foreach ($arr_no as $key=>$value) { // if ranges were entered, convert them to comma-separated list

          $arr_no[$key]=trim($value);

          // error check for stray characters in the order #'s
          if (strspn($value,"1234567890-")!=strlen($value)) { pdfoc_message_handler('PDFOC_ERROR_BAD_ORDER_NUMBERS');  }

          if (substr_count($arr_no[$key],'-')>0) { // this was a range of values, so get them all

            // error check for too many dashes
            if (substr_count($arr_no[$key],'-')>1) { pdfoc_message_handler('PDFOC_ERROR_BAD_ORDER_NUMBERS');  }

            $temp_range=explode('-',$arr_no[$key]);
            $arr_no[$key]=implode(',',range((int) $temp_range[0], (int) $temp_range[1]));

          } // EOIF substr_count

        }  // EOFOREACH $arr_no

        $order_numbers=implode(',',$arr_no);
        $order_numbers_query = "o.orders_id in (" . zen_db_input($order_numbers) . ")";

        $orders_query .= " and (" . $order_numbers_query . ")";

      }  // EOIF isset($_POST['order_numbers'])


      if (isset($_POST['startdate']) && $_POST['startdate']!='' && isset($_POST['enddate']) && $_POST['enddate']!='') { // use orders between the specified dates

        if ((strlen($_POST['startdate']) != 10) || pdfoc_verify_date($_POST['startdate'])) { pdfoc_message_handler('PDFOC_ERROR_BAD_DATE'); }
        if ((strlen($_POST['enddate']) != 10) || pdfoc_verify_date($_POST['enddate'])) { pdfoc_message_handler('PDFOC_ERROR_BAD_DATE'); }

        $startdate = zen_db_prepare_input($_POST['startdate']);
        $enddate = zen_db_prepare_input($_POST['enddate']);
        $date_query = "o.date_purchased between '" . zen_db_input($startdate) . "' and '" . zen_db_input($enddate) . " 23:59:59'";

        $orders_query .= " and (" . $date_query . ")";

      } // EOIF isset($_POST['startdate'])


      if (isset($_POST['pull_status']) && $_POST['pull_status']!='0') {

        $status_query = "o.orders_status = '". zen_db_input(zen_db_prepare_input($_POST['pull_status'])) . "'";

        $orders_query .= " and (" . $status_query . ")";

      } // EOIF isset($_POST['pull_status'])

    } // EOIF $checked_exist


    // however we got them, we have our query conditions; now
    // finish off the query and check to see if any orders have
    // been selected.
    //
    $orders_query .= " order by orders_id DESC";

    $orders = $db->Execute($orders_query);
    if (!$orders->RecordCount() > 0) { pdfoc_message_handler('PDFOC_NO_ORDERS'); }

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

     if (isset($_GET['action']) && ($_GET['action']=='refresh' || $_GET['action']=='credit_confirm_no')) { $action = 'refresh'; }

     elseif (isset($_GET['action']) && $_GET['action']=='credit_confirm') {

        $action = 'credit_confirm';

        // restore the post vars (whether to print order number, telephone, etc.)
        //
        foreach ($_SESSION['pdfoc'] as $k => $v) {
          $_POST[$k] = $v;
        }

     }

     elseif (isset($_POST['file_type']) && $_POST['file_type']=="Credit.php") { $action = 'credit_request'; }

     elseif ((isset($_POST['file_type']) && $_POST['file_type']!="0" && $_POST['file_type']!="Credit.php") || (isset($_POST['status']) && $_POST['status']!='0')) { $action = 'status_andor_print'; }

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
         foreach ($_POST as $k => $v) {

           $_SESSION['pdfoc'][$k] = $v;

         }
         // check to see if any order(s) does not yet have a
         // credit number. If there is one, pop up a confirmation
         // message. If all selected orders already have
         // credit numbers, skip the message.
         //
         $orders = $db->Execute($orders_query);
         $no_credit = 0;

         while (!$orders->EOF) {

           $verify_credit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'CR' AND orders_id = '" . $orders->fields['orders_id'] . "'");

           if ($verify_credit->EOF) { // this order doesn't have a credit yet
              $no_credit++;
              break;
           }

           $orders->MoveNext();

         }
         if ($no_credit > 0) {
              $message = PDFOC_MESSAGE_CREDIT_ARE_YOU_SURE .
                         '<br /><a href="' . zen_href_link(FILENAME_PDFOC,'form=action&action=credit_confirm') .  '">' . PDFOC_TEXT_YES . '</a>' .
                         '<br /><a href="' . zen_href_link(FILENAME_PDFOC, 'form=action&action=credit_confirm_no') . '">' . PDFOC_TEXT_NO . '</a>';

          } else {

             zen_redirect(FILENAME_PDFOC . '?form=action&action=credit_confirm');

          }

          break;

       case 'status_andor_print':   // Status update and/or printing options were specified
       case 'credit_confirm':

         // --- BEGIN STATUS UPDATE AND NOTIFICATION --------------------------------------------------
         if (isset($_POST['status']) && $_POST['status']!='0') { // need to update status

         // reset orders query result to beginning
         //
         $orders = $db->Execute($orders_query);
         $status = zen_db_prepare_input($_POST['status']);
         $comments = zen_db_prepare_input($_POST['comments']);

           while (!$orders->EOF) { // loop over all specified orders

             $order = new order($orders->fields['orders_id']);
             $oID = $orders->fields['orders_id'];

             $customer_notified = 0;

             if (($_POST['notify_comments']) && ($comments!='')) {
                $notify_comments = sprintf(PDFOC_EMAIL_TEXT_COMMENTS_UPDATE,pdfoc_html_cleanup($comments));
             } else {
                $notify_comments = '';
             }

             if ($_POST['notify']) { // send e-mail to customer informing him/her of new order status

               $email_text_subject =  sprintf($pdfoc_subject[(int)$status], $oID);  // $pdfoc_subject[] is defined in admin/includes/languages/<language>/pdfoc.php


               $email = sprintf(PDFOC_EMAIL_SALUTATION,$order->customer['name']);

               $email .= "\n\n" .
                        $email_text_subject . ".\n\n" .  PDFOC_EMAIL_SEPARATOR .  "\n\n" .
                        PDFOC_EMAIL_TEXT_INVOICE_URL . ' ' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders->fields['orders_id'], 'SSL') . "\n" .
                        PDFOC_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($order->info['date_purchased']) . "\n\n" . $notify_comments .  PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_IF_QUESTIONS . PDFOC_EMAIL_SIGNOFF . STORE_NAME . "\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n";

               $email_html['EMAIL_SUBJECT'] = "\n\n";
               $email_html['EMAIL_MESSAGE_HTML'] = sprintf(PDFOC_EMAIL_SALUTATION,$order->customer['name']) . "\n\n" .
                        $email_text_subject . ".\n\n" .  PDFOC_EMAIL_SEPARATOR .  "\n\n" .
                        PDFOC_EMAIL_TEXT_INVOICE_URL . ' <a href="' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders->fields['orders_id'], 'SSL') . '">' . zen_catalog_href_link(FILENAME_CATALOG_ACCOUNT_HISTORY_INFO, 'order_id=' . $orders->fields['orders_id'], 'SSL') . '</a>' . "\n" .
                        PDFOC_EMAIL_TEXT_DATE_ORDERED . ' ' . zen_date_long($order->info['date_purchased']) . "\n\n" . $notify_comments .  PDFOC_EMAIL_SEPARATOR . "\n\n" .
                        PDFOC_EMAIL_IF_QUESTIONS . PDFOC_EMAIL_SIGNOFF . STORE_NAME . "\n\n" . PDFOC_EMAIL_SEPARATOR . "\n\n";

               zen_mail($order->customer['name'], $order->customer['email_address'], $email_text_subject, $email, STORE_NAME, STORE_OWNER_EMAIL_ADDRESS, $email_html);

               if (SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_STATUS == '1' and SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO != '') {
                  zen_mail('', SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO, SEND_EXTRA_ORDERS_STATUS_ADMIN_EMAILS_TO_SUBJECT . ' ' . $email_text_subject, $email, STORE_NAME, EMAIL_FROM);
               }
               $customer_notified = '1';

             }  // EOIF $_POST['notify']

             if ($status != $order->info['orders_status'] || $customer_notified=='1') { // update order status if new status specified or if customer notified

               $db->Execute("update " . TABLE_ORDERS . " set orders_status = '" . zen_db_input($status) . "', last_modified = now() where orders_id = '" . $orders->fields['orders_id'] . "'");
               $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (orders_id, orders_status_id, date_added, customer_notified, comments)
                             values ('" . $orders->fields['orders_id'] . "', '" . zen_db_input($status) . "', now(), '" . $customer_notified . "', '" . $comments  . "')");

             } // EOIF $_POST['status'] != $order->info['orders_status']

             $orders->MoveNext();

           } // EOWHILE (!$orders->EOF)

         } // EOIF isset($_POST['status'])

         // --- END STATUS UPDATE AND NOTIFICATION --------------------------------------------

         // --- BEGIN PRINTING ----------------------------------------------------------------
         if (isset($_POST['file_type']) && $_POST['file_type']!="0")  { // time to print

           // Basic error handling, initialization, and avoid-a-timeout setup
           //
           if (!is_writeable(DIR_PDFOC_PDF)) { pdfoc_message_handler('PDFOC_SET_PERMISSIONS'); }

           $pageloop = "0";
           $time0   = time();

           require(DIR_PDFOC_INCLUDE . 'class.ezpdf.php');

           // Load the specified template (e.g. invoice or label). Note: $pdf is instantiated in
           // the template file when $pageloop = 0, which is the case here.
           require(DIR_PDFOC_INCLUDE . 'templates/' . $_POST['file_type']);

           $pageloop = "1";


           $numpage = 0;
           $numrecords = 0;
           $nexttotal = $nextproduct = $nextproduct1 = $nextproduct2 = 0; // last two are used in combined Packing Slip and Invoice template
           $nextcomment = $nextcomment1 = $nextcomment2 = 0; // last two are used in combined Packing Slip and Invoice template
           $secondpage = false;

           if ($_POST['show_comments']) { $show_comments = true; } else { $show_comments = false; }

           // reset orders query result to beginning
           //
           $orders = $db->Execute($orders_query);

           while (!$orders->EOF) { // loop over all specified orders

             $order = new order($orders->fields['orders_id']);
             $oID = $orders->fields['orders_id'];

             if ($numpage != 0) { $pdf->EzNewPage(); }    // insert a new page into the existing pdf document (not on the first iteration)


             // for each order, reload the specified template. The template file
             // contains the order-specific instructions, such as printing the invoice number
             // and order date. That's why it has to be reloaded for every order.
             //
             require(DIR_PDFOC_INCLUDE . 'templates/' . $_POST['file_type']);

             $numpage++;
             if ($_POST['file_type'] == "Labels.php") { $numrecords += $numlabel; }  elseif ($secondpage===false) { $numrecords++; }

             if ($_POST['file_type'] == "Packing-Slip-and-Invoice.php") { $numpage++; }

             // Send fake header to avoid timeout, got this trick from phpMyAdmin
             //
             $time1  = time();
             if ($time1 >= $time0 + 30) {
               $time0 = $time1;
               header('X-bpPing: Pong');
             }

             if ($secondpage===true) {    // continue printing this order
                continue;
             } else {                     // finished with this order, so move to next one
                $orders->MoveNext();
             }

           } // EOWHILE !$orders->EOF

           $pdf_code = $pdf->output();  // get pdf stream for this order as a string

           // append the pdf page for this order to the pdf file
           $fname = DIR_PDFOC_PDF . FILENAME_PDFOC_PDF;
           if ($fp = fopen($fname,'w')) {
             fwrite($fp,$pdf_code);
             fclose($fp);
           } else {
             pdfoc_message_handler('PDFOC_FAILED_TO_OPEN');
           }

         } // EOIF isset($_POST['file_type'])

         // --- END PRINTING ----------------------------------------------------------------


         // Notify admin of print/update success.
         //
         if (isset($_POST['status']) && $_POST['status']!='0') {
           $message .=  PDFOC_MESSAGE_STATUS_WAS_UPDATED;

           if ($customer_notified=='1') {
             $message .= PDFOC_MESSAGE_EMAIL_SENT;
           } else { // add period
             $message .= ".";
           }
         }

         if (isset($_POST['file_type']) && $_POST['file_type'] != "0") {
           $message .=  "<br />" . sprintf(PDFOC_MESSAGE_SUCCESS, $numpage, $numrecords, $fname);
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
     if((PDFOC_BLOCK_ALL_ORDERS_QUERY_UPDATE == 'true')&&($orders_query == $all_orders_query)){
           exit('Blocked all orders query update! Press the browser back button and check your query.');
     }
     while (!$orders->EOF) {

       $oID = zen_db_prepare_input($orders->fields['orders_id']);
       zen_remove_order($oID, $_POST['restock']);

       $orders->MoveNext();

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
//$message .= '<br /><br/>file_type:' . $_POST['file_type'] . '<br /><br />';
//foreach ($_POST['orderlist'] as $k => $v ) { $message .= "POST[orderlist][$k]=$v  ";  }
if($orders_query == $all_orders_query){
  $messageStack->add($pdfoc_error['PDFOC_NO_SELECTION'], 'error');
}



// output the html for the batch_print admin page -- note this includes php for order listing
//
require(DIR_PDFOC_INCLUDE . 'pdfoc_header.php');
require(DIR_PDFOC_INCLUDE . 'pdfoc_body.php');
require(DIR_PDFOC_INCLUDE . 'pdfoc_footer.php');


?>