<?php

/*  PDF Order Center 1.0 for Zen Cart v1.2.6d  and v1.2.7d
 *  By Grayson Morris, 2006
 *  Printing sections based on Batch Print Center for osCommerce by Shaun Flanagan
 *
 * Released under the Gnu General Public License (see GPL.txt)
 *
 * pdfoc_header.php
 *
 */
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/pdfoc.css">
<link rel="stylesheet" type="text/css" href="includes/javascript/spiffyCal/spiffyCal_v2_1.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
<!--
function init()
{
cssjsmenu('navbar');
if (document.getElementById)
{
var kill = document.getElementById('hoverJS');
kill.disabled = true;
}
}
// -->
</script>
<script language="javascript">
function addAutoComment(frmobj)
{
frmobj.comments.value=""
for(i=0;i<frmobj.autocomment.length;i++)
{
if(frmobj.autocomment[i].checked)
{frmobj.comments.value+=frmobj.autocomment[i].value}
}
}
</script>
<script language="javascript" src="includes/javascript/spiffyCal/spiffyCal_v2_1.js"></script>
<script language="javascript">
var dateAvailable=new ctlSpiffyCalendarBox("dateAvailable", "pdfoc_selection", "startdate","btnDate1","",scBTNMODE_CUSTOMBLUE);
var dateAvailable1=new ctlSpiffyCalendarBox("dateAvailable1", "pdfoc_selection", "enddate","btnDate2","",scBTNMODE_CUSTOMBLUE);

</script>
</head>
<body onload="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<div id="spiffycalendar" class="text"></div>  
<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="0">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="3">
          <tr>
          <td class="dataTableHelptext" width="15"><a class="helptextPopup" id="pdfocGeneralHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC,'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_GENERAL_HELPTEXT; ?></span></a></td>
          <td class="pageHeading"><?php echo PDFOC_HEADING_TITLE; ?></td>
          <td class="main" align="left"><?php echo PDFOC_TEXT_HOVER_FOR_HELP; ?></td>
          <td class="pageHeading" align="right"><?php echo zen_draw_form('pdfoc_manual', FILENAME_PDFOC_MANUAL); ?><input type="submit" value="<?php echo PDFOC_LINK_MANUAL; ?>" /></form></td>
          <td class="pageHeading" align="right"><?php echo zen_draw_form('pdfoc_main', FILENAME_PDFOC); ?><input type="submit" value="<?php echo PDFOC_LINK_MAINPAGE; ?>" /></form></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="2">