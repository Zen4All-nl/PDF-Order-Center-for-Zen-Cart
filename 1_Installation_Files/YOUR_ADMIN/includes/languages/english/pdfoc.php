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

// Admin page text settings
//

define('PDFOC_AUTO_CHANGE_STATUS', 'New status:');
define('PDFOC_AUTO_CHANGE_STATUS_HELPTEXT', 'Automatically change order statuses to the selected status. If none, no statuses will be changed.');
define('PDFOC_BILLING', 'Billing:');
define('PDFOC_BILLING_OR_DELIVERY', 'Label address:');
define('PDFOC_BILLING_OR_DELIVERY_HELPTEXT', 'When printing labels : use customer billing address or delivery address?');
define('PDFOC_CHOOSE_TEMPLATE', 'Template:');
define('PDFOC_CHOOSE_TEMPLATE_HELPTEXT', 'Choose the desired printing template; this will be used for all selected orders.');
define('PDFOC_COMMENTS', 'Comments:');
define('PDFOC_COMMENTS_HELPTEXT', 'Optionally specify comments to store in the database with the new status. You can specify a default message to include by checking the
                                  appropriate box above. If printing an invoice, credit, packing slip, or quote
                                  and "print comments" is selected, this comment will also be printed.');
define('PDFOC_COMMENTS_AUTO_SHIPPED', 'Insert standard shipped comment');
define('PDFOC_COMMENTS_AUTO_SHIPPED_TEXT', 'Thank you for your order. Your order has shipped.');
define('PDFOC_COMMENTS_AUTO_CC_DECLINED', 'Insert standard CC declined comment');
define('PDFOC_COMMENTS_AUTO_CC_DECLINED_TEXT', 'Your credit card was declined. Please contact us to arrange another method of payment.');
define('PDFOC_COMMENTS_AUTO_BACKORDER', 'Insert standard backorder comment');
define('PDFOC_COMMENTS_AUTO_BACKORDER_TEXT', 'Items in your order are backordered. We will send you an update as soon as the items arrive.');
define('PDFOC_DATE_FROM', 'From: ');
define('PDFOC_DATE_TO', 'Through: ');
define('PDFOC_DELETE_OPTIONS', 'Delete Orders');
define('PDFOC_DELETE_ORDERS', 'Delete Selected Orders');
define('PDFOC_DELETE_ORDERS_HELPTEXT', 'Delete the orders in the orders list. If Restock is selected, product quantities will be restocked.<br /><br />
                                     You will be asked to confirm before the orders are deleted.');
define('PDFOC_DELIVERY', 'Delivery:');
define('PDFOC_ENTER_CUSTOMER_DATA', 'Search terms:');
define('PDFOC_ENTER_CUSTOMER_DATA_HELPTEXT', 'Enter customer data to search for (part of the
                                  customer, shipping, or billing name or address, telephone number, or ip address).');
define('PDFOC_ENTER_DATES', 'Dates:');
define('PDFOC_ENTER_DATES_HELPTEXT', 'Click to select start and end order creation dates from the calendar, or manually enter the date range
                          (in YYYY-MM-DD format).');
define('PDFOC_ENTER_NUMBERS', 'Order numbers:');
define('PDFOC_ENTER_NUMBERS_HELPTEXT', 'Enter order numbers and/or ranges in ascending order without white space. Example (don\'t enter the quotes):<br />
                            "2577,2580-2585,2588"');
define('PDFOC_ENTER_STATUS', 'Order Status:');
define('PDFOC_ENTER_STATUS_HELPTEXT', 'If specified, only orders with this status will be selected. If none, all statuses will be included.');
define('PDFOC_INVOICE_FINAL', 'Final');
define('PDFOC_INVOICE_FINAL_OR_PREVIEW', 'Final invoice or preview?');
define('PDFOC_INVOICE_FINAL_OR_PREVIEW_HELPTEXT', 'A unique, sequential invoice number will be generated and stored in the database for a final invoice.
                                                 No invoice number will be generated for an invoice preview. See the PDFOC manual for details.');
define('PDFOC_INVOICE_PREVIEW', 'Preview');
define('PDFOC_LABEL_TO_START_ON', 'Start on label:');
define('PDFOC_LABEL_TO_START_ON_HELPTEXT', 'When printing labels : label number to start on<br />
                                (upper left is 1; count along rows)');
define('PDFOC_LEGEND_BILLING_SHIPPING_MISMATCH','Billing != Shipping');
define('PDFOC_LEGEND_INVOICE','Invoice');
define('PDFOC_LEGEND_CREDIT','Credit');
define('PDFOC_NOTIFY_CUSTOMER', 'Notify customer?');
define('PDFOC_NOTIFY_CUSTOMER_HELPTEXT', 'Send an order status update e-mail to each customer.');
define('PDFOC_ORDERS_SELECT_OPTIONS', 'Order Selection Options');
define('PDFOC_ORDERS_SELECT_HELPTEXT', "Please specify customer data search terms, order numbers, dates, and/or order status to select the desired orders.<br />
                                     Missing orders will simply be skipped. The selected orders will then be displayed in the orders list below.");
define('PDFOC_ORDER_STATUS_OPTIONS', 'Order Status Options');
define('PDFOC_PRINT_CC_NO', 'Print credit card number?');
define('PDFOC_PRINT_CC_NO_HELPTEXT', 'Specify whether to print the customer\'s credit card number (if applicable) on invoices, credits, packing slips, and quotes.');
define('PDFOC_PRINT_COMMENTS', 'Print comments?');
define('PDFOC_PRINT_COMMENTS_HELPTEXT', 'Specify whether to print the comments stored with this order on invoices, credits, packing slips, and quotes.');
define('PDFOC_PRINT_EMAIL_ADDRESS', 'Print customer\'s e-mail address?');
define('PDFOC_PRINT_EMAIL_ADDRESS_HELPTEXT', 'Specify whether to print the customer\'s e-mail address on invoices, credits, packing slips, and quotes.');
define('PDFOC_PRINTING_OPTIONS', 'Printing Options');
define('PDFOC_PRINT_ORDER_DATE', 'Print order date?');
define('PDFOC_PRINT_ORDER_DATE_HELPTEXT', 'Specify whether to print the order date on invoices, credits, packing slips, and quotes.');
define('PDFOC_PRINT_PAYMENT_INFO', 'Print payment information?');
define('PDFOC_PRINT_PAYMENT_INFO_HELPTEXT', 'Specify whether to print the customer\'s payment information on invoices, credits, packing slips, and quotes.');
define('PDFOC_PRINT_TEL_NO', 'Print telephone number?');
define('PDFOC_PRINT_TEL_NO_HELPTEXT', 'Specify whether to print the customer\'s telephone number on invoices, credits, packing slips, and quotes.');
define('PDFOC_PROGRAM_MESSAGE', 'NOTIFICATION:');
define('PDFOC_NOTIFY_COMMENTS', 'Include comments?');
define('PDFOC_NOTIFY_COMMENTS_HELPTEXT', 'Include the comments specified here (if any) in the e-mail sent to the customer(s). Note:
                                     each customer will receive the same comments.');
define('PDFOC_SUBMIT_USE_SELECTED', 'Use');
define('PDFOC_SUBMIT_SELECTED_HELPTEXT', 'Click "Use" to select the checked orders in the list below.
                                        Click "Omit" to select the UNCHECKED orders.');
define('PDFOC_SUBMIT_OMIT_SELECTED', 'Omit');
define('PDFOC_TABLE_HEADING_CUSTOMERS', 'Customers');
define('PDFOC_TABLE_HEADING_DATE_PURCHASED', 'Date Purchased');
define('PDFOC_TABLE_HEADING_ORDERS_ID','ID');
define('PDFOC_TABLE_HEADING_ORDER_TOTAL', 'Order Total');
define('PDFOC_TABLE_HEADING_STATUS', 'Status');
define('PDFOC_TEXT_COMMENTS_NO','[No comments]');
define('PDFOC_TEXT_COMMENTS_YES','[Order has comments]');
define('PDFOC_TEXT_DATE_CREDIT_CREATED','Credit Date:');
define('PDFOC_TEXT_DATE_INVOICE_CREATED','Invoice Date:');
define('PDFOC_TEXT_DATE_ORDER_LAST_MODIFIED','Last Modified:');
define('PDFOC_TEXT_INFO_IP_ADDRESS','IP Address:');
define('PDFOC_TEXT_INFO_PAYMENT_METHOD','Payment Method:');
define('PDFOC_TEXT_INFO_SHIPPING_METHOD','Shipping Method:');
define('PDFOC_TEXT_NO', 'NO');
define('PDFOC_TEXT_PRODUCTS_ORDERED', 'Products Ordered: ');
define('PDFOC_TEXT_RESET', 'Reset form');
define('PDFOC_TEXT_RESTOCK', 'Restock?');
define('PDFOC_TEXT_SUBMIT', 'Process Selected orders');
define('PDFOC_TEXT_SUBMIT_HELPTEXT', 'Carry out the printing and status update options selected below on the orders in the orders list.');
define('PDFOC_TEXT_YES', 'YES');

// Errors and messages
//
$pdfoc_error['PDFOC_ALL_SELECTED_FOR_DELETE'] =  'You selected all orders for deletion. To prevent unintended disaster, this is not allowed. No orders have been deleted.';
$pdfoc_error['PDFOC_ERROR_BAD_DATE'] =  'Invalid date: please enter a valid date in Year-Month-Day (0000-00-00) format.';
$pdfoc_error['PDFOC_ERROR_BAD_ORDER_NUMBERS'] =  'Invalid order numbers: please enter a valid format. Do not include white space. (e.g. 2577,2580-2585,2588)';
$pdfoc_error['PDFOC_ERROR_CONFLICTING_SPECIFICATION'] =  'You checked orders in the order list but did not click on "Use" or "Omit". Please retry your query.';
$pdfoc_error['PDFOC_ERROR_INVALID_INPUT'] = 'Internal error: unrecognized or invalid script input.';
$pdfoc_error['PDFOC_FAILED_TO_OPEN'] = 'Could not open PDF file for writing; permissions are not valid. Either change permissions to 777 or delete the existing batch_orders.pdf file in admin/includes/modules/pdfoc/temp_pdf.';
$pdfoc_error['PDFOC_NO_ORDERS'] =  'There were no orders selected for export. Try changing your order options.';
$pdfoc_error['PDFOC_SET_PERMISSIONS'] = 'Can\'t write to directory!  Please set the permissions of your temp_pdf folder to 777.';
$pdfoc_error['PDFOC_NO_SELECTION'] = 'Warning: no selection made, Delete and Submit buttons disabled to prevent PDFOC updating all orders!';

define('PDFOC_MESSAGE_CREDIT_ARE_YOU_SURE', 'At least one selected order does not yet have a credit number. Are you sure you want to create a credit number for every order in the orders list?');
define('PDFOC_MESSAGE_DELETE_ARE_YOU_SURE', 'WARNING! Are you sure you want to DELETE all the orders in the orders list?');

// Note: you can disable the PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE js warning popup by commenting the definition below
define('PDFOC_MESSAGE_SUBMIT_ARE_YOU_SURE', 'Process the selected orders?');

define('PDFOC_MESSAGE_EMAIL_SENT', ' and notification e-mails have been sent to the customers.');  // is second half of sentence that begins with status-was-updated message
define('PDFOC_MESSAGE_ERROR_OCCURRED', 'Uh oh.....an error occurred doing processing, and we don\'t know what it is.');
define('PDFOC_MESSAGE_NO_DEBIT_EXISTS', "Order number %d does not have a debit invoice number, so no credit number has been assigned.");
define('PDFOC_MESSSAGE_ORDERS_WERE_DELETED', 'The selected orders have been deleted.');
define('PDFOC_MESSAGE_SELECTED_ORDERS_SHOWN_BELOW', 'The selected orders are shown below.');
define('PDFOC_MESSAGE_STATUS_WAS_UPDATED', 'The status has been updated for the selected orders');  // don't put a period -- may be collated with email-sent message
define('PDFOC_MESSAGE_SUCCESS', "A PDF of %d page(s) and %d record(s) was successful!
                           <a href=\"%s\" target=\"_blank\" id=\"pdfocMessageLink\"><b>Click here</b></a> to open the file.");


// Settings for the invoice, credit, label, packing slip, etc.
//
define('PDFOC_ENTRY_CC_EXP', 'Expiration Date:');
define('PDFOC_ENTRY_CC_NUMBER', 'Credit Card Number:');
define('PDFOC_ENTRY_CC_OWNER', 'Credit Card Owner:');
define('PDFOC_ENTRY_CREDIT', 'CREDIT');
define('PDFOC_ENTRY_EMAIL', 'E-mail:');
define('PDFOC_ENTRY_INVOICE', 'INVOICE');
define('PDFOC_ENTRY_PACKING_SLIP', 'PACKING SLIP');
define('PDFOC_ENTRY_PHONE', 'Phone:');
define('PDFOC_ENTRY_QUOTE', 'PRICE QUOTE');
define('PDFOC_ENTRY_QUOTE_FOR', 'QUOTE FOR:');
define('PDFOC_ENTRY_PAYMENT_METHOD', 'Payment Method:');
define('PDFOC_ENTRY_PAYMENT_TYPE', 'Credit Card:');
define('PDFOC_ENTRY_SHIPPING', 'Shipping:');
define('PDFOC_ENTRY_SHIP_TO', 'SHIP TO:');
define('PDFOC_ENTRY_SOLD_TO', 'SOLD TO:');
define('PDFOC_ENTRY_SUBTOTAL', 'Subtotal:');
define('PDFOC_ENTRY_TAX', 'Tax:');
define('PDFOC_ENTRY_TOTAL', 'Total:');
define('PDFOC_PAYMENT_TYPE', 'Credit Card');
define('PDFOC_SHIP_FROM_COUNTRY', 'Netherlands');  // used to prevent printing of country for national deliveries
define('PDFOC_TABLE_HEADING_COMMENTS', 'Comments');
define('PDFOC_TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Price (ex)');
define('PDFOC_TABLE_HEADING_PRICE_INCLUDING_TAX', 'Price (inc)');
define('PDFOC_TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('PDFOC_TABLE_HEADING_PRODUCTS', 'Products');
define('PDFOC_TABLE_HEADING_TAX', 'Tax');
define('PDFOC_TABLE_HEADING_TOTAL', 'Total');
define('PDFOC_TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (ex)');
define('PDFOC_TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (inc)');
define('PDFOC_TEXT_COMMENTS','Comments:');
define('PDFOC_TEXT_INVOICE_DATE','Invoice Date:');
define('PDFOC_TEXT_INVOICE_NUMBER','Invoice Number:');
define('PDFOC_TEXT_ORDER_DATE','Order Date:');
define('PDFOC_TEXT_ORDER_FORMAT','F j, Y');
define('PDFOC_TEXT_ORDER_NUMBER','Order Number:');

// Settings for the order update e-mail to the customer
//
define('PDFOC_EMAIL_SALUTATION', 'Dear %s,');
define('PDFOC_EMAIL_SEPARATOR', '-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-');
define('PDFOC_EMAIL_TEXT_COMMENTS_UPDATE', 'Comments concerning your order:' . "\n\n%s\n\n");
define('PDFOC_EMAIL_TEXT_DATE_ORDERED', 'Date Ordered:');
define('PDFOC_EMAIL_TEXT_INVOICE_URL', 'Detailed Invoice:');
define('PDFOC_EMAIL_TEXT_ORDER_NUMBER', 'Order Number:');

// Be sure to replace the e-mail address in the following line with the correct one for your store:
define('PDFOC_EMAIL_IF_QUESTIONS', "If you have any questions, please contact our customer service department at service@yourstore.com.\n\n");
define('PDFOC_EMAIL_SIGNOFF', "Sincerely,\n\n");

// Note: the array indices correspond to your order statuses; add additional entries if you
// have more statuses. Your defined order statuses are listed in the Admin panel
// under Localization::Order Status.
// These will be used as both the e-mail subject and the first
// line of the e-mail text, so don't capitalize all the words. The
// final period will be added in when the e-mail is sent, so don't
// include it here.
$pdfoc_subject[1] = 'Your order #%d has been received';
$pdfoc_subject[2] = 'Payment for your order #%d has been received';
$pdfoc_subject[3] = 'Your order #%d has been sent';
$pdfoc_subject[4] = 'Here is an update on your order #%d';
$pdfoc_subject[5] = 'Your order #%d has been cancelled';

?>