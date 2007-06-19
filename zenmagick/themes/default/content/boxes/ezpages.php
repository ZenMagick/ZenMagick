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

<?php if (zm_setting('isShowEZBoxesNav')) { ?>
    <?php $sbchapter = isset($sbchapter) ? $sbchapter : $zm_request->getParameter("sbchapter", null); ?>
    <?php $pages = null != $sbchapter ? $zm_pages->getPagesForChapterId($sbchapter) : $zm_pages->getPagesForSidebar(); ?>
    <?php if (0 < count($pages)) { ?>
        <h3><?php zm_l10n("Important Links") ?></h3>
        <div id="sb_ezpages" class="box">
            <?php foreach ($pages as $page) { ?>
                <?php zm_ezpage_link($page->getId())?>
            <?php } ?>
        </div>
    <?php } ?>
<?php } ?>
