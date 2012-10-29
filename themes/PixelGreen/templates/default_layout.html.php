<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <!-- Theme created by Erwin Aligam - ealigam@gmail.com -->
    <?php echo $this->fetch('head.html.php'); ?>
    <?php $resourceManager->cssFile('css/PixelGreen.css', array('media' => 'screen')) ?>
    <?php $resourceManager->cssFile('css/theme.css', array('media' => 'screen')) ?>
    <?php $resourceManager->jsFile('js/common.js', $resourceManager->FOOTER) ?>
  </head>

  <body>
    <?php define('KEYWORD_DEFAULT', _zm("enter search")); ?>
    <!-- wrap starts here -->
    <div id="wrap">

      <div id="header"><div id="header-content">

        <h1 id="logo"><a href="<?php echo $net->url('index') ?>" title="">Zen<span class="gray">Magick</span></a></h1>
        <h2 id="slogan">As simple as that!</h2>

        <!-- Menu Tabs -->
        <?php echo $this->fetch('top-menu.html.php') ?>
      </div></div>

      <div class="headerphoto"></div>

      <!-- content-wrap starts here -->
      <div id="content-wrap"><div id="content">

        <div id="sidebar" >
          <?php echo $this->fetchBlockGroup('rightColumn', array('format' => '<div class="sidebox">%s</div>')) ?>
        </div>

        <div id="main">
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

      <!-- content-wrap ends here -->
      </div></div>

    <!-- footer starts here -->
    <div id="footer"><div id="footer-content">

        <div class="col float-left">
          <h1>Site Partners</h1>
          <ul>
            <li><a href="http://www.zenmagick.org/"><strong>ZenMagick</strong> - zen-cart the easy way</a></li>
          </ul>
        </div>

        <div class="col float-left">
          <h1>Links</h1>
          <ul>
            <?php foreach ($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()) as $page) { ?>
                <li><?php echo $html->ezpageLink($page->getId()) ?></li>
            <?php } ?>
          </ul>
        </div>

        <div class="col2 float-right">
        <p>
        Powered by <strong><a href="http://www.zenmagick.org">ZenMagick</a></strong>
        <br />
        Design by: <a href="http://www.styleshout.com/"><strong>styleshout</strong></a> &nbsp; &nbsp;
        Valid <a href="http://jigsaw.w3.org/css-validator/check/referer"><strong>CSS</strong></a> |
              <a href="http://validator.w3.org/check/referer"><strong>XHTML</strong></a>
        </p>

        <ul>
          <li><a href="<?php echo $net->url('index') ?>"><strong>Home</strong></a></li>
        </ul>
        </div>

    </div></div>
    <!-- footer ends here -->

    <!-- wrap ends here -->
    </div>

  </body>
</html>
