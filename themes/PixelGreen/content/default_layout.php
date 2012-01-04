<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>

    <meta name="Description" content="Information architecture, Web Design, Web Standards." />
    <meta name="Keywords" content="your, keywords" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Author" content="Erwin Aligam - ealigam@gmail.com" />
    <meta name="Robots" content="index,follow" />
    <?php $resourceManager->cssFile('PixelGreen.css', array('media' => 'screen')) ?>
    <?php $resourceManager->cssFile('theme.css', array('media' => 'screen')) ?>
    <?php $resourceManager->jsFile('common.js', $resourceManager->FOOTER) ?>
    <title>Pixel Green</title>
  </head>

  <body>
    <!-- wrap starts here -->
    <div id="wrap">

      <div id="header"><div id="header-content">

        <h1 id="logo"><a href="<?php echo $net->url('index') ?>" title="">Zen<span class="gray">Magick</span></a></h1>
        <h2 id="slogan">As simple as that!</h2>

        <!-- Menu Tabs -->
        <?php echo $this->fetch('top-menu.php') ?>
      </div></div>

      <div class="headerphoto"></div>

      <!-- content-wrap starts here -->
      <div id="content-wrap"><div id="content">


        <div id="sidebar" >
          <?php echo $this->fetchBlockGroup('rightColumn', array('format' => '<div class="sidebox">%s</div>')) ?>
        </div>

        <div id="main">
          <?php if (!ZMLangUtils::inArray($request->getRequestId(), 'index')) { ?>
              <?php echo $macro->buildCrumbtrail($crumbtrail, " &gt; "); ?>
          <?php } ?>

          <?php if ($container->get('messageService')->hasMessages()) { ?>
              <ul id="messages">
              <?php foreach ($container->get('messageService')->getMessages() as $message) { ?>
                  <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
              <?php } ?>
              </ul>
          <?php } ?>

          <?php echo $this->fetch($viewTemplate); ?>

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
