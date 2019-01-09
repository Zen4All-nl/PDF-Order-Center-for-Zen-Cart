<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * pdfoc_body.php
 *
 */


  $orders_statuses = array();
  $orders_status_array = array();
  $orders_status = $db->Execute("select orders_status_id, orders_status_name from " . TABLE_ORDERS_STATUS . " where language_id = '" . $_SESSION['languages_id'] . "'");
  $orders_statuses[] = array('id' => 0, 'text' => 'None');

  while (!$orders_status->EOF) {
    $orders_statuses[] = array('id' => $orders_status->fields['orders_status_id'],'text' => $orders_status->fields['orders_status_name'] . ' [' . $orders_status->fields['orders_status_id'] . ']');
    $orders_status_array[$orders_status->fields['orders_status_id']] = $orders_status->fields['orders_status_name'];
    $orders_status->MoveNext();
  }

  $directory = DIR_PDFOC_TEMPLATES;
  $resc = opendir($directory);

  if (!$resc) {
    echo "Problem opening directory $directory. Error: $php_errormsg";
    exit;
  }

  $file_type_array = array(array('id' => '0', 'text' => PDFOC_TEXT_NONE));  // This constant is defined in admin/includes/languages/english/extra_definitions/pdfoc.php
  while ($file = readdir($resc)) {

    $ext = strrchr($file, ".");

    if ($ext == ".php") {

      $filename = str_replace('-', '_',$file);
      $filename = str_replace($ext, "",$filename);
    $fileconst = 'PDFOC_TEMPLATE_NAME_' . strtoupper($filename);
    /* look for a constant for that filename; if exists, use it, otherwise use filename */
    /* (allows language-specific names to be displayed in the dropdown menus) */
    if (defined("$fileconst"))
    {
      $filename = constant($fileconst);
    }
    else
    {
      $filename = "MISTAKE! " . $filename;        // debugging code
//      $filename = str_replace('_', " ", $filename);
    }
      $file_type_array[] = array('id' => $file,'text' => $filename);
    } // EOIF $ext

  }  // EOWHILE $file
?>
<tr>

<!--// This is the options/actions section on LH half of page //-->

  <td valign="top" width="35%"><table valign="top" border="0" cellpadding="5" cellspacing="0" width="100%">
<?php
  if ($message) {
?>
  <tr>
     <td>
      <table border="0" cellpadding="5" cellspacing="0" width="100%">
              <tr class="pdfocMessageHeaderRow">
        <td class="pdfocMessageHeaderContent" id="pdfocProgramMessage" width="50%"><?php echo PDFOC_PROGRAM_MESSAGE; ?></td>
      </tr>
              <tr class="pdfocMessageRow">
                <td class="pdfocMessageContent"><?php echo $message; ?></td>
              </tr>
      </table>
     </td>
  </tr>
  <tr>
    <td colspan="2"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
  </tr>


<?php
  } // EOIF $message
  echo zen_draw_form('pdfoc_deletion', FILENAME_PDFOC, 'form=deletion','post','',true);

  if((PDFOC_BLOCK_ALL_ORDERS_QUERY_UPDATE == 'true')&&($_SESSION['pdfoc']['orders_query'] == $all_orders_query)){
    // protect admin from updating all orders (when no selection was made)
    $pdfoc_buttons_disabled = 'disabled="disabled"';
  }else{
    $pdfoc_buttons_disabled = '';
  }

?>
    <tr>
      <td>
          <table valign="top" border="0" cellpadding="5" cellspacing="0" width="100%">
              <tr class="dataTableRowSelected">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocDeleteOrdersHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_DELETE_ORDERS_HELPTEXT; ?></span></a></td>
                <td><input <?php echo $pdfoc_buttons_disabled; ?> type="submit" name="delete" value="<?php echo PDFOC_DELETE_ORDERS; ?>" onclick="return confirm('<?php echo PDFOC_MESSAGE_DELETE_ARE_YOU_SURE; ?>')" /></td>
                <td><?php echo PDFOC_TEXT_RESTOCK; ?></td>
                <td><?php echo zen_draw_selection_field('restock', 'checkbox', true, (PDFOC_RESTOCK_DEFAULT=='true' ? true : false)); ?></td>
                </form>
              </tr>
          </table>                
<?php
  echo zen_draw_form('pdfoc_action', FILENAME_PDFOC, 'form=action','post','',true);
?>
          <table valign="top" border="0" cellpadding="5" cellspacing="0" width="100%">
              <tr class="dataTableRowSelected">

                <td class="dataTableHelptext" align="right"><a class="helptextPopup" id="pdfocTextSubmitHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_TEXT_SUBMIT_HELPTEXT; ?></span></a></td>
                <td align="left"><input <?php echo $pdfoc_buttons_disabled; ?> type="submit" name="submit_action" value="<?php echo PDFOC_TEXT_SUBMIT; ?>" <?php echo defined('PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE') ? 'onclick="return confirm(\'' . PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE . '\')"': '';?> /></td>
     </tr>
          </table>
       </td>
    </tr>
    <tr>
       <td>
       <table id="pdfocPrintingOptions" <?php echo PDFOC_HIDE_PRINTING_OPTIONS == 'true' ? ' class="pdfocHidden"': '';?> valign="top" border="0" cellpadding="5" cellspacing="0" width="100%">
              <tr>
                <td colspan="7"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr class="dataTableRowSelected">
        <td class="dataTableContent" colspan="7"><b><?php echo PDFOC_PRINTING_OPTIONS; ?></b></td>
      </tr>
              <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocChooseTemplateHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_CHOOSE_TEMPLATE_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_CHOOSE_TEMPLATE; ?></td>
                <td colspan="5"><?php echo zen_draw_pull_down_menu('file_type', $file_type_array, PDFOC_TEMPLATE_DEFAULT); ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocInvoiceFinalOrPreviewHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_INVOICE_FINAL_OR_PREVIEW_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_INVOICE_FINAL_OR_PREVIEW; ?></td>
                <td colspan="5" class="dataTableContent"><?php echo PDFOC_INVOICE_PREVIEW . zen_draw_selection_field('invoice_mode', 'radio', "preview", (PDFOC_FINAL_PREVIEW_DEFAULT=='preview' ? true : false)) . ' ' . PDFOC_INVOICE_FINAL . zen_draw_selection_field('invoice_mode', 'radio', "final", (PDFOC_FINAL_PREVIEW_DEFAULT=='final' ? true : false)); ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocBillingOrDeliveryHelptex" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_BILLING_OR_DELIVERY_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_BILLING_OR_DELIVERY; ?></td>
                <td colspan="5" class="dataTableContent"><?php echo PDFOC_DELIVERY . zen_draw_selection_field('address', 'radio', "delivery", (PDFOC_LABELADDRESS_DEFAULT=='delivery' ? true : false)) . ' ' . PDFOC_BILLING . zen_draw_selection_field('address', 'radio', "billing", (PDFOC_LABELADDRESS_DEFAULT=='billing' ? true : false)); ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocLabelToStartOnHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_LABEL_TO_START_ON_HELPTEXT; ?></span></a></td>
        <td class="dataTableContent"><?php echo PDFOC_LABEL_TO_START_ON; ?></td>
                <td colspan="5" class="dataTableContent"><?php echo zen_draw_input_field('startpos', '1'); ?></td>
              </tr>
      <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintOrderDateHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_ORDER_DATE_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_ORDER_DATE; ?></td>
                <td><?php echo zen_draw_selection_field('show_order_date', 'checkbox', true, (PDFOC_ORDERDATE_DEFAULT=='true' ? true : false)); ?></td>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_COMMENTS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_COMMENTS; ?></td>
                <td><?php echo zen_draw_selection_field('show_comments', 'checkbox', true, (PDFOC_PRINTCOMMENTS_DEFAULT=='true' ? true : false)); ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintTelNoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_TEL_NO_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_TEL_NO; ?></td>
                <td><?php echo zen_draw_selection_field('show_phone', 'checkbox', true, (PDFOC_TELEPHONE_DEFAULT=='true' ? true : false)); ?></td>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintEmailAddressHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_EMAIL_ADDRESS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_EMAIL_ADDRESS; ?></td>
                <td><?php echo zen_draw_selection_field('show_email', 'checkbox', true, (PDFOC_EMAIL_DEFAULT=='true' ? true : false)); ?></td>
              </tr>
      <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintPaymentInfoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_PAYMENT_INFO_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_PAYMENT_INFO; ?></td>
                <td><?php echo zen_draw_selection_field('show_pay_method', 'checkbox', true, (PDFOC_PAYMENTINFO_DEFAULT=='true' ? true : false)); ?></td>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocPrintCcNoHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_PRINT_CC_NO_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_PRINT_CC_NO; ?></td>
                <td><?php echo zen_draw_selection_field('show_cc', 'checkbox', true, (PDFOC_CCNO_DEFAULT=='true' ? true : false)); ?></td>
              </tr>
              <tr>
                <td colspan="7"><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
              </tr>
              <tr class="dataTableRowSelected">
        <td class="dataTableContent" colspan="7"><b><?php echo PDFOC_ORDER_STATUS_OPTIONS; ?></b></td>
      </tr>
      <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocAutoChangeStatusHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_AUTO_CHANGE_STATUS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_AUTO_CHANGE_STATUS; ?></td>
                <td colspan="5"><?php echo zen_draw_pull_down_menu('status', $orders_statuses, (PDFOC_NEWSTATUS_DEFAULT=='' ? 0 : PDFOC_NEWSTATUS_DEFAULT)); ?></td>
              </tr>
               <tr class="dataTableRow">
                <td class="dataTableContent"><INPUT TYPE="checkbox" NAME="autocomment" VALUE="<?php echo PDFOC_COMMENTS_AUTO_SHIPPED_TEXT; ?>" onClick="addAutoComment(this.form)"></td>
                <td class="dataTableContent" colspan="5"><?php echo PDFOC_COMMENTS_AUTO_SHIPPED; ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableContent"><INPUT TYPE="checkbox" NAME="autocomment" VALUE="<?php echo PDFOC_COMMENTS_AUTO_CC_DECLINED_TEXT; ?>" onClick="addAutoComment(this.form)"></td>
                <td class="dataTableContent" colspan="5"><?php echo PDFOC_COMMENTS_AUTO_CC_DECLINED; ?></td>
              </tr>
              <tr class="dataTableRow">
                <td class="dataTableContent"><INPUT TYPE="checkbox" NAME="autocomment" VALUE="<?php echo PDFOC_COMMENTS_AUTO_BACKORDER_TEXT; ?>" onClick="addAutoComment(this.form)"></td>
                <td class="dataTableContent" colspan="5"><?php echo PDFOC_COMMENTS_AUTO_BACKORDER; ?></td>
              </tr>
      <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_COMMENTS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_COMMENTS; ?></td>
                <td colspan="5"><?php echo zen_draw_textarea_field('comments', 'soft', '30', '5','','',false); ?></td>
              </tr>
      <tr class="dataTableRow">
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocNotifyCustomerHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_NOTIFY_CUSTOMER_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_NOTIFY_CUSTOMER; ?></td>
                <td><?php echo zen_draw_selection_field('notify', 'checkbox', true, (PDFOC_NOTIFYCUSTOMER_DEFAULT=='true' ? true : false)); ?></td>
                <td><?php echo zen_draw_separator('pixel_trans.gif', '1', '10'); ?></td>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocNotifyCommentsHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_NOTIFY_COMMENTS_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><?php echo PDFOC_NOTIFY_COMMENTS; ?></td>
                <td><?php echo zen_draw_selection_field('notify_comments', 'checkbox', true, (PDFOC_NOTIFYCOMMENTS_DEFAULT=='true' ? true : false)); ?></td>
              </tr>
    </table>
      </td>
    </tr>
  </form>
 </table></td>

<!--// This is the order list section on RH half of page //-->

   <td valign="top" width="65%"><!--bof PDFOC orders statuses //-->
<?php
if (is_array(zen_get_orders_status())) {
    echo '<div id="orderstatuses">';
    echo '<strong>&lt;=</strong> <a ' . (((int)$_GET['pull_status'] == 0) ? 'class="selected"': '') . 'href="' . zen_href_link(FILENAME_PDFOC, 'pull_status=0'). '">' . PDFOC_TEXT_RESET . '</a>';
    foreach (zen_get_orders_status() as $value) {
        echo '<span>|</span>';
        //print_r( $value);
        echo '<a ' . (((int)$_GET['pull_status'] == $value['id']) ? 'class="selected"': '') . 'href="' . zen_href_link(FILENAME_PDFOC, 'pull_status=' . $value['id']). '">' . $value['text'] . '</a>';
    }
    echo '</div>';
}
?>
<!--eof PDFOC orders statuses //--><table border="0" cellpadding="3" cellspacing="0" width="100%">
<?php  echo zen_draw_form('pdfoc_selection', FILENAME_PDFOC, 'form=selection','post','',true);
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">

<?php
// Split page when more results than fit on one page
//
// First: reset page when page is unknown
//
if (($_GET['page'] == '' or $_GET['page'] <= 1) and $_GET['oID'] != '') {
  $check_page = $db->Execute($orders_query);
  $check_count=1;
  if ($check_page->RecordCount() > PDFOC_ORDERLISTINGMAX_DEFAULT) {
    while (!$check_page->EOF) {
      if ($check_page->fields['orders_id'] == $_GET['oID']) {
        break;
      }
      $check_count++;
      $check_page->MoveNext();
    }
    $_GET['page'] = round((($check_count/PDFOC_ORDERLISTINGMAX_DEFAULT)+(fmod_round($check_count,PDFOC_ORDERLISTINGMAX_DEFAULT) !=0 ? .5 : 0)),0);
  } else {
    $_GET['page'] = 1;
  }
}
// get db results for the current page
// and display selected order row as such
//
    $orders_split = new splitPageResults($_GET['page'], PDFOC_ORDERLISTINGMAX_DEFAULT, $orders_query, $orders_query_numrows);
    $orders = $db->Execute($orders_query);

      $heading=array();
      $contents=array();

      // Set up the top box displaying order information for the selected order in
      // the orders list below

      // Get the selected order
      //
      while (!$orders->EOF) {
        if ((!isset($_GET['oID']) || (isset($_GET['oID']) && ($_GET['oID'] == $orders->fields['orders_id']))) && !isset($oInfo)) {
          $oInfo = new objectInfo($orders->fields);
        }
        $orders->MoveNext();
      }

      if (isset($oInfo) && is_object($oInfo)) {

        $order = new order($oInfo->orders_id);

        // add in OTFIN stuff here: verify_credit, verify_debit
        //
        $verify_debit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'DB' AND orders_id = '" . $oInfo->orders_id . "'");
        $verify_credit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'CR' AND orders_id = '" . $oInfo->orders_id . "'");

        $heading[] = array('text' => '<strong>[' . $oInfo->orders_id . ']&nbsp;&nbsp;' . zen_datetime_short($order->info['date_purchased']) . '</strong>');

        // set up left-hand side of order infoBox
        //
        $contents[] = array('text' => PDFOC_TEXT_DATE_ORDER_LAST_MODIFIED . ' ' . (zen_not_null($order->info['last_modified']) ? zen_date_short($order->info['last_modified']) : 'n/a'));
        $contents[] = array('text' => PDFOC_TEXT_DATE_INVOICE_CREATED . ' ' . (!$verify_debit->EOF ? zen_date_short($verify_debit->fields['invoice_date']) . ' #' . $verify_debit->fields['orders_invoices_id'] : 'n/a'));
        $contents[] = array('text' => PDFOC_TEXT_DATE_CREDIT_CREATED . ' ' . (!$verify_credit->EOF ? zen_date_short($verify_credit->fields['invoice_date']) . ' #' . $verify_credit->fields['orders_invoices_id'] : 'n/a'));
        $contents[] = array('text' => '<br />' . $order->customer['email_address']);
        $contents[] = array('text' => PDFOC_TEXT_INFO_IP_ADDRESS . ' ' . $order->info['ip_address']);
        $contents[] = array('text' => '<br />' . PDFOC_TEXT_INFO_PAYMENT_METHOD . ' '  . $order->info['payment_method']);
        $contents[] = array('text' => PDFOC_TEXT_INFO_SHIPPING_METHOD . ' '  . $order->info['shipping_method']);

// check if order has open gv
        $gv_check = $db->Execute("select order_id, unique_id
                                  from " . TABLE_COUPON_GV_QUEUE ."
                                  where order_id = '" . $oInfo->orders_id . "' and release_flag='N' limit 1");
        if ($gv_check->RecordCount() > 0) {
          $goto_gv = '<a href="' . zen_href_link(FILENAME_GV_QUEUE, 'order=' . $oInfo->orders_id) . '">' . zen_image_button('button_gift_queue.gif',IMAGE_GIFT_QUEUE) . '</a>';
          $contents[] = array('text' => '<br />' . zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','3'));
          $contents[] = array('align' => 'center', 'text' => $goto_gv);
        }

// indicate if comments exist
      $orders_history_query = $db->Execute("select orders_status_id, date_added, customer_notified, comments from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $oInfo->orders_id . "' and comments !='" . "'" );
      if ($orders_history_query->RecordCount() > 0) {
      $contents[] = array('align' => 'left', 'text' => '<br />' . ($orders_history_query->RecordCount() > 0 ? PDFOC_TEXT_COMMENTS_YES : PDFOC_TEXT_COMMENTS_NO));
      }

      $lhblock = new tableBlock($contents);
      $lhcontents = $lhblock->tableBlock($contents);

      // set up right-hand side of order infoBox
      //
      $contents = array();
      $contents[] = array('text' => '<b>' . PDFOC_TEXT_PRODUCTS_ORDERED . sizeof($order->products) . '</b>' );
      for ($i=0; $i<sizeof($order->products); $i++) {
        $contents[] = array('text' => $order->products[$i]['qty'] . '&nbsp;x&nbsp;' . $order->products[$i]['name']);

        if (sizeof($order->products[$i]['attributes']) > 0) {
          for ($j=0; $j<sizeof($order->products[$i]['attributes']); $j++) {
            $contents[] = array('text' => '&nbsp;<i> - ' . $order->products[$i]['attributes'][$j]['option'] . ': ' . $order->products[$i]['attributes'][$j]['value'] . '</i></nobr>' );
          }
        }
        if ($i > MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING and MAX_DISPLAY_RESULTS_ORDERS_DETAILS_LISTING != 0) {
          $contents[] = array('align' => 'left', 'text' => TEXT_MORE);
          break;
        }
      }

      $rhblock = new tableBlock($contents);
      $rhcontents = $rhblock->tableBlock($contents);

      $contents = array();
      $contents[] = array('text' => $lhcontents . '</td><td class="infoBoxContent" valign="top">' . $rhcontents);

     } // EOIF isset($oInfo)


  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
?>
         <tr>
            <td width="100%" valign="top">
<?php
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
    echo '          </tr>' . "\n";

  }
?>
            </table></td>
          </tr>
          <tr>
            <td width="100%"><?php echo zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','2'); ?></td>
          </tr>
<!--// set up form for checkboxes for orders to select
    //-->
          <tr>
               <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                 <tr class="dataTableHeadingRow">
                   <td class="dataTableContent" colspan="7"><b><?php echo PDFOC_ORDERS_SELECT_OPTIONS; ?></b></td>
                   <td class="dataTableContent" align="right" colspan="3"><input type="submit" name="submit_selection" value="<?php echo PDFOC_TEXT_SUBMIT; ?>" /></td>
         </tr>
                 <tr>
                   <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterNumbersHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_NUMBERS_HELPTEXT; ?></span></a></td>
                   <td class="dataTableContent"><?php echo PDFOC_ENTER_NUMBERS; ?></td>
                   <td class="dataTableContent"><?php echo zen_draw_input_field('order_numbers'); ?></td>
                   <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterDatesHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_DATES_HELPTEXT; ?></span></a></td>
                   <td class="dataTableContent"><?php echo PDFOC_DATE_FROM; ?></td>
                   <td class="dataTableContent"><script language="javascript">dateAvailable.writeControl(); dateAvailable.dateFormat="yyyy-MM-dd";</script></td>
                   <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterStatusHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_STATUS_HELPTEXT; ?></span></a></td>
                   <td class="dataTableContent"><?php echo PDFOC_ENTER_STATUS; ?></td>
                 </tr>
                 <tr>
                   <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterCustomerDataHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_CUSTOMER_DATA_HELPTEXT; ?></span></a></td>
                   <td class="dataTableContent"><?php echo PDFOC_ENTER_CUSTOMER_DATA; ?></td>
                   <td class="dataTableContent"><?php echo zen_draw_input_field('customer_data'); ?></td>
                   <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocEnterDatesHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_ENTER_DATES_HELPTEXT; ?></span></a></td>
                   <td class="dataTableContent"><?php echo PDFOC_DATE_TO; ?></td>
                   <td class="dataTableContent"><script language="javascript">dateAvailable1.writeControl(); dateAvailable1.dateFormat="yyyy-MM-dd";</script></td>
                   <td class="dataTableContent" colspan="2"><?php echo zen_draw_pull_down_menu('pull_status', $orders_statuses, (PDFOC_SELECTIONSTATUS_DEFAULT=='' ? 0 : PDFOC_SELECTIONSTATUS_DEFAULT)); ?></td>
                 </tr>
               </table></td>
          </tr>
          <tr>
            <td width="100%"><?php echo zen_image(DIR_WS_IMAGES . 'pixel_black.gif','','100%','2'); ?></td>
          </tr>
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td class="dataTableHelptext"><a class="helptextPopup" id="pdfocSubmitSelectedHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_SUBMIT_SELECTED_HELPTEXT; ?></span></a></td>
                <td class="dataTableContent"><input type="submit" name="use_selected_orders" value="<?php echo PDFOC_SUBMIT_USE_SELECTED; ?>" /></td>
                <td class="dataTableContent"><input type="submit" name="omit_selected_orders" value="<?php echo PDFOC_SUBMIT_OMIT_SELECTED; ?>" /></td>
                <td class="smallText" align="right" colspan="3"><?php echo TEXT_LEGEND . ' ' . zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . ' ' . PDFOC_LEGEND_BILLING_SHIPPING_MISMATCH
                                             // ----- BOF OTFIN -----
                                             . '&nbsp;&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . ' ' . PDFOC_LEGEND_INVOICE . '&nbsp;&nbsp;&nbsp;&nbsp;' . zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_YELLOW, 10, 10) . ' ' . PDFOC_LEGEND_CREDIT
                                             // ----- EOF OTFIN -----
                                             ; ?></td>
              </tr>
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent" align="center"><?php echo ''; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo PDFOC_TABLE_HEADING_ORDERS_ID; ?></td>
                <td class="dataTableHeadingContent"><?php echo PDFOC_TABLE_HEADING_CUSTOMERS; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo PDFOC_TABLE_HEADING_ORDER_TOTAL; ?></td>
                <td class="dataTableHeadingContent" align="center"><?php echo PDFOC_TABLE_HEADING_DATE_PURCHASED; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo PDFOC_TABLE_HEADING_STATUS; ?></td>
              </tr>

<?php

    // reset orders query result to beginning
    //
    $orders = $db->Execute($orders_query);
    $list_orders_end = $orders->fields['orders_id'];

    while (!$orders->EOF) {

      $show_status_dots = '';

      // ----- BOF OTFIN -----
      // Check if a final invoice and/ or credit has been created
      //
      $verify_debit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'DB' AND orders_id = '" . $orders->fields['orders_id'] . "'");
      $verify_credit = $db->Execute("select * from " . TABLE_ORDERS_INVOICES . " where invoice_type = 'CR' AND orders_id = '" . $orders->fields['orders_id'] . "'");

      if (!$verify_credit->EOF) { $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif', IMAGE_ICON_STATUS_YELLOW, 10, 10) . '&nbsp;'; }
      if (!$verify_debit->EOF) { $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_green.gif', IMAGE_ICON_STATUS_GREEN, 10, 10) . '&nbsp;'; }

      // ----- EOF OTFIN -----
      // Check if billing != shipping
      //
      if (($orders->fields['delivery_name'] != $orders->fields['billing_name'] and $orders->fields['delivery_name'] != '')) {
        $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
      }
      if (($orders->fields['delivery_street_address'] != $orders->fields['billing_street_address'] and $orders->fields['delivery_street_address'] != '')) {
        $show_status_dots .= zen_image(DIR_WS_IMAGES . 'icon_status_red.gif', IMAGE_ICON_STATUS_RED, 10, 10) . '&nbsp;';
      }

      // only show orders with non-zero status (those are premature orders created by some payment methods, and are not valid orders)
      //
      if (isset($orders->fields['orders_status']) && $orders->fields['orders_status']>0) {
         if (isset($oInfo) && is_object($oInfo) && ($orders->fields['orders_id'] == $oInfo->orders_id)) {
           echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n" .
                '                 <td class="dataTableContent" align="right">' . zen_draw_checkbox_field("orderlist[]",$orders->fields['orders_id']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $oInfo->orders_id . '&action=refresh', 'NONSSL') . '\'">' . $show_status_dots . $orders->fields['orders_id'] . '</td>' . "\n" .
                '                 <td class="dataTableContent" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $oInfo->orders_id . '&action=refresh', 'NONSSL') . '\'"><a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] != '' ? '<br />' . $orders->fields['customers_company'] : '') . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $oInfo->orders_id . '&action=refresh', 'NONSSL') . '\'">' . strip_tags($orders->fields['order_total']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="center" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $oInfo->orders_id . '&action=refresh', 'NONSSL') . '\'">' . zen_date_short($orders->fields['date_purchased']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $oInfo->orders_id . '&action=refresh', 'NONSSL') . '\'">' . $orders_status_array[$orders->fields['orders_status']] . '</td>' . "\n";
         } else {
           echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n" .
                '                 <td class="dataTableContent" align="right">' . zen_draw_checkbox_field("orderlist[]",$orders->fields['orders_id']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $orders->fields['orders_id'] . '&action=refresh', 'NONSSL') . '\'">' . $show_status_dots . $orders->fields['orders_id'] . '</td>' . "\n" .
                '                 <td class="dataTableContent" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $orders->fields['orders_id'] . '&action=refresh', 'NONSSL') . '\'"><a href="' . zen_href_link(FILENAME_CUSTOMERS, 'cID=' . $orders->fields['customers_id'], 'NONSSL') . '">' . zen_image(DIR_WS_ICONS . 'preview.gif', ICON_PREVIEW . ' ' . TABLE_HEADING_CUSTOMERS) . '</a>&nbsp;' . $orders->fields['customers_name'] . ($orders->fields['customers_company'] != '' ? '<br />' . $orders->fields['customers_company'] : '') . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $orders->fields['orders_id'] . '&action=refresh', 'NONSSL') . '\'">' . strip_tags($orders->fields['order_total']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="center" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $orders->fields['orders_id'] . '&action=refresh', 'NONSSL') . '\'">' . zen_date_short($orders->fields['date_purchased']) . '</td>' . "\n" .
                '                 <td class="dataTableContent" align="right" onclick="document.location.href=\'' . zen_href_link(FILENAME_PDFOC, 'oID=' . $orders->fields['orders_id'] . '&action=refresh', 'NONSSL') . '\'">' . $orders_status_array[$orders->fields['orders_status']] . '</td>' . "\n";
         }
      }
?>
              </tr>
<?php
       $list_orders_begin = $orders->fields['orders_id'];
      $orders->MoveNext();
    }
?>
         <input type="hidden" name="orders_begin" value="<?php echo $list_orders_begin; ?>" />
         <input type="hidden" name="orders_end" value="<?php echo $list_orders_end; ?>" />
         </form>
        <!--// show links to prev/next page of results
            //-->
              <tr>
                <td colspan="5"><table border="0" width="100%" cellspacing="0" cellpadding="2">
                  <tr>
                    <td class="smallText" valign="top"><?php echo $orders_split->display_count($orders_query_numrows, PDFOC_ORDERLISTINGMAX_DEFAULT, $_GET['page'], TEXT_DISPLAY_NUMBER_OF_ORDERS); ?></td>
                    <td class="smallText" align="right"><?php echo $orders_split->display_links($orders_query_numrows, PDFOC_ORDERLISTINGMAX_DEFAULT, MAX_DISPLAY_PAGE_LINKS, $_GET['page']); ?></td>
                  </tr>
                </table></td>
              </tr>

       </table></td>
     </tr>
   </table></td>
 </tr>