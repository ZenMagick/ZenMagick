<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
    $pageLayout = "layout/".$zm_view->getName().".php";
    if ($zm_theme->themeFileExists($pageLayout)) {
        include $zm_theme->themeFile($pageLayout);
    }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
  <head>
    <title><?php ZMMetaTags::instance()->getTitle() ?></title>
    <base href="<?php echo $zm_request->getPageBase() ?>" />
    <meta http-equiv="content-type" content="text/html; charset=<?php echo zm_i18n('HTML_CHARSET') ?>" />
    <meta name="generator" content="ZenMagick <?php echo zm_setting('ZenMagickVersion') ?>" />
    <meta name="keywords" content="<?php ZMMetaTags::instance()->getKeywords()?>" />
    <meta name="description" content="<?php ZMMetaTags::instance()->getDescription()?>" />
    <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("site.css") ?>" />
    <!--[if IE]><link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("ie.css") ?>"  /><![endif]-->
    <script type="text/javascript" src="<?php $zm_theme->themeURL("common.js") ?>"></script>
    <?php /* give other themes the chance to add to the default CSS without having to copy everything */ ?>
    <?php if ($zm_theme->themeFileExists("theme.css")) { ?>
      <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL("theme.css") ?>" />
    <?php } ?>
    <?php $pageCSS = "css/".$zm_view->getName().".css"; ?>
    <?php /* page specific CSS */ ?>
    <?php if ($zm_theme->themeFileExists($pageCSS)) { ?>
      <link rel="stylesheet" type="text/css" media="screen,projection" href="<?php $zm_theme->themeURL($pageCSS) ?>" />
    <?php } ?>
    <?php if (!ZMLayout::instance()->isLeftColEnabled() || !ZMLayout::instance()->isRightColEnabled()) { ?>
      <style type="text/css" media="screen,projection">
        <?php if (!ZMLayout::instance()->isLeftColEnabled()) { ?>
          #content {margin-left:10px;}
        <?php } ?>
        <?php if (!ZMLayout::instance()->isRightColEnabled()) { ?>
          body div#content {margin-right:20px;}
        <?php } ?>
      </style>
    <?php } ?>
  </head>

  <body id="b_<?php echo $zm_view->getName() ?>"<?php zm_onload() ?>>
    <?php $bannerBox = ZMBanners::instance()->getBannerForIndex(1); if (null != $bannerBox) { ?>
        <div id="bannerOne"><?php zm_display_banner($bannerBox); ?></div>
    <?php } ?>

    <div id="container">
      <?php include $zm_theme->themeFile("header.php") ?>
      <?php include $zm_theme->themeFile("menu.php") ?>

      <?php if (ZMLayout::instance()->isLeftColEnabled()) { ?>
        <div id="leftcol">
          <?php foreach (ZMLayout::instance()->getLeftColBoxNames() as $box) { ?>
              <?php include $zm_theme->themeFile("boxes/" .$box) ?>
          <?php } ?>
        </div>
      <?php } ?>

      <?php if (ZMLayout::instance()->isRightColEnabled()) { ?>
        <div id="rightcol">
          <?php foreach (ZMLayout::instance()->getRightColBoxNames() as $box) { ?>
              <?php include $zm_theme->themeFile("boxes/" .$box) ?>
          <?php } ?>
        </div>
      <?php } ?>

      <div id="content">
        <?php if ('index' != $zm_view->getName() && zm_setting('isShowCrumbtrail')) { /* this is the actual view, not neccessarily what is in the URL */ ?>
            <?php echo zm_build_crumbtrail(ZMCrumbtrail::instance(), " &gt; "); ?>
        <?php } ?>

        <?php $bannerBox = ZMBanners::instance()->getBannerForIndex(3); if (null != $bannerBox) { ?>
            <div id="bannerThree"><?php zm_display_banner($bannerBox); ?></div>
        <?php } ?>

        <?php if (ZMMessages::instance()->hasMessages()) { ?>
            <ul id="messages">
            <?php foreach (ZMMessages::instance()->getMessages() as $message) { ?>
                <li class="<?php echo $message->getType() ?>"><?php echo $message->getText() ?></li>
            <?php } ?>
            </ul>
        <?php } ?>

        <?php if ($zm_view->isViewFunction()) { $zm_view->callView(); } else { include($zm_view->getViewFilename()); } ?>

        <?php $bannerBox = ZMBanners::instance()->getBannerForIndex(4); if (null != $bannerBox) { ?>
            <div id="bannerFour"><?php zm_display_banner($bannerBox); ?></div>
        <?php } ?>
      </div>

      <?php include $zm_theme->themeFile("footer.php") ?>
    </div>

    <?php $bannerBox = ZMBanners::instance()->getBannerForIndex(6); if (null != $bannerBox) { ?>
        <div id="bannerSix"><?php zm_display_banner($bannerBox); ?></div>
    <?php } ?>

  </body>
</html>
