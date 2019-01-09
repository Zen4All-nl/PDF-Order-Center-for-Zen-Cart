SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

SELECT @pdfocid:=configuration_group_id
FROM configuration_group
WHERE configuration_group_title='PDF Order Center';

DELETE FROM configuration
WHERE configuration_group_id=@pdfocid;

DELETE FROM configuration_group
WHERE configuration_group_id=@pdfocid
LIMIT 1;



CREATE TABLE IF NOT EXISTS orders_invoices (
orders_invoices_id int(11) NOT NULL auto_increment,
invoice_date datetime NOT NULL default '0001-01-01 00:00:00',
order_tax decimal(15,4) NOT NULL default '0.0000',
order_total decimal(15,4) NOT NULL default '0.0000',
invoice_type char(2) NOT NULL default '',
orders_id int(11) NOT NULL default '0',
PRIMARY KEY (orders_invoices_id)
) ENGINE=MyISAM;

SELECT @sortorder:=max(sort_order)
FROM configuration_group;

INSERT INTO configuration_group (configuration_group_title, configuration_group_description, sort_order, visible)
VALUES ('PDF Order Center', 'User-customizable defaults for PDF Order Center.', @sortorder+1, 1);

SELECT @pdfocid:=max(configuration_group_id)
FROM configuration_group;

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Order Listing: max entries per page?', 'PDFOC_ORDERLISTINGMAX_DEFAULT', '100', 'Determines the maximum number of orders shown per page in the orders list. Only orders on the current page will be included when pressing the Use and Omit buttons.', @pdfocid , 5, NULL, NULL );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Deletion: restock quantities default?', 'PDFOC_RESTOCK_DEFAULT', 'true', 'If true, the PDFOC action control panel option to restock product quantities when deleting orders will be checked by default.', @pdfocid , 10, NULL, NULL );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Order Selection: default status?', 'PDFOC_SELECTIONSTATUS_DEFAULT', '', 'Determines the default order status used for order selection on the PDFOC selection control panel. NOTE: A setting other than "None" will always limit the selected orders to that status, unless you select "None" manually before submitting the PDFOC form.', @pdfocid , 20, 'zen_get_order_status_name_plus_none', 'zen_cfg_pull_down_order_statuses_plus_none(' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: default template?', 'PDFOC_TEMPLATE_DEFAULT', 'Invoice.php', 'Determines the default printing template selected in the drop-down menu on the PDFOC action control panel. NOTE: A setting other than "None" will always invoke printing, unless you select "None" manually before submitting the PDFOC form.', @pdfocid , 30, 'pdfoc_get_template_name' , 'pdfoc_cfg_pull_down_templates(' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: final invoice or preview?', 'PDFOC_FINAL_PREVIEW_DEFAULT', 'preview', 'Determines the default setting on the PDFOC action control panel when printing invoices.', @pdfocid , 40, NULL , 'zen_cfg_select_option(array(\'preview\', \'final\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: default label address?', 'PDFOC_LABELADDRESS_DEFAULT', 'delivery', 'Determines the default setting on the PDFOC action control panel when printing labels.', @pdfocid , 50, NULL , 'zen_cfg_select_option(array(\'delivery\', \'billing\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: order date checked?', 'PDFOC_ORDERDATE_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print the order date on invoices, packing slips, etc will be checked by default.', @pdfocid , 60, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: customer e-mail checked?', 'PDFOC_EMAIL_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print the customer e-mail on invoices, packing slips, etc will be checked by default.', @pdfocid , 70, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: telephone number checked?', 'PDFOC_TELEPHONE_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print the customer telephone number on invoices, packing slips, etc will be checked by default.', @pdfocid , 80, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: payment information checked?', 'PDFOC_PAYMENTINFO_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print payment information for the order on invoices, packing slips, etc will be checked by default.', @pdfocid , 90, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: credit card number checked?', 'PDFOC_CCNO_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print the customer credit card number on invoices, packing slips, etc will be checked by default.', @pdfocid , 100, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: comments checked?', 'PDFOC_PRINTCOMMENTS_DEFAULT', 'true', 'If true, the PDFOC action control panel option to print the comments stored in the database for this order on invoices, packing slips, etc will be checked by default.', @pdfocid , 110, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Printing: hide Printing Options?', 'PDFOC_HIDE_PRINTING_OPTIONS', 'false', 'If true, the Printing Options form will be hidden.', @pdfocid , 115, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Status Update: default status?', 'PDFOC_NEWSTATUS_DEFAULT', '', 'Determines the default order status selected for status updates on the PDFOC action control panel. NOTE: A setting other than "None" will always update the selected orders with that status, unless you select "None" manually before submitting the PDFOC form.', @pdfocid , 120, 'zen_get_order_status_name_plus_none', 'zen_cfg_pull_down_order_statuses_plus_none(' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Status Update: notify customer checked?', 'PDFOC_NOTIFYCUSTOMER_DEFAULT', 'true', 'If true, the PDFOC action control panel option to notify the customer via e-mail when performing order status updates will be checked by default.', @pdfocid , 130, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO configuration ( configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function )
VALUES ( 'Status Update: include comments checked?', 'PDFOC_NOTIFYCOMMENTS_DEFAULT', 'true', 'If true, the PDFOC action control panel option to include the current comments in the customer notification e-mail will be checked by default.', @pdfocid , 140, NULL , 'zen_cfg_select_option(array(\'true\', \'false\'),' );

INSERT INTO admin_pages (page_key, language_key, main_page, page_params, menu_key, display_on_menu, sort_order)
VALUES ('configPdfoc', 'BOX_PDFOC', 'FILENAME_CONFIGURATION', 'gID=77', 'configuration', 'Y', @pdfocid),
       ('pdfoc', 'BOX_CUSTOMERS_PDFOC', 'FILENAME_PDFOC', '', 'customers', 'Y', @pdfocid);

COMMIT;