<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
    <meta http-equiv="content-type" content="text/html; charset=<?php echo zm_i18n('HTML_CHARSET') ?>" />
    <meta name="generator" content="ZenMagick <?php echo ZMSettings::get('ZenMagickVersion') ?>" />
    <meta name="keywords" content="<?php ZMMetaTags::instance()->getKeywords()?>" />
    <meta name="description" content="<?php ZMMetaTags::instance()->getDescription()?>" />
    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("site.css") ?>" />
    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("theme.css") ?>" />
    <title><?php ZMMetaTags::instance()->getTitle() ?></title>
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
      <?php include $zm_theme->themeFile("top-menu.php") ?>
    </div>	
          
    <!-- content-wrap starts here -->
    <div id="content-wrap">		
                        
    <img src="<?php $zm_theme->themeURL("images/headerphoto.jpg") ?>" width="820" height="120" alt="headerphoto" class="no-border" />
      
      <div id="sidebar" >							
        <?php if (ZMLayout::instance()->isLeftColEnabled()) { ?>
          <div id="leftcol">
            <?php foreach (ZMLayout::instance()->getLeftColBoxNames() as $box) { ?>
                <?php include $zm_theme->themeFile("boxes/" .$box) ?>
            <?php } ?>
          </div>
        <?php } ?>
      </div>
        
      <div id="main">	
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
        
      <div id="rightbar">
      <?php if (ZMLayout::instance()->isRightColEnabled()) { ?>
        <div id="rightcol">
          <?php foreach (ZMLayout::instance()->getRightColBoxNames() as $box) { ?>
              <?php include $zm_theme->themeFile("boxes/" .$box) ?>
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
        <?php $first = true; foreach (ZMEZPages::instance()->getPagesForFooter() as $page) { ?>
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
