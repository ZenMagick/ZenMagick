<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" dir="<?php echo $html->getDir() ?>">
  <head>
    <?php echo $this->fetch('head.php') ?>
    <?php $resourceManager->cssFile('css/site.css') ?>
    <?php $resourceManager->cssFile('css/ie.css', array('prefix' => '<!--[if IE]>', 'suffix' => '<![endif]-->')) ?>
    <?php $resourceManager->jsFile('js/common.js', $resourceManager->FOOTER) ?>
    <?php /* give other themes the chance to add to the default CSS without having to copy everything */ ?>
    <?php if ($this->exists("resource:css/theme.css")) { ?>
        <?php $resourceManager->cssFile('css/theme.css') ?>
    <?php } ?>
    <?php $pageCSS = "resource:css/".$request->getRequestId().".css"; ?>
    <?php /* page specific CSS */ ?>
    <?php if ($this->exists($pageCSS)) { ?>
        <?php $resourceManager->cssFile($pageCSS) ?>
    <?php } ?>
    <?php if (!$templateManager->isLeftColEnabled() || !$templateManager->isRightColEnabled()) { ?>
      <style type="text/css" media="screen,projection">
        <?php if (!$templateManager->isLeftColEnabled()) { ?>
          #content {margin-left:10px;}
        <?php } ?>
        <?php if (!$templateManager->isRightColEnabled()) { ?>
          body div#content {margin-right:20px;}
        <?php } ?>
      </style>
    <?php } ?>
  </head>

  <body id="b_<?php echo $request->getRequestId() ?>">
    <div id="bannerOne"><?php echo $this->fetchBlockGroup('banners.header1') ?></div>

    <div id="mainWrapper">
      <?php echo $this->fetch('header.php') ?>

      <div id="contentMainWrapper">
        <div id="crumbtrail"><?php echo $this->fragment('crumbtrail') ?></div>

       	 <?php if ($container->get('messageService')->hasMessages()) { ?>
            <ul id="messages">
           		<?php foreach ($container->get('messageService')->getMessages() as $message) { ?>
                	<li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
            	<?php } ?>
            </ul>
		      <?php } ?>

       	 <div id="contentWrapper">
		      <?php if ($templateManager->isLeftColEnabled()) { ?>
		        <div id="navColumnOne" class="columnLeft back">
			        <?php echo $this->fetchBlockGroup('leftColumn', array('format' => '<div class="leftBoxContainer">%s<div class="sbFooter"></div></div>')) ?>
		        </div>
		      <?php } ?>

		      <?php $bgConent = ('index' == $request->getRequestId()) ? '' : 'bgContent ';?>
		      <div id="mainColumn" class="<?php echo $bgConent; ?>forward">
		        <div id="mainColumnWrapper">
			        <div id="bannerThree"><?php echo $this->fetchBlockGroup('banners.header3') ?></div>
			        <?php echo $this->fetch($viewTemplate); ?>
			        <div id="bannerFour"><?php echo $this->fetchBlockGroup('banners.footer1') ?></div>
		        </div>
		      </div>
	      </div>
	      <div class="clearBoth"></div>
	  </div>

      <?php echo $this->fetch('footer.php') ?>
      <?php $this->fragment('crumbtrail', $macro->buildCrumbtrail($crumbtrail, " &gt; ")) ?>
    </div>

    <div id="bannerSix"><?php echo $this->fetchBlockGroup('banners.footer3') ?></div>

  </body>
</html>
