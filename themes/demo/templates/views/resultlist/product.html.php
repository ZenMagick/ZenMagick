<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
use ZenMagick\StoreBundle\Services\Products;
?>

<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <!-- need id on link to identify the product -->
    <td><a href="<?php echo $net->product($product->getId(), $view['request']->getParameter('categoryId')) ?>" id="product_<?php echo $product->getId() ?>" class="product"><?php echo $html->image($product->getImageInfo(), Products::IMAGE_SMALL) ?></a></td>
    <td class="pinfo">
        <a href="<?php echo $net->product($product->getId()) ?>"><?php echo $view->escape($product->getName()) ?></a><br/>
        <?php echo $html->more($product->getDescription(), 120) ?>
    </td>
    <td class="pprice"><?php echo $utils->formatMoney($product->getPrice()) ?></td>
</tr>
