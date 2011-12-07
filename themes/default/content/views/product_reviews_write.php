<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

<?php echo $form->addProduct($currentProduct->getId(), 1) ?>
  <div>
    <div id="pinfo">
      <?php echo $html->productImageLink($currentProduct) ?>
      <?php echo $currentProduct->getDescription() ?>
    </div>
    <strong><?php echo $utils->formatMoney($currentProduct->getPrice()); ?></strong>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Add to cart") ?>" /></div>
    <p id="author"><?php _vzm("Review by: %s", $request->getAccount()->getFullName()) ?></p>
  </div>
</form>

<?php echo $form->open('product_reviews_write', 'productId=' . $currentProduct->getId().'&languageId='.$currentProduct->getLanguageId(), true, array('id'=>'newReview')) ?>
    <fieldset>
        <legend><?php _vzm("New Review") ?></legend>
        <p><?php _vzm("Choose a ranking for this item. 1 star is the worst and 5 stars is the best.") ?></p>

        <div id="stars">
            <?php for ($ii=1; $ii<6; ++$ii) { $checked = ($newReview->getRating() == $ii ? ' checked="checked"' : ''); ?>
              <input type="radio" id="rating-<?php echo $ii ?>" name="rating" value="<?php echo $ii ?>"<?php echo $checked ?> />
              <label for="rating-<?php echo $ii ?>"><img src="<?php echo $this->asUrl("images/stars_".$ii."_small.gif") ?>" alt="<?php _vzm("%s Star", $ii) ?>" title=" <?php _vzm("%s Star", $ii) ?> " /></label>
            <?php } ?>
        </div>

        <div id="rtitle">
            <label for="title"><?php _vzm('Title') ?></label> <input type="text" id="title" name="title" value=""/>
        </div>

        <label for="text"><?php _vzm("Please tell us what you think and share your opinions with others. Be sure to focus your comments on the product.") ?></label>
        <textarea id="text" name="text" cols="60" rows="5"><?php echo $html->encode($newReview->getText()) ?></textarea>
        <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" /></div>
        <div>
            <strong><?php _vzm("NOTE:") ?> </strong><?php _vzm("HTML tags are not allowed.") ?><br />
            <?php if ($settings->get('isApproveReviews')) { ?>
               <strong><?php _vzm("NOTE:") ?> </strong><?php _vzm("Reviews require prior approval before they will be displayed.") ?>
            <?php } ?>
        </div>
    </fieldset>
</form>
<br class="clearBoth" />

