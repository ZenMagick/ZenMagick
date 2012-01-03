<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
 */
?>
<?php
    $bmurl = urlencode($net->url(null, null));
    $bmtitle = sprintf(_zm("%s at %s"), $metaTags->getTitle(), $settingsService->get('storeName'));
?>
<h3><a href="http://ekstreme.com/socializer/?url=<?php echo $bmurl ?>&amp;title=<?php echo $bmtitle ?>"<?php echo $html->hrefTarget()?>><?php _vzm("[More]") ?></a><?php _vzm("Bookmark this") ?></h3>
<div id="sb_bookmarks" class="box">
<a href="http://del.icio.us/post?v=2&amp;url=<?php echo $bmurl ?>&amp;title=<?php echo $bmtitle ?>"<?php echo $html->hrefTarget()?>><img src="<?php echo $this->asUrl("images/bookmarks/deliciou.png") ?>" alt="link to del.icio.us" />del.icio.us</a>

    <a href="http://www.furl.net/storeIt.jsp?t=<?php echo $bmtitle ?>&amp;u=<?php echo $bmurl ?>"<?php echo $html->hrefTarget()?>><img src="<?php echo $this->asUrl("images/bookmarks/furl.png") ?>" alt="link to Furl" />Furl</a>

    <a href="http://www.wists.com/r.php?r=<?php echo $bmurl ?>&amp;title=<?php echo $bmtitle ?>"<?php echo $html->hrefTarget()?>><img src="<?php echo $this->asUrl("images/bookmarks/wists.png") ?>" alt="link to Wists" />wists</a>

    <a href="http://myweb2.search.yahoo.com/myresults/bookmarklet?u=<?php echo $bmurl ?>&amp;t=<?php echo $bmtitle ?>"<?php echo $html->hrefTarget()?>><img src="<?php echo $this->asUrl("images/bookmarks/yahoo.png") ?>" alt="link to Yahoo" />Yahoo My Web</a>

    <a href="http://www.addthis.com/bookmark.php?url=<?php echo $bmurl ?>&amp;title=<?php echo $bmtitle ?>" title="<?php _vzm('Bookmark using any bookmarking manager!') ?>"<?php echo $html->hrefTarget()?>><img src="<?php echo $this->asUrl("images/bookmarks/button1-bm.gif") ?>" alt="<?php _vzm('Bookmark using any bookmarking manager!') ?>" width="125" height="16" /></a>

</div>
