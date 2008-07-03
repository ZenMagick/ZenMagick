{* 
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
*}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <title>{$zm_meta->getTitle()}</title>
    <base href="{$zm->ZMRequest->getPageBase()}">
    <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
    <meta name="generator" content="ZenMagick {$zm_setting.ZenMagickVersion}">
    <meta name="keywords" content="{$zm_meta->getKeywords(false)}">
    <meta name="description" content="{$zm_meta->getDescription(false)}">
    <!--[if IE]><link rel="stylesheet" type="text/css" media="screen,projection" href="{$zm_theme->themeURL("ie.css", false)}"  /><![endif]-->
    <script type="text/javascript" src="{$zm_theme->themeURL("common.js", false)}"></script>
    <script type="text/javascript" src="{$zm_theme->themeURL("category.js", false)}"></script>
    <link rel="stylesheet" type="text/css" media="screen,projection" href="{$zm_theme->themeURL("site.css", false)}">
  </head>

  <body id="b_{$zm_view->getName()}"{$zm->onload()}>
    {assign var=bannerBox value=$zm_banners->getBannerForIndex(1)}
    {if $bannerBox}
      <div id="bannerOne">{$zm->display_banner($bannerBox)}</div>
    {/if}

    <div id="container">
      {include file="header.tpl"}
      {include file="menu.tpl"}

      {if $zm_layout->isLeftColEnabled()}
        <div id="leftcol">
          {assign var=boxes value=$zm_layout->getLeftColBoxNames()}
          {foreach from=$boxes item=box}
            {include file="boxes/$box"}
          {/foreach}
        </div>
      {/if}

      {if $zm_layout->isRightColEnabled()}
        <div id="rightcol">
          {assign var=boxes value=$zm_layout->getRightColBoxNames()}
          {foreach from=$boxes item=box}
            {include file="boxes/$box"}
          {/foreach}
        </div>
      {/if}

      <div id="content">
        {assign var=bannerBox value=$zm_banners->getBannerForIndex(3)}
        {if $bannerBox}
          <div id="bannerThree">{$zm->display_banner($bannerBox)}</div>
        {/if}

        {include file="$view_name"}

        {assign var=bannerBox value=$zm_banners->getBannerForIndex(4)}
        {if $bannerBox}
          <div id="bannerFour">{$zm->display_banner($bannerBox)}</div>
        {/if}
      </div>

      {include file="footer.tpl"}
    </div>

    {assign var=bannerBox value=$zm_banners->getBannerForIndex(6)}
    {if $bannerBox}
      <div id="bannerSix">{$zm->display_banner($bannerBox)}</div>
    {/if}

  </body>
</html>
