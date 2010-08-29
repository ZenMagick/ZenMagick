<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
 *
 * Portions Copyright (c) 2003 The zen-cart developers
 * Portions Copyright (c) 2003 osCommerce
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
 *
 * $Id$
 */
?>
<?php

    // allow for custom layout settings without having to copy the whole file every time...
    $pageLayout = "layout/".$request->getRequestId().".php";
    if ($this->exists($pageLayout)) {
        echo $this->fetch($pageLayout);
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <title><?php echo $metaTags->getTitle() ?></title>
    <base href="<?php echo $request->getPageBase() ?>" />
    <meta http-equiv="content-type" content="text/html; charset=<?php echo ZMSettings::get('zenmagick.mvc.html.charset') ?>" />
    <meta name="generator" content="ZenMagick <?php echo ZMSettings::get('zenmagick.version') ?>" />
    <meta name="keywords" content="<?php echo $metaTags->getKeywords()?>" />
    <meta name="description" content="<?php echo $metaTags->getDescription()?>" />
    <?php $resources->cssFile('site.css') ?>
    <?php $resources->cssFile('ie.css', array('prefix' => '<!--[if IE]>', 'suffix' => '<![endif]-->')) ?>
    <?php $resources->jsFile('common.js', ZMViewUtils::FOOTER) ?>
    <?php /* give other themes the chance to add to the default CSS without having to copy everything */ ?>
    <?php if ($this->exists("theme.css")) { ?>
        <?php $resources->cssFile('theme.css') ?>
    <?php } ?>
    <?php $pageCSS = "css/".$request->getRequestId().".css"; ?>
    <?php /* page specific CSS */ ?>
    <?php if ($this->exists($pageCSS)) { ?>
        <?php $resources->cssFile($pageCSS) ?>
    <?php } ?>
    <?php if (!ZMTemplateManager::instance()->isLeftColEnabled() || !ZMTemplateManager::instance()->isRightColEnabled()) { ?>
      <style type="text/css" media="screen,projection">
        <?php if (!ZMTemplateManager::instance()->isLeftColEnabled()) { ?>
          #content {margin-left:10px;}
        <?php } ?>
        <?php if (!ZMTemplateManager::instance()->isRightColEnabled()) { ?>
          body div#content {margin-right:20px;}
        <?php } ?>
      </style>
    <?php } ?>
  </head>

  <body id="b_<?php echo $request->getRequestId() ?>">
    <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('header1'))) { ?>
        <div id="bannerOne"><?php echo $macro->showBanner($bannerBox); ?></div>
    <?php } ?>

    <div id="container">
      <?php echo $this->fetch('header.php') ?>
      <?php echo $this->fetch('menu.php') ?>

      <div id="leftcol">
        <!-- block::leftColumn -->
      </div>

      <div id="rightcol">
        <!-- block::rightColumn -->
      </div>

      <div id="content">
        <?php if ('index' != $request->getRequestId()) { /* this is the actual view, not neccessarily what is in the URL */ ?>
            <?php echo $macro->buildCrumbtrail($crumbtrail, " &gt; "); ?>
        <?php } ?>

        <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('header3'))) { ?>
            <div id="bannerThree"><?php echo $macro->showBanner($bannerBox); ?></div>
        <?php } ?>

        <?php if (ZMMessages::instance()->hasMessages()) { ?>
            <ul id="messages">
            <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
            <?php } ?>
            </ul>
        <?php } ?>

        <?php echo $this->fetch($viewTemplate); ?>

        <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('footer1'))) { ?>
            <div id="bannerFour"><?php echo $macro->showBanner($bannerBox); ?></div>
        <?php } ?>
      </div>

      <?php echo $this->fetch('footer.php') ?>
    </div>

    <?php if (null != ($bannerBox = ZMBanners::instance()->getBannerForSet('footer3'))) { ?>
        <div id="bannerSix"><?php echo $macro->showBanner($bannerBox); ?></div>
    <?php } ?>

  </body>
</html>
