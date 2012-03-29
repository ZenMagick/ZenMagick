<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
    <meta name="generator" content="ZenMagick <?php echo $settingsService->get('zenmagick.version') ?>" />
    <meta name="keywords" content="<?php echo $metaTags->getKeywords()?>" />
    <meta name="description" content="<?php echo $metaTags->getDescription()?>" />
    <?php $resourceManager->cssFile('site.css') ?>
    <?php $resourceManager->cssFile('theme.css') ?>
    <title><?php echo $metaTags->getTitle() ?></title>
  </head>
  <body>
  <!-- wrap starts here -->
  <div id="wrap">

    <div id="header">
      <h1 id="logo"><span class="green">Zen</span><span class="blue">Magick</span></h1>
      <h2 id="slogan">As simple as that!</h2>

      <form method="POST" class="searchform" action="#">
        <p><input type="text" name="search_query" class="textbox" />
          <input type="submit" name="search" class="button" value="Search" /></p>
      </form>

      <!-- Menu Tabs -->
      <ul>
        <?php
          $menu = array();
          $menu[] = array($net->url('index'), _zm("Home"));
          if ($request->isAnonymous()) {
              $menu[] = array($net->url('login', '', true), _zm("Login"));
          }
          if ($request->isRegistered()) {
              $menu[] = array($net->url('account', '', true), _zm("Account"));
          }
          if (!$request->isAnonymous()) {
              $menu[] = array($net->url('logoff', '', true), _zm("Logoff"));
          }
          if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) {
              $menu[] = array($net->url('shopping_cart', '', true), _zm("Cart"));
              $menu[] = array($net->url('checkout_shipping', '', true), _zm("Checkout"));
          }
          foreach ($container->get('ezPageService')->getPagesForHeader($session->getLanguageId()) as $page) {
              $menu[] = array($html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle()).'</span>', array()));
          }
          foreach ($menu as $item) {
              if (2 == count($item)) {
                $current = ZMTools::compareStoreUrl($item[0]) ? ' id="current"' : '';
                ?><li<?php echo $current ?>><a href="<?php echo $item[0] ?>"><span><?php echo $item[1] ?></span></a></li><?php
              } else {
                $current = '';
                //TODO:
                //preg_match('/.*href=[\'"]([^\'"]*)[\'"].*/', $item[0], $matches);
                //$current = ZMTools::compareStoreUrl($matches[1]) ? ' id="current"' : '';
                ?><li<?php echo $current ?>><?php echo $item[0] ?></li><?php
              }
          }
        ?>
      </ul>
    </div>

    <!-- content-wrap starts here -->
    <div id="content-wrap">

    <img src="<?php echo $this->asUrl("images/headerphoto.jpg") ?>" width="820" height="120" alt="headerphoto" class="no-border" />

      <div id="sidebar" >
        <div id="leftcol">
          <?php echo $this->fetchBlockGroup('leftColumn') ?>
        </div>
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

      <div id="rightbar">
        <div id="rightcol">
          <?php echo $this->fetchBlockGroup('rightColumn') ?>
        </div>
      </div>

    <!-- content-wrap ends here -->
    </div>

  <!-- footer starts here -->
  <div id="footer">

    <div class="footer-left">
      <p class="align-left">
      &copy; 2008-2012 <strong>ZenMagick</strong> |
      Design by <a href="http://www.styleshout.com/">styleshout</a> |
      Valid <a href="http://validator.w3.org/check/referer">XHTML</a> |
      <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
      </p>
    </div>

    <div class="footer-right">
      <p class="align-right">
        <?php $first = true; foreach ($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()) as $page) { ?>
            <?php if (!$first) { echo '&nbsp;|&nbsp;'; } $first = false; ?>
            <?php echo $html->ezpageLink($page->getId()) ?>
        <?php } ?>
      </p>
    </div>

  </div>
  <!-- footer ends here -->

  <!-- wrap ends here -->
  </div>

  </body>
</html>
