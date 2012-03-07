<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

<?php echo get_class($container->get('productService')) ?>
<?php echo $utils->staticPageContent("main_page") ?>
<?php $featured = $container->get('productService')->getFeaturedProducts(null, 4, false, $session->getLanguageId()); ?>
<h3>Featured Products</h3>
<div id="featured">
  <?php foreach ($featured as $product) { ?>
    <div><table class="tbl" width="100%" height="125">
      <tr><td><?php echo $html->productImageLink($product) ?></td></tr></table><table>
      <tr height="38" wid><td width="100%" valign="top"><font class="product"><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $html->more($html->strip($product->getName()), 40) ?></a></font></td></tr>
      <?php $offers = $product->getOffers(); ?>
      <tr>
        <td width="50%"><font class="price"><?php echo $utils->formatMoney($offers->getCalculatedPrice()) ?></font></td>
        <td width="50%" align="right"><a href="<?php echo $net->product($product->getId()) ?>">Link</a></td>
      </tr>
    </table></div>
  <?php } ?>
</div>
