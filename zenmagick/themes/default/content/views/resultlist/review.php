<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

<?php $product = $zm_products->getProductForId($review->getProductId()); ?>
<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td>
        <a href="<?php zm_product_href($review->getProductId()) ?>"><?php zm_product_image($product) ?></a>
        <h3><?php echo $product->getName() ?></h3>
        <?php $rtext = zm_l10n_get("%s of 5 stars!", $review->getRating()); ?>
        <p><?php zm_image('stars_'.$review->getRating().'.gif', $rtext) ?></p>
        <p class="rtext"><?php zm_more(zm_strip_html($review->getText(), false), 120) ?></p>
        <p class="rinfo"><?php zm_l10n("Date added: %s by %s", $review->getDateAdded(), $review->getAuthor()) ?></p>
    </td>
    <td class="lnks">
        <p><a class="btn" href="<?php zm_product_href($product->getId()) ?>"><?php zm_l10n("Product Information") ?></a></p>
        <?php $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId(); ?>
        <p><a class="btn" href="<?php zm_href(FILENAME_PRODUCT_REVIEWS_INFO, $params) ?>"><?php zm_l10n("Read full review") ?></a></p>
    </td>
</tr>
