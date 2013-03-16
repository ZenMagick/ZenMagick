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
?>

<?php if (!isset($currentProduct) && 'specials' != $request->getRequestId()) { ?>
    <?php $products = $container->get('productService')->getSpecials(1, $session->getLanguageId()); ?>
    <?php if (0 != count($products)) { $product = $products[0]; ?>
        <h3><a href="<?php echo $net->generate('specials') ?>"><?php _vzm("[More]") ?></a><?php _vzm("Specials") ?></h3>
        <div id="sb_specials" class="box">
            <p><?php echo $html->productImageLink($product) ?></p>
            <p><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $view->escape($product->getName()) ?></a></p>
            <?php $offers = $product->getOffers(); ?>
            <p class="price"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></p>
        </div>
    <?php } ?>
<?php } ?>
