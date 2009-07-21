<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us" lang="en-us">
<!--

Name       : Precision Reloaded
Description: A three-column, fluid design suitable for news sites, shopping carts and blogs.
Version    : 1.0
Released   : 20090515

-->
  <head>
   <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
    <title>Your Title Here </title>
    <meta name="keywords" content="" />  <!-- come back and fill in theses meta tags later  -->
    <meta name="description" content="" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("style.css") ?>" />
    <link rel="stylesheet" type="text/css" media="screen" href="<?php $zm_theme->themeURL("theme.css") ?>" />
    <!-- this script is an external script call - OK no need for CDATA tags  -->
    <script type="text/javascript" src="<?php $zm_theme->themeURL("common.js") ?>"></script>
  </head>
  <body>

	<div id="container">  <!-- wrap the entire package  -->
	<div id="header"> <!-- for the logo image set we will use a CSS background solution here  -->
      <div id="logo">       
		<!--	<img src="xxx.jpg" alt="" class="logoimage" />   -->
			
		<h1>Zenmagick <br/> Enterprises <br/></h1>
        <h2>Online</h2>
      </div>  <!-- end of logo div  -->
     
	 <div id="creatacct"> <!-- insert the create account here  -->
	 <a href="http://www.zenmagick.org/index.php?main_page=create_account"> Create Your Account </a>
	 </div>
	  <div id="menu">
        <?php include $zm_theme->themeFile("top-menu.php") ?>
      </div> <!-- end of menu div -->
    </div> <!-- end of header div  -->
	
	<div id="pagebar">  <!-- insert page menu bar here  -->
		  <div id="pagemenu">
          <?php include $zm_theme->themeFile("page_menu.php") ?>
                </div> <!-- end pagemenu  -->
	</div>  <!-- end page pagebar  -->

	<!-- start page -->
    <div id ="colmask" class="threecol">
		<div id="colmid">  
			<div id="colleft">
				<!-- start content center column  -->
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
      <!-- end content center column-->			
			
				<!-- start leftcol here -->
				<?php if (ZMTemplateManager::instance()->isLeftColEnabled()) { ?>
				
				<div id="sidebar1" class="sidebar">
				<ul>
					<?php foreach (ZMTemplateManager::instance()->getLeftColBoxNames() as $box) { ?>
					<li>
					<?php include $zm_theme->themeFile("boxes/" .$box) ?>
					</li>
					<?php } ?>
				
				</ul>
			</div>    <!-- end leftcol here -->
      <?php } ?>

      
	  
		<!-- begin RightCol material  -->
      <?php if (ZMTemplateManager::instance()->isRightColEnabled()) { ?>
        <!-- start sidebar2 -->
        <div id="sidebar2" class="sidebar">
          <ul>
            <?php foreach (ZMTemplateManager::instance()->getRightColBoxNames() as $box) { ?>
              <li>
                  <?php include $zm_theme->themeFile("boxes/" .$box) ?>
              </li>
            <?php } ?>
 
		  </ul>
        </div>
        <!-- end RightCol -->
      <?php } ?>

      <div style="clear: both;">&nbsp;</div>
    
	</div>  <!-- end of colleft  -->
	</div>  <!-- end of colmid  -->
	</div>    <!-- end colmask threecol -->
	
	
	
	
    <div id="footer">
      <p class="legal">&copy;2009 All Rights Reserved - &nbsp;<strong>Your Web Store Site</strong>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;| Powered by <strong><a href="http://www.zenmagick.org">ZenMagick</a></strong></p>
      <p class="credit">Design by <strong>You the chief designer</strong></p>
    </div> <!-- end of footer div  -->
	</div> <!-- end of container div  -->
	
  </body>
</html>
