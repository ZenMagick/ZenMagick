<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>

    <meta name="Description" content="Information architecture, Web Design, Web Standards." />
    <meta name="Keywords" content="your, keywords" />
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <meta name="Distribution" content="Global" />
    <meta name="Author" content="Erwin Aligam - ealigam@gmail.com" />
    <meta name="Robots" content="index,follow" />

    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("PixelGreen.css") ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("theme.css") ?>" />

    <script type="text/javascript" src="<?php $zm_theme->themeURL("common.js") ?>"></script>

    <title>Pixel Green</title>
  </head>

  <body>
    <!-- wrap starts here -->
    <div id="wrap">

      <div id="header"><div id="header-content">	
        
        <h1 id="logo"><a href="<?php $net->url(FILENAME_DEFAULT) ?>" title="">Zen<span class="gray">Magick</span></a></h1>	
        <h2 id="slogan">As simple as that!</h2>		
        
        <!-- Menu Tabs -->
        <?php include $zm_theme->themeFile("top-menu.php") ?>
      </div></div>
      
      <div class="headerphoto"></div>
            
      <!-- content-wrap starts here -->
      <div id="content-wrap"><div id="content">		
        
        
        <?php if (ZMTemplateManager::instance()->isRightColEnabled()) { ?>
          <div id="sidebar" >
            <?php foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) { ?>
                <div class="sidebox">
                    <?php include $zm_theme->themeFile("boxes/" .$box) ?>
                </div>
            <?php } ?>
          </div>
        <?php } ?>

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
            <?php foreach (ZMEZPages::instance()->getPagesForFooter() as $page) { ?>
                <li><?php $html->ezpageLink($page->getId()) ?></li>
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
          <li><a href="<?php $net->url(FILENAME_DEFAULT) ?>"><strong>Home</strong></a></li>
        </ul>	
        </div>
      
    </div></div>
    <!-- footer ends here -->
      
    <!-- wrap ends here -->
    </div>

  </body>
</html>
