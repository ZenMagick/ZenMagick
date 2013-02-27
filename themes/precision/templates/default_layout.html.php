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
    <?php echo $this->fetch('head.html.php') ?>
    <?php $resourceManager->cssFile('css/style.css', array('media' => 'screen')) ?>
    <?php $resourceManager->cssFile('css/theme.css', array('media' => 'screen')) ?>
    <?php $resourceManager->jsFile('js/common.js', $resourceManager->FOOTER) ?>
  </head>
  <body>
    <?php define('KEYWORD_DEFAULT', _zm("enter search")); ?>
    <div id="header">
      <div id="logo">
        <h1><a href="<?php echo $net->generate('index') ?>">ZenMagick</a></h1>
        <h2>As simple as that!</h2>
      </div>
      <div id="menu">
        <?php echo $this->fetch('top-menu.html.php') ?>
      </div>
    </div>
    <!-- start page -->
    <div id="page">

      <?php if ($templateManager->isLeftColEnabled()) { ?>
        <!-- start sidebar1 -->
        <div id="sidebar1" class="sidebar">
          <ul>
              <?php echo $this->fetchBlockGroup('leftColumn', array('format' => '<li>%s</li>')) ?>
          </ul>
        </div>
        <!-- end sidebar1 -->
      <?php } ?>

      <!-- start content -->
      <div id="content">
          <div id="crumbtrail"><?php echo $this->fragment('crumbtrail') ?></div>

          <?php if ($messageService->hasMessages()) { ?>
              <ul id="messages">
              <?php foreach ($messageService->getMessages() as $message) { ?>
                  <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
              <?php } ?>
              </ul>
          <?php } ?>

          <?php echo $this->fetch($viewTemplate); ?>
          <?php $this->fragment('crumbtrail', $macro->buildCrumbtrail($crumbtrail, " &gt; ")) ?>
      </div>
      <!-- end content -->

      <?php if ($templateManager->isRightColEnabled()) { ?>
        <!-- start sidebar2 -->
        <div id="sidebar2" class="sidebar">
          <ul>
              <?php echo $this->fetchBlockGroup('rightColumn', array('format' => '<li>%s</li>')) ?>
          </ul>
        </div>
        <!-- end sidebar2 -->
      <?php } ?>

      <div style="clear: both;">&nbsp;</div>
    </div>
    <!-- end page -->
    <div id="footer">
      <p class="legal">&copy;2008-2012 All Rights Reserved. | Powered by <strong><a href="http://www.zenmagick.org">ZenMagick</a></strong></p>
      <p class="credit">Design by <a href="http://www.freecsstemplates.org/">Free CSS Templates</a></p>
    </div>
  </body>
</html>
