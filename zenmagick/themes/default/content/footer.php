<?php
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
?>

<div id="footer">
  <?php if (zm_setting('isShowEZFooterNav')) { ?>
      <p id="fpages">
          <?php $pages = $zm_pages->getPagesForFooter(); ?>
          <?php foreach ($pages as $page) { ?>
              <?php zm_ezpage_link($page->getId()) ?>
          <?php } ?>
      </p>
  <?php } ?>
  <?php if (zm_setting('isDisplayTimerStats')) { ?>
      <p>
        <?php $db = $zm_runtime->getDB(); ?>
        Queries: <?php echo $db->queryCount(); ?>; Query Time: <?php echo round($db->queryTime(), 4); ?>;
        Page Execution Time: <?php echo zm_get_elapsed_time(); ?>;
      </p>
  <?php } ?>

  <?php $bannerBox = $zm_banners->getBannerForIndex(5); if (null != $bannerBox) { ?>
      <div id="bannerFive"><?php zm_display_banner($bannerBox); ?></div>
  <?php } ?>

  <p>Powered by <a href="http://www.zen-cart.com">zen-cart</a> and <a href="http://www.zenmagick.org">ZenMagick</a></p>
  <p>&copy; 2006,2007  <a href="http://www.zenmagick.org">ZenMagick</a> | Design based on andreas08 by <a href="http://andreasviklund.com">Andreas Viklund</a></p>
</div>
