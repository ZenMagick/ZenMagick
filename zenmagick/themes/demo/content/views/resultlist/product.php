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

<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td class="cpt"><input type="checkbox" name="compareId[]" value="<?php echo $product->getId() ?>" /></td>
    <!-- need id on link to identify the product -->
    <td><a href="<?php zm_product_href($product->getId()) ?>" id="product_<?php echo $product->getId() ?>" class="product"><?php zm_image($product->getImageInfo(), PRODUCT_IMAGE_SMALL) ?></a></td>
    <td class="pinfo">
        <a href="<?php zm_product_href($product->getId()) ?>"><?php echo $product->getName() ?></a><br/>
        <?php zm_more(zm_strip_html($product->getDescription(), false), 120) ?>
    </td>
    <td class="pprice"><?php zm_format_currency($product->getPrice()) ?></td>
</tr>
