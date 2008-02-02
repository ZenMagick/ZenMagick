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

<?php if (null != $zm_request->getProductId()) { ?>
    <?php $product = $zm_products->getProductForId($zm_request->getProductId()); ?>
    <?php $manufacturer = $product->getManufacturer(); ?>
    <?php if (null != $manufacturer) { ?>
        <h3><?php zm_l10n("Manufacturer Info") ?></h3>
        <div id="sb_manufacturer_info" class="box">
            <?php 
            if ($manufacturer->hasImage()) {
                $url = zm_href(ZM_FILENAME_CATEGORY, 'manufacturers_id='.$manufacturer->getId(), false);
                $target = '';
                if (!zm_is_empty($manufacturer->getURL())) {
                    $url = zm_redirect_href('manufacturer', $manufacturer->getId(), false);
                    $target = zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"';
                }
                ?><a href="<?php echo $url ?>"<?php echo $target ?>><?php zm_image($manufacturer->getImageInfo()) ?></a><?php
                if (!zm_is_empty($manufacturer->getURL())) {
                    $url = zm_href(ZM_FILENAME_CATEGORY, 'manufacturers_id='.$manufacturer->getId(), false);
                    ?><a href="<?php echo $url ?>"<?php echo $target ?>><?php zm_l10n("Other Products") ?></a><?php
                }
            } else {
                $url = zm_href(ZM_FILENAME_CATEGORY, 'manufacturers_id='.$manufacturer->getId(), false);
                $target = '';
                $text = zm_l10n_get("Other Products");
                if (!zm_is_empty($manufacturer->getURL())) {
                    $url = zm_redirect_href('manufacturer', $manufacturer->getId(), false);
                    $target = zm_setting('isJSTarget') ? ' onclick="newWin(this); return false;"' : ' target="_blank"';
                    $text = zm_l10n_get("Manufacturer Homepage");
                }
                ?><a href="<?php echo $url ?>"<?php echo $target ?>><?php echo $text ?></a><?php
                if (!zm_is_empty($manufacturer->getURL())) {
                    $url = zm_href(ZM_FILENAME_CATEGORY, 'manufacturers_id='.$manufacturer->getId(), false);
                    ?><a href="<?php echo $url ?>"<?php echo $target ?>><?php zm_l10n("Other Products") ?></a><?php
                }
            } ?>
        </div>
    <?php } ?>
<?php } ?>

