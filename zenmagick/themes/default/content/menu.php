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

<div id="menu">
  <ul>
    <li class="first"><a href="<?php zm_href(FILENAME_DEFAULT); ?>"><?php zm_l10n("HOME") ?></a></li>
    <?php if ($zm_request->isAnonymous()) { ?>
        <li><a href="<?php zm_secure_href(FILENAME_LOGIN); ?>"><?php zm_l10n("LOGIN") ?></a></li>
    <?php } ?>
    <?php if ($zm_request->isRegistered()) { ?>
        <li><a href="<?php zm_secure_href(FILENAME_ACCOUNT); ?>"><?php zm_l10n("ACCOUNT") ?></a></li>
    <?php } ?>
    <?php if (!$zm_request->isAnonymous()) { ?>
        <li><a href="<?php zm_secure_href(FILENAME_LOGOFF); ?>"><?php zm_l10n("LOGOFF") ?></a></li>
    <?php } ?>
    <?php if (!$zm_request->getShoppingCart()->isEmpty() && !zm_is_checkout_page()) { ?>
        <li><a href="<?php zm_secure_href(FILENAME_SHOPPING_CART); ?>"><?php zm_l10n("SHOPPING CART") ?></a></li>
        <li><a href="<?php zm_secure_href(FILENAME_CHECKOUT_SHIPPING); ?>"><?php zm_l10n("CHECKOUT") ?></a></li>
    <?php } ?>
    <?php if (zm_setting('isShowEZHeaderNav')) { ?>
        <?php foreach (ZMEZPages::instance()->getPagesForHeader() as $page) { ?>
            <li><?php zm_ezpage_link($page->getId()) ?></li>
        <?php } ?>
    <?php } ?>
  </ul>
</div>
