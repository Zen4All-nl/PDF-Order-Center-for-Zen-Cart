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

require('includes/application_top.php');
?>
<!doctype html>
<html <?php echo HTML_PARAMS; ?>>
  <head>
    <meta charset="<?php echo CHARSET; ?>">
    <title><?php echo TITLE; ?></title>
    <link rel="stylesheet" href="includes/stylesheet.css">
    <link rel="stylesheet" href="includes/css/pdfoc.css">
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
      <div class="row main">
        <span class="dataTableHelptext"><a class="helptextPopup" id="pdfocGeneralHelptext" href="<?php echo zen_href_link(FILENAME_PDFOC, 'action=refresh'); ?>"><?php echo PDFOC_HELPTEXT_ICON; ?><span><?php echo PDFOC_GENERAL_HELPTEXT; ?></span></a></span>
        <?php echo PDFOC_TEXT_HOVER_FOR_HELP; ?>
      </div>
      <div class="row text-right"><a href="<?php echo zen_href_link(FILENAME_PDFOC); ?>" class="btn btn-default" role="button"><?php echo PDFOC_LINK_MAINPAGE; ?></a></div>

      <?php echo PDFOC_FORMATTED_TEXT_MANUAL; ?>

      <!-- body_eof //-->
    </div>
    <!-- footer //-->
    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    <!-- footer_eof //-->
  </body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>