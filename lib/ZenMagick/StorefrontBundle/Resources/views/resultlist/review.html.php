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

<?php $product = $container->get('productService')->getProductForId($review->getProductId(), $session->getLanguageId()); ?>
<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td>
        <?php echo $html->productImageLink($product) ?>
        <h3><?php echo $view->escape($product->getName()) ?></h3>
        <?php $rtext = sprintf(_zm("%s of 5 stars!"), $review->getRating()); ?>
        <p><img src="<?php echo $this->asUrl('images/stars_'.$review->getRating().'.gif') ?>" alt="<?php echo $rtext ?>" /></p>
        <h4 class="rtitle"><?php echo $html->strip($review->getTitle()) ?></h4>
        <p class="rtext"><?php echo $html->more($html->strip($review->getText()), 120) ?></p>
        <p class="rinfo"><?php _vzm("Date added: %s by %s", $locale->shortDate($review->getDateAdded()), $review->getAuthor()) ?></p>
    </td>
    <td class="lnks">
        <p><a class="btn" href="<?php echo $net->product($product->getId()) ?>"><?php _vzm("Product Information") ?></a></p>
        <?php $params = array('productId' => $review->getProductId(), 'reviews_id' => $review->getId()); ?>
        <p><a class="btn" href="<?php echo $view['router']->generate('product_reviews_info', $params) ?>"><?php _vzm("Read full review") ?></a></p>
    </td>
</tr>
