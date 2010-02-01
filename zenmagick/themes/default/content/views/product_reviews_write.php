<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
      <?php $html->productImageLink($currentProduct) ?>
      <?php echo $html->encode($currentProduct->getDescription()) ?>
    </div>
    <strong><?php echo $utils->formatMoney($currentProduct->getPrice()); ?></strong>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Add to cart") ?>" /></div>
    <p id="author"><?php zm_l10n("Review by: %s", $request->getAccount()->getFullName()) ?></p>
  </div>
</form>

<?php echo $form->open(FILENAME_PRODUCT_REVIEWS_WRITE, 'action=process&productId=' . $currentProduct->getId(), true, array('id'=>'newReview')) ?>
    <fieldset>
        <legend><?php zm_l10n("New Review") ?></legend>
        <p><?php zm_l10n("Choose a ranking for this item. 1 star is the worst and 5 stars is the best.") ?></p>

        <div id="stars">
            <?php for ($ii=1; $ii<6; ++$ii) { $checked = ($newReview->getRating() == $ii ? ' checked="checked"' : ''); ?>
              <input type="radio" id="rating-<?php echo $ii ?>" name="rating" value="<?php echo $ii ?>"<?php echo $checked ?> />
              <label for="rating-<?php echo $ii ?>"><img src="<?php echo $this->asUrl("images/stars_".$ii."_small.gif") ?>" alt="<?php zm_l10n("%s Star", $ii) ?>" title=" <?php zm_l10n("%s Star", $ii) ?> " /></label>
            <?php } ?>
        </div>

        <label for="text"><?php zm_l10n("Please tell us what you think and share your opinions with others. Be sure to focus your comments on the product.") ?></label>
        <textarea id="text" name="text" cols="60" rows="5"><?php echo $html->encode($newReview->getText()) ?></textarea>
        <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send") ?>" /></div>
        <div>
            <strong><?php zm_l10n("NOTE:") ?> </strong><?php zm_l10n("HTML tags are not allowed.") ?><br />
            <?php if (ZMSettings::get('isApproveReviews')) { ?>
               <strong><?php zm_l10n("NOTE:") ?> </strong><?php zm_l10n("Reviews require prior approval before they will be displayed.") ?>
            <?php } ?>
        </div>
    </fieldset>
</form>
<br class="clearBoth" />

