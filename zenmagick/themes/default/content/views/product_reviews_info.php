<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php echo $form->addProduct($currentProduct->getId(), 1) ?>
  <div>  
    <div id="pinfo">
      <?php $imageInfo = $currentProduct->getImageInfo() ?>
      <a href="<?php echo $net->product($currentProduct->getId()) ?>"><?php echo $html->productImageLink($currentProduct) ?></a>
      <?php echo $currentProduct->getDescription(); ?>
    </div>
    <strong><?php echo $utils->formatMoney($currentProduct->getPrice()); ?></strong>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Add to cart") ?>" /></div>

    <p id="author">
      <?php _vzm("Review by: %s", $currentReview->getAuthor()) ?>
      <?php $rtext = zm_l10n_get("%s of 5 stars!", $currentReview->getRating()) ?>
      <span id="stars">
        <img src="<?php echo $this->asUrl('images/stars_'.$currentReview->getRating().'.gif') ?>" alt="<?php echo $rtext ?>" />
        <?php $rtext ?>
      </span>
    </p>
    <div id="rlongtext">
        <?php echo $html->encode($currentReview->getText()) ?>
    </div>
  </div>
</form>
