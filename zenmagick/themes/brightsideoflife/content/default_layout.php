<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo zm_i18n('HTML_CHARSET') ?>" />
    <meta name="generator" content="ZenMagick <?php echo ZMSettings::get('zenmagick.version') ?>" />
    <meta name="keywords" content="<?php $request->getMetaTags()->getKeywords()?>" />
    <meta name="description" content="<?php $request->getMetaTags()->getDescription()?>" />
    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("site.css") ?>" />
    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("theme.css") ?>" />
    <title><?php $request->getMetaTags()->getTitle() ?></title>
  </head>
  <body>
  <!-- wrap starts here -->
  <div id="wrap">
    
    <div id="header">				
      <h1 id="logo"><span class="green">Zen</span><span class="blue">Magick</span></h1>	
      <h2 id="slogan">As simple as that!</h2> 
  
      <form method="post" class="searchform" action="#">
        <p><input type="text" name="search_query" class="textbox" />
          <input type="submit" name="search" class="button" value="Search" /></p>
      </form>
        
      <!-- Menu Tabs -->
      <ul>
        <?php
          $menu = array();
          $menu[] = array($net->url(FILENAME_DEFAULT, '', false, false), zm_l10n_get("Home"));
          if ($request->isAnonymous()) {
              $menu[] = array($net->url(FILENAME_LOGIN, '', true, false), zm_l10n_get("Login"));
          }
          if ($request->isRegistered()) {
              $menu[] = array($net->url(FILENAME_ACCOUNT, '', true, false), zm_l10n_get("Account"));
          }
          if (!$request->isAnonymous()) {
              $menu[] = array($net->url(FILENAME_LOGOFF, '', true, false), zm_l10n_get("Logoff"));
          }
          if (!$request->getShoppingCart()->isEmpty() && !$request->isCheckout()) {
              $menu[] = array($net->url(FILENAME_SHOPPING_CART, '', true, false), zm_l10n_get("Cart"));
              $menu[] = array($net->url(FILENAME_CHECKOUT_SHIPPING, '', true, false), zm_l10n_get("Checkout"));
          }
          foreach (ZMEZPages::instance()->getPagesForHeader($session->getLanguageId()) as $page) {
              $menu[] = array($html->ezpageLink($page->getId(), '<span>'.$html->encode($page->getTitle(), false).'</span>', array(), false));
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
                        
    <img src="<?php $zm_theme->themeURL("images/headerphoto.jpg") ?>" width="820" height="120" alt="headerphoto" class="no-border" />
      
      <div id="sidebar" >							
        <?php if (ZMTemplateManager::instance()->isLeftColEnabled()) { ?>
          <div id="leftcol">
            <?php foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $box) { ?>
                <?php echo $this->fetch('boxes/'.$box) ?>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
        
      <div id="main">	
        <?php if (!ZMLangUtils::inArray($request->getRequestId(), 'index')) { /* this is the actual view, not neccessarily what is in the URL */ ?>
            <?php echo $macro->buildCrumbtrail($request->getCrumbtrail(), " &gt; "); ?>
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
        
      <div id="rightbar">
      <?php if (ZMTemplateManager::instance()->isRightColEnabled()) { ?>
        <div id="rightcol">
          <?php foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) { ?>
              <?php echo $this->fetch('boxes/'.$box) ?>
          <?php } ?>
        </div>
      <?php } ?>

      </div>			
        
    <!-- content-wrap ends here -->		
    </div>

  <!-- footer starts here -->	
  <div id="footer">
    
    <div class="footer-left">
      <p class="align-left">			
      &copy; 2008 <strong>ZenMagick</strong> |
      Design by <a href="http://www.styleshout.com/">styleshout</a> |
      Valid <a href="http://validator.w3.org/check/referer">XHTML</a> |
      <a href="http://jigsaw.w3.org/css-validator/check/referer">CSS</a>
      </p>		
    </div>
    
    <div class="footer-right">
      <p class="align-right">
        <?php $first = true; foreach (ZMEZPages::instance()->getPagesForFooter($session->getLanguageId()) as $page) { ?>
            <?php if (!$first) { echo '&nbsp;|&nbsp;'; } $first = false; ?>
            <?php $html->ezpageLink($page->getId()) ?>
        <?php } ?>
      </p>
    </div>
    
  </div>
  <!-- footer ends here -->
    
  <!-- wrap ends here -->
  </div>

  </body>
</html>
