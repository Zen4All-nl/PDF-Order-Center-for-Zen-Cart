<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 *  pdfoc_manual.php
 *
 */

define('PDFOC_FORMATTED_TEXT_MANUAL','<a name="top"></a><h2 class="pdfoc">Contents</h2>
<a href="#1">What Is PDF Order Center?</a><br />
<a href="#2">Customizing PDF Order Center</a><br />
<a href="#3">About On-the-Fly Invoice Numbering</a><br />
<a href="#4">How to Use PDF Order Center</a><br />
<ul>
  <li><a href="#5">Order Selection via the Selecton Control Panel</a></li>
  <li><a href="#6">Order Selection via the Orders List</a></li>
  <li><a href="#7">Deleting Orders</a></li>
  <li><a href="#8">Printing Options</a></li>
  <li><a href="#9">Order Status Options</a></li>
  <li><a href="#10">Specifying Multiple Actions</a></li>
</ul>
<a href="#11">The Minimally Useful Credit Hack</a><br />
<a href="#12">Removing Unwanted Templates from the Templates List</a><br />
<a href="#13">How to Set Up Your Templates</a><br />
<a href="#14">How to Alter a Template</a><br />
<a href="#15">How to Create a New Template</a><br />


<a name="1"></a><h1 class="pdfoc">What Is PDF Order Center?</h1>
<p class="pdfoc">
PDF Order Center is an alternative to the default ZC order processing setup
in Admin::Customer::Orders. Like the default setup, PDFOC allows you to update
order status, optionally including comments, optionally e-mailing the customer;
delete an order; and print an invoice or packing slip for an order. But
PDFOC also allows you to:
</p><p class="pdfoc">
<ul>
  <li>    process multiple orders at a time (batch processing)  </li>
  <li>    print shipping labels </li>
  <li>    print price quotes and credit invoices  </li>
  <li>    make minor customizations to the invoice format from the PDFOC control panel </li>
  <li>    create a unique, sequential invoice number for each order </li>
</ul></p><p class="pdfoc">
And as the name implies, PDFOC creates PDF invoices, packing slips, etc. PDFOC
works with a template system, which allows you to easily add additional document
formats as desired.
</p><p class="pdfoc">
An additional feature built in to PDFOC is On-the-Fly Invoice Numbering (OTFIN).
This gives you the option of creating a unique, sequential invoice number for
each order at the moment of printing. OTFIN uncouples the order number from the
invoice number, so that cancelled or deleted orders no longer cause gaps in your
invoice numbering system. OTFIN also allows you to create a credit for an order
(necessary for e.g. cancelled orders for which an invoice number has already been
generated). See below (The Minimally Useful Credit Hack) for more information
on creating credits.
</p>
<p><a href="#top">Top</a></p>
<a name="2"></a><h1 class="pdfoc">Customizing PDF Order Center</h1>
<h2 class="pdfoc">The PDFOC Page Look</h2>
<p class="pdfoc">
You can change the settings in admin/includes/pdfoc.css to alter the helptext
color, helptext location, and text decoration for the "click here" link when
you\'ve created a PDF file.
</p><p class="pdfoc">
The PDFOC testers felt that the "click here" link was easy to overlook when you
first start using PDFOC, so by default that link blinks. If blinking links irritate
the heck out of you, by all means change this in the css file.
</p>

<h2 class="pdfoc">The PDFOC Page Defaults</h2>
<p class="pdfoc">
You can specify the desired default settings for most items on the PDFOC page
by selecting Configuration::PDF Order Center from the Admin navigation bar.
</p>
<p><a href="#top">Top</a></p>

<a name="3"></a><h1 class="pdfoc">About On-the-Fly Invoice Numbering</h1>
<p class="pdfoc">
On-the-Fly Invoice Numbering offers an alternative to the ZC default practice of
using the order number as the invoice number.
</p><p class="pdfoc">
Good bookkeeping practice, and some national tax authorities, require an unbroken
sequence of invoice numbers (the "audit trail"). Using the ZC order number as
invoice number, however, can create problems if orders are deleted or are cancelled.
</p><p class="pdfoc">
OTFIN ensures that your actual invoices are numbered successively
and dated appropriately by creating a distinct invoice number and date at the time
of printing. It also ensures that the invoice date is the actual date on
which the invoice is generated rather than the order date. OTFIN also allows you
to create a credit invoice for each invoiced order.
</p><p class="pdfoc">
Only one credit invoice and one debit invoice can be created per order. The credit
invoice is an all-or-nothing affair: it effectively cancels out the order. If you
need to issue a credit invoice because a customer has changed his order (added or
removed items), remember two things:
</p><p class="pdfoc">
<ul>
<li> You need not bother with a credit invoice until you have created a final
   debit (=regular) invoice for the order. </li>

<li> If you have already created a final debit invoice for the order, OTFIN\'s structure
   requires that you create a credit invoice (which nulls out the order),
   cancel the order, and create a new order with the updated order data.
   You can then create a new invoice for the new order. (Naturally, you can
   apply any payment already made by the customer to the new invoice.)
   You may find the Master Password contribution to be of use to you in
   creating new orders. </li>
</ul>
</p>
<p><a href="#top">Top</a></p>

<a name="4"></a><h1 class="pdfoc">How to Use PDF Order Center</h1>
<p class="pdfoc">
By default, the link to PDFOC is under the Admin::Customers menu. This brings up
the main PDFOC page. The page consists of an action control panel on the left-hand side
and a selection control panel and orders list on the right-hand side. When you
first access PDFOC, the orders list displays all orders.
</p><p class="pdfoc">
You can now select orders and specify actions to be carried out on them: for example,
printing an invoice or deleting the order.
</p><p class="pdfoc">
You can select orders in one of two ways: by specifying criteria for them in the
Order Selection control panel, or by checking them in the orders list. The selected
orders will be displayed in the orders list once you submit your selection. You can
then view details about each order in the orders list by clicking somewhere in that
order\'s row. The detailed order information will appear in the top infobox on
the right-hand side of the page.
</p><p class="pdfoc">
To print, delete, or change status for orders, remember this general rule:
select the orders; select the action; submit the action. The orders list always contains
the currently selected orders. These are the orders that will be affected if you
specify printing, status update, or deletion; make sure this list contains
exactly the orders you wish to affect before submitting the action control panel form.
</p><p class="pdfoc">
If you hover over the question mark beside items on the PDFOC page, a helptext
will appear. If you click on a question mark, the page forms will be reset (among
other things, this will display all orders in the orders list).
</p><p class="pdfoc">
You can specify the desired default settings for most items on the PDFOC page
by selecting Configuration::PDF Order Center from the Admin navigation bar.
</p>
<p><a href="#top">Top</a></p>

<a name="5"></a><h2 class="pdfoc">Order Selection via the Selecton Control Panel</h2>
<p class="pdfoc">
You can enter a search string, order numbers, start and end dates, and/or an order
status. These values are combined in an AND structure; that is, only orders
meeting all specified criteria will be selected.
</p>
<h3 class="pdfoc">Search String Examples:</h3>
<p class="pdfoc">
<ul>
     <li>        jones  </li>
     <li>        iowa   </li>
     <li>        261.12.13 </li>
     <li>        yahoo.com </li>
</ul>
</p><p class="pdfoc">
Orders containing the specified string will be selected. The string will be sought in
the customer, billing, and shipping name and address, customer IP address, customer
e-mail address, and customer telephone number.
</p>
<h3 class="pdfoc">Order Number Examples:</h3>
<p class="pdfoc">
<ul>
  <li>           359  </li>
  <li>           1300-1320  </li>
  <li>           2567,4209,4233-4238,4313 </li>
  <li>           1-10000000 </li>
</ul>
</p><p class="pdfoc">
Enter the numbers and/or ranges without white space. All orders in the specified
string will be selected. If there are orders missing
in a specified range, those numbers will simply be skipped; no empty invoice
or label will be created when printing.
</p><p class="pdfoc">
<h3 class="pdfoc">Date Examples:</h3>
</p><p class="pdfoc">
<ul>
   <li>          select dates using attached calendars  </li>
   <li>          enter dates manually in YYYY-MM-DD format:
         <ul>
                  <li>   2005-10-02   </li>
                  <li>   2003-01-31    </li>
         </ul>
</li></ul>
</p><p class="pdfoc">
All orders between 00:00:00 on the start date and 23:59:59 on the end date will
be selected.
</p><p class="pdfoc">
If you have selected an order status, only orders with that status (and meeting
the other selection criteria) will be selected.
</p><p class="pdfoc">
Once you have specified your selection criteria, press the "Submit Options" button at the
top right of the selection control panel. The new selection will then be
displayed in the orders list.
</p>
<p><a href="#top">Top</a></p>

<a name="6"></a><h2 class="pdfoc">Order Selection via the Orders List</h2>
<p class="pdfoc">
Alternatively, you can select orders from the orders list on the right-hand side
of the page. Check the desired orders. There are two methods for submitting your
selection:
</p><p class="pdfoc">
<ul>
   <li>Use : if you click this button, the CHECKED orders will be selected. </li>

   <li>Omit : if you click this button, the UNCHECKED orders will be selected. </li>
</ul>
</p><p class="pdfoc">
The set of orders used in determining the selection is limited to orders on the
current page. If the orders list currently contains more orders than fit onto
one page, only those orders visible on the page you are currently viewing will
be used in determining the new selection. (You can adjust the number of orders
shown per page under Admin::Configuration::PDF Order Center.)
</p><p class="pdfoc">
You must click either "Use" or "Omit" to select orders from the orders list. If you
check orders in the orders list and then press the selection control panel "Submit Options"
button or the "Enter" key, you will see a message to this effect.
</p>
<p><a href="#top">Top</a></p>

<a name="7"></a><h2 class="pdfoc">Deleting Orders</h2>
<p class="pdfoc">
You can delete the currently selected orders en masse by clicking this button on
the action control panel. Check your selection in the orders list thoroughly to
make sure you have exactly the orders you want.
</p><p class="pdfoc">
You can specify whether to restock the products from the deleted orders.
</p><p class="pdfoc">
If the orders list contains more orders than fit onto the current page, note that
deletion will be carried out on ALL orders in the orders list, even if
they are not on the currently visible page.
</p><p class="pdfoc">
You will be prompted to confirm the delete. The delete will be carried out ONLY
if you click "OK".
</p>
<p><a href="#top">Top</a></p>

<a name="8"></a><h2 class="pdfoc">Printing Options</h2>
<p class="pdfoc">
If you do not wish to print anything, select "None" as the template. Otherwise
select the desired template from the list. Specify the remaining printing options
as you wish. Then press the "Submit Options" button at the top of the action
control panel (the left-hand side of the page). A single PDF file will be created
that contains a copy of the chosen template for each order selected.
</p><p class="pdfoc">
The remaining printing options are applicable to some, but not all, of the available
templates. When printing shipping labels, for example, you can specify whether
to use the customer\'s billing address or the shipping address for that order.
When printing an invoice or packing slip, you can specify whether to include
the order date, customer telephone number, customer e-mail address, and payment
information in the document. Select the desired options before pressing "Submit Options".
</p>

<h3 class="pdfoc">Shipping Labels</h3>
<p class="pdfoc">
You can specify which label to begin on when printing shipping labels. That
way, you can print (say) 7 labels off on a new label sheet, then use that same
label sheet the next time you print labels, beginning at label #8. To specify
a start label, start counting with #1 in the upper left corner of your label
sheet and count across rows:
</p><p class="pdfoc">
<pre>
               1  2  3
               
               4  5  6
               
               7  8  9
               
               ......
</pre>
</p><p class="pdfoc">
PDFOC automatically begins a new page in the PDF document starting at label #1
if you are printing more labels than will fit onto a single label sheet. For
example, if your label sheets contain 15 labels, you start printing at label #8
on the first sheet, and you are printing labels for 30 orders, PDFOC will create
a PDF with three pages: 8 labels (#8-#15) on the first page, 15 labels (#1-#15)
on the second page, and 7 labels (#1-#7) on the third page.
</p><p class="pdfoc">
Note: when you first install PDFOC, you must adjust the label template for your
particular label format. See the next section, How to Set Up Your Templates.
</p>
<p><a href="#top">Top</a></p>

<a name="9"></a><h2 class="pdfoc">Order Status Options</h2>
<p class="pdfoc">
If you do not wish to change the order status for the selected orders, choose "None".
Otherwise select the new status to apply to the selected orders.
</p><p class="pdfoc">
If you select "None", the remaining status options will not be used, even if
selected.
</p><p class="pdfoc">
You can optionally type in comments for this status change to store in the
orders_history table in the database.
</p><p class="pdfoc">
You can optionally notify each customer of the status change via e-mail, optionally
including the comments you typed in. Because this is a batch process, each
customer will receive the same comments. Note: You can customize the e-mail
subject and text to match your store\'s order statuses in
admin/includes/languages/english/pdfoc.php. (Repeat for each language in your
store.)
</p>
<p><a href="#top">Top</a></p>

<a name="10"></a><h2 class="pdfoc">Specifying Multiple Actions</h2>
<p class="pdfoc">
You can specify printing options and status update options at the same time. Both
will be carried out on the selected orders.
</p>
<p><a href="#top">Top</a></p>

<a name="11"></a><h1 class="pdfoc">The Minimally Useful Credit Hack</h1>
<p class="pdfoc">
In PDFOC, you can credit an order (if, for example, the order was cancelled). This
functionality actually comes from another contribution, OTFIN (On-the-Fly Invoice
Numbering), and has been built in to PDFOC. If you don\'t ever need to use it,
no problem; but if you do, read on.
</p><p class="pdfoc">
Your crediting options are exceedingly limited. You can credit the entire order.
There is no way to select individual items from an order to credit. You\'d need a
much more sophisticated order editing system to do that.
</p><p class="pdfoc">
If in the future a contribution becomes available for ZC that does allow you to
create credits with full-blown options, I will be glad to (try to) modify PDFOC
to work with it. In the meantime, consider the "all or nothing" credit in PDFOC
just a little extra something to use if it is convenient.
</p>
<p><a href="#top">Top</a></p>

<a name="12"></a><h1 class="pdfoc">Removing Unwanted Templates from the Templates List</h1>
<p class="pdfoc">
PDFOC comes with several standard templates. You may never want to use some of
them. In that case, it can be annoying to have them show up in the drop-down
templates menu.
</p><p class="pdfoc">
PDFOC determines the available templates by looking for files ending in .php in
the templates directory. To keep a template from showing up (without deleting
the template altogether), either move it out of the templates directory, or
give it a different extension (such as .php.bak).
</p>
<p><a href="#top">Top</a></p>

<a name="13"></a><h1 class="pdfoc">How to Set Up Your Templates</h1>
<p class="pdfoc">
After you\'ve installed PDFOC, you should generate test invoices, shipping labels,
etc. to see if the format and sizes work for you. Everything about the layout
can be altered, but be warned; some alterations will require you to learn about
the internals of the pdf class. Other alterations are as simple as changing a
constant value. This section describes the minimum alterations you will need to
make to use PDFOC on your store.
</p>

<h3 class="pdfoc">Shipping Labels</h3>
</p><p class="pdfoc">
Open modules/pdfoc/templates/Labels.php and make the following changes:
<ul>
  <li>  - set the proper number of rows and columns for your label sheets </li>
  <li>  - set the proper width and height for your labels </li>
</ul>
</p>
<p><a href="#top">Top</a></p>

<h3 class="pdfoc">Invoice, Credit, Price Quote and Packing Slip</h3>

<p class="pdfoc">These templates use the store name and address stored in the database under
STORE_NAME_ADDRESS. The templates assume a five-line name and address. For
example:
<pre>
        Butterwing Crafts
        Epinalpad 7
        5627LT Eindhoven
        Giro 33.4546.01
        KvK 17175193
</pre>
or
<pre>
        Beter Elektro
        A. Beceelaan 101
        2025JJ Haarlem
        http://www.beterelektro.nl
        info@beterelektro.nl
</pre>
or
<pre>
        Sally\'s Designs
        1234 Dressup Lane
        Fancypants, CA 99999
        Tel 415-999-9999
        Fax 415-999-9990
</pre>
</p><p class="pdfoc">
Use whatever you like to fill out to five lines (if you use fewer--or more--you\'ll
have to significantly adjust the template settings).
</p><p class="pdfoc">
You will also probably need to adjust the column widths in the products table
displayed in these forms. These values are found near the top of the template
files:
<pre>
  define(\'PDFOC_PRODUCTS_COLUMN_SIZE\', \'165\');
  define(\'PDFOC_MODEL_COLUMN_SIZE\', \'40\');
  define(\'PDFOC_PRICING_COLUMN_SIZES\', \'64\');
</pre>
Note that if you increase one column size, you\'ll need to decrease others to
compensate. Here\'s where you can set the total width of the table:
<pre>
  define(\'PDFOC_PRODUCT_TABLE_HEADER_WIDTH\', \'517\');
</pre>
Make sure you keep it within the page width:
<pre>
  define(\'PDFOC_LINE_LENGTH\', \'552\');
</pre>
Here\'s a rough calculation to help you keep things aligned:
<pre>
PDFOC_PRODUCTS_COLUMN_SIZE +  PDFOC_MODEL_COLUMN_SIZE + 4*PDFOC_PRICING_COLUMN_SIZES = PDFOC_PRODUCT_TABLE_HEADER_WIDTH
</pre>
It isn\'t exact because there may be some padding on the table (see
PDFOC_PRODUCT_TABLE_LEFT_MARGIN in the templates), but it\'s a decent approximation.
</p><p class="pdfoc">
These templates use a company logo, named invoicelogo.jpg, which is found in the
templates folder. Replace this file with your own logo, using the same name.
</p><p class="pdfoc">
The default invoicelogo.jpg is 85 x 85 pixels. Your logo will probably have
different dimensions. Its printed size should NOT exceed about 300w x 100h pixels.
Note that print resolution is different from screen resolution; be sure to save your
image at larger size than specified in the PDF to prevent jagged, unattractive
printing.
</p><p class="pdfoc">
To fit your logo onto the page, you will need to change the following line
in each of these templates:
</p><p class="pdfoc">
<pre>
  // logo image (x offset from left, y offset from bottom, width, height)
  $pdf->addJpegFromFile(DIR_BPC_TEMPLATES . \'invoicelogo.jpg\',365,730,85,85);
</pre>
</p><p class="pdfoc">
Change the last two numbers to reflect your logo\'s width and height. Change the
first number to move the logo left or right; change the second number to move
it up or down. Note: PDF pages have the origin (0,0) in the bottom left corner.
Horizontal values increase to the right (as expected); vertical values increase
going upward. This means that a y-value of 730 is near the top of the page, and
a y-value of 10 is near the bottom of the page. Moreover, the y offset in
the line above specifies the offset of the BOTTOM of the image with respect to
the bottom of the page.
</p>
<p><a href="#top">Top</a></p>

<h3 class="pdfoc">Letterhead</h3>
<p class="pdfoc">
This template also uses the five-line store name and address. It also uses
both the company logo mentioned above for invoices and packing
slips, and a watermark image. Again, you will need to change this line:
</p><p class="pdfoc">
<pre>
  // logo image (x offset from left, y offset from bottom, width, height)
  $pdf->addJpegFromFile(DIR_BPC_TEMPLATES . \'invoicelogo.jpg\',365,730,85,85);
</pre>
</p><p class="pdfoc">
You will also need to upload a watermark image (named watermark.jpg) to the
templates folder, and alter this line accordingly:
</p><p class="pdfoc">
<pre>
  // add watermark (x offset from left, y offset from bottom, width, height)
  $pdf->addJpegFromFile(DIR_BPC_TEMPLATES . \'watermark.jpg\',30,110,550,550);
</pre>
</p>
<p><a href="#top">Top</a></p>

<h3 class="pdfoc">Christmas Card</h3>
<p class="pdfoc">
This template also uses an image, christmascard.jpg. You might like the default
image, but if not, replace it with your own (same name) and alter this line
accordingly:
</p><p class="pdfoc">
<pre>
  // image (x offset from left, y offset from bottom, width, height)
  $pdf->addJpegFromFile(DIR_BPC_TEMPLATES . \'christmascard.jpg\',0,0,590,820);
</pre>
</p><p class="pdfoc">
NOTE: christmascard.jpg should contain an upside-down image. This template
creates a card to be folded out of a single A4 sheet. Try it out before changing
anything and you\'ll see what I mean.
</p>
<p><a href="#top">Top</a></p>

<h3 class="pdfoc">Grid</h3>
<p class="pdfoc">
Not truly a template in its own right, but rather a tool for helping you line
up elements in another template. It needs no adjustments.
</p>
<p><a href="#top">Top</a></p>

<a name="14"></a><h1 class="pdfoc">How to Alter a Template</h1>
<p class="pdfoc">
If you want to make other adjustments to these templates, such as changing the
font sizes or the locations of particular elements, by all means experiment. But
be warned that this is very precise work, and changing the size of one font will
cascade into changes throughout the document: table widths and row heights will
then need to be changed, which will cause specified x and y positions to need to be
changed.....once you get familiar with it, it isn\'t hard, but it does have a
learning curve.
</p><p class="pdfoc">
Documentation for the PDF class used in PDFOC can be found at
<a href="http://www.ros.co.nz/pdf/readme.pdf">http://www.ros.co.nz/pdf/readme.pdf</a>.
</p><p class="pdfoc">
The PDF document is created from the template by means of the pdf class. Using
the methods in this class, you specify the exact size and x and y location of each
element on the screen.
</p><p class="pdfoc">
It\'s important to note that elements are built up like layers. The first item
you place will lie underneath all subsequent items at that location. If you want
a gray table background, for example, make sure you draw that BEFORE placing
the text you want to display in the table.
</p><p class="pdfoc">
You will want to familiarize yourself with the working of at least three methods
in the pdf class: ezText(), addText(), and ezSetY(). You can read through the class
files in the modules/pdfoc folder for the gory details.
</p><p class="pdfoc">
In a nutshell:
</p><p class="pdfoc">
<b>$newypos = addText($xpos, $ypos, $font_size, $text)</b> takes an absolute x and y
position, a font size, and the text to print and puts it on the page with the
bottom-left corner of the text at the specified x and y. It returns the new y
position (vertical location on the page), which can be useful to you.
</p><p class="pdfoc">
<b>$newypos = ezText($text,$font_size,$options=array())</b> prints the specified text
at the current page location. If you don\'t specify a font size, the last size
specified will be used. You can optionally include an array to specify the
line justification, left and/or right indents, and left and/or right absolute
positions on the line. ezText() returns the new y position on the page (which
will generally not be the y position on which it started). See the function
definition in class.ezpdf.php in the modules/pdfoc folder.
</p><p class="pdfoc">
<b>ezSetY($y)</b> sets the vertical location of the document at $y.
</p><p class="pdfoc">
One thing to note is that you can specify the vertical location on the page
with a call to ezSetY(), but you cannot specify the horizontal location (there
is no ezSetX() method). This means that you will need to use the optional array
settings in ezText() to line up your text horizontally (or use addText()).
</p><p class="pdfoc">
addText() allows you to specify the exact position where your text begins;
ezText() allows you to justify your text (crucial for lining up monetary amounts
properly under one another). Each will be more useful in certain situations.
</p><p class="pdfoc">
If you use ezText(), note that you will need to place a call to ezSetY() before
every call to ezText() that should fall on the same horizontal line. Lines 208-228
in the Invoice.php template illustrate this.
</p><p class="pdfoc">
<p><a href="#top">Top</a></p>

<a name="15"></a><h1 class="pdfoc">How to Create a New Template</h1>
<p class="pdfoc">
You are encouraged to create a template for any documents you would like to
have. Please share them with the ZC community as well!
</p><p class="pdfoc">
It\'s probably easiest to start with a template that looks somewhat like
the one you want, and modify from there using the tips in the section above
(How to Alter a Template). If you do start from scratch, note that the
following is the minimum required in a template file:
</p><p class="pdfoc">
<pre>
if ($pageloop == "0") {   // initialize pdf settings

  $pdf = new Cezpdf(A4,portrait); // change for your desired paper / orientation

  $pdf->selectFont(DIR_BPC_FONTS . \'Helvetica.afm\');
  $pdf->setFontFamily(DIR_BPC_FONTS . \'Helvetica.afm\');

} else {  // print out an X document

  // your pdf calls go here
}
</pre>
</p><p class="pdfoc">
If you want to use different fonts, store the desired fonts in the fonts
folder.
</p><p class="pdfoc">
The name you give your template will be its display value in the drop-down
template menu on the PDF Order Center page. "-" and "_" are replaced by spaces.
Only files ending in .php will be included in the menu.
</p><p class="pdfoc">');
?>
