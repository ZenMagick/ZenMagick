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
    <?php $resources->cssFile('style.css', array('media' => 'screen')) ?>
    <?php $resources->cssFile('theme.css', array('media' => 'screen')) ?>
    <?php $resources->jsFile('common.js', ZMViewUtils::FOOTER) ?>
  </head>
  <body>
    <div id="header">
      <div id="logo">
        <h1><a href="<?php echo $net->url(FILENAME_DEFAULT) ?>">ZenMagick</a></h1>
        <h2>As simple as that!</h2>
      </div>
      <div id="menu">
        <?php echo $this->fetch('top-menu.php') ?>
      </div>
    </div>
    <!-- start page -->
    <div id="page">

      <?php if (ZMTemplateManager::instance()->isLeftColEnabled()) { ?>
        <!-- start sidebar1 -->
        <div id="sidebar1" class="sidebar">
          <ul>
            <?php foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $box) { ?>
              <?php if ($this->exists('boxes/'.$box)) { ?>
                <li>
                <?php echo $this->fetch('boxes/'.$box) ?>
                </li>
              <?php } ?>
            <?php } ?>
          </ul>
        </div>
        <!-- end sidebar1 -->
      <?php } ?>

      <!-- start content -->
      <div id="content">
          <?php if (!ZMLangUtils::inArray($request->getRequestId(), 'index')) { /* this is the actual view, not neccessarily what is in the URL */ ?>
              <?php echo $macro->buildCrumbtrail($crumbtrail, " &gt; "); ?>
          <?php } ?>

          <?php if (ZMMessages::instance()->hasMessages()) { ?>
              <ul id="messages">
              <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                  <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
              <?php } ?>
              </ul>
          <?php } ?>
        
          <?php echo $this->fetch($viewTemplate); ?>
      </div>
      <!-- end content -->

      <?php if (ZMTemplateManager::instance()->isRightColEnabled()) { ?>
        <!-- start sidebar2 -->
        <div id="sidebar2" class="sidebar">
          <ul>
            <?php foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) { ?>
              <?php if ($this->exists('boxes/'.$box)) { ?>
                <li>
                <?php echo $this->fetch('boxes/'.$box) ?>
                </li>
              <?php } ?>
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
