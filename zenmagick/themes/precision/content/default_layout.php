<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<!--
Design by Free CSS Templates
http://www.freecsstemplates.org
Released for free under a Creative Commons Attribution 2.5 License

Name       : Precision
Description: A three-column, fixed-width design suitable for news sites and blogs.
Version    : 1.0
Released   : 20081126

-->
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <title>Precision by Free CSS Templates</title>
    <meta name="keywords" content="" />
    <meta name="description" content="" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("style.css") ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("theme.css") ?>" />
    <script type="text/javascript" src="<?php $zm_theme->themeURL("common.js") ?>"></script>
  </head>
  <body>
    <div id="header">
      <div id="logo">
        <h1><a href="#">ZenMagick</a></h1>
        <h2><a href="<?php $net->url(FILENAME_DEFAULT) ?>">As simple as that!</a></h2>
      </div>
      <div id="menu">
        <?php include $zm_theme->themeFile("top-menu.php") ?>
      </div>
    </div>
    <!-- start page -->
    <div id="page">

      <?php if (ZMLayout::instance()->isLeftColEnabled()) { ?>
        <!-- start sidebar1 -->
        <div id="sidebar1" class="sidebar">
          <ul>
            <?php foreach (ZMLayout::instance()->getLeftColBoxNames() as $box) { ?>
              <li>
                  <?php include $zm_theme->themeFile("boxes/" .$box) ?>
              </li>
            <?php } ?>
          </ul>
        </div>
        <!-- end sidebar1 -->
      <?php } ?>

      <!-- start content -->
      <div id="content">
          <?php if (!ZMTools::inArray($zm_view->getName(), 'index')) { /* this is the actual view, not neccessarily what is in the URL */ ?>
              <?php echo $macro->buildCrumbtrail(ZMCrumbtrail::instance(), " &gt; "); ?>
          <?php } ?>

          <?php if (ZMMessages::instance()->hasMessages()) { ?>
              <ul id="messages">
              <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                  <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
              <?php } ?>
              </ul>
          <?php } ?>
        
          <?php if ($zm_view->isViewFunction()) { $zm_view->callView(); } else { include($zm_view->getViewFilename()); } ?>
      </div>
      <!-- end content -->

      <?php if (ZMLayout::instance()->isRightColEnabled()) { ?>
        <!-- start sidebar2 -->
        <div id="sidebar2" class="sidebar">
          <ul>
            <?php foreach (ZMLayout::instance()->getRightColBoxNames() as $box) { ?>
              <li>
                  <?php include $zm_theme->themeFile("boxes/" .$box) ?>
              </li>
            <?php } ?>
          </ul>
        </div>
        <!-- end sidebar2 -->
      <?php } ?>

      <div style="clear: both;">&nbsp;</div>
    </div>
    <!-- end page -->
    <div id="footer">
      <p class="legal">&copy;2008 All Rights Reserved. | Powered by <strong><a href="http://www.zenmagick.org">ZenMagick</a></strong></p>
      <p class="credit">Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a></p>
    </div>
  </body>
</html>
