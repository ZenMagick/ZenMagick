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
    <meta charset="<?php echo $settingsService->get('zenmagick.http.html.charset') ?>" />
    <title><?php echo $this->fragment('page.title') ?></title>
    <?php $this->fragment('page.title', $metaTags->getTitle()) ?>
    <meta name="keywords" content="" />  <!-- come back and fill in theses meta tags later  -->
    <meta name="description" content="" />
    <?php $resourceManager->cssFile('style.css', array('media' => 'screen')) ?>
    <?php $resourceManager->cssFile('theme.css', array('media' => 'screen')) ?>
    <?php $resourceManager->jsFile('common.js', $resourceManager::FOOTER) ?>
  </head>
  <body>
    <?php define('KEYWORD_DEFAULT', ''); ?>
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
        <?php echo $this->fetch('top-menu.php') ?>
      </div> <!-- end of menu div -->
    </div> <!-- end of header div  -->

	<div id="pagebar">  <!-- insert page menu bar here  -->
		  <div id="pagemenu">
          <?php echo $this->fetch('page_menu.php') ?>
                </div> <!-- end pagemenu  -->
	</div>  <!-- end page pagebar  -->

	<!-- start page -->
    <div id ="colmask" class="threecol">
		<div id="colmid">
			<div id="colleft">
				<!-- start content center column  -->
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
      </div>
      <!-- end content center column-->

				<!-- start leftcol here -->
				<?php if ($templateManager->isLeftColEnabled()) { ?>

				<div id="sidebar1" class="sidebar">
				<ul>
	        <?php echo $this->fetchBlockGroup('leftColumn', array('format' => '<li>%s</li>')) ?>
				</ul>
			</div>    <!-- end leftcol here -->
      <?php } ?>



		<!-- begin RightCol material  -->
      <?php if ($templateManager->isRightColEnabled()) { ?>
        <!-- start sidebar2 -->
        <div id="sidebar2" class="sidebar">
          <ul>
	          <?php echo $this->fetchBlockGroup('rightColumn', array('format' => '<li>%s</li>')) ?>
		      </ul>
        </div>
        <!-- end RightCol -->
      <?php } ?>

      <div style="clear: both;">&nbsp;</div>

	</div>  <!-- end of colleft  -->
	</div>  <!-- end of colmid  -->
	</div>    <!-- end colmask threecol -->




    <div id="footer">
      <p class="legal">&copy;2009-2012 All Rights Reserved - &nbsp;<strong>Your Web Store Site</strong>&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;| Powered by <strong><a href="http://www.zenmagick.org">ZenMagick</a></strong></p>
      <p class="credit">Design by <strong>You the chief designer</strong></p>
    </div> <!-- end of footer div  -->
	</div> <!-- end of container div  -->

  </body>
</html>
