<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

<?php $form->addProduct($zm_product->getId(), 1) ?>
  <div>  
    <div id="pinfo">
      <?php $html->productImageLink($zm_product) ?>
      <?php $html->encode($zm_product->getDescription()) ?>
    </div>
    <strong><?php $utils->formatMoney($zm_product->getPrice()); ?></strong>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Add to cart") ?>" /></div>
    <p id="author"><?php zm_l10n("Review by: %s", ZMRequest::getAccount()->getFullName()) ?></p>
  </div>
</form>

<?php $form->open(FILENAME_PRODUCT_REVIEWS_WRITE, 'action=process&products_id=' . $zm_product->getId(), true, array('id'=>'review')) ?>
    <fieldset>
        <legend><?php zm_l10n("New Review") ?></legend>
        <p><?php zm_l10n("Choose a ranking for this item. 1 star is the worst and 5 stars is the best.") ?></p>

        <div id="stars">
            <input type="radio" id="rating-1" name="rating" value="1" />
            <label for="rating-1"><img src="<?php $zm_theme->themeURL("images/stars_1_small.gif") ?>" alt="<?php zm_l10n("One Star") ?>" title=" <?php zm_l10n("One Star") ?> " /></label>
            <input type="radio" id="rating-2" name="rating" value="2" />
            <label for="rating-2"><img src="<?php $zm_theme->themeURL("images/stars_2_small.gif") ?>" alt="<?php zm_l10n("Two Stars") ?>" title=" <?php zm_l10n("Two Stars") ?> " /></label>
            <input type="radio" id="rating-3" name="rating" value="3" />
            <label for="rating-3"><img src="<?php $zm_theme->themeURL("images/stars_3_small.gif") ?>" alt="<?php zm_l10n("Three Stars") ?>" title=" <?php zm_l10n("Three Stars") ?> " /></label>
            <input type="radio" id="rating-4" name="rating" value="4" />
            <label for="rating-4"><img src="<?php $zm_theme->themeURL("images/stars_4_small.gif") ?>" alt="<?php zm_l10n("Four Stars") ?>" title=" <?php zm_l10n("Four Stars") ?> " /></label>
            <input type="radio" id="rating-5" name="rating" value="5" />
            <label for="rating-5"><img src="<?php $zm_theme->themeURL("images/stars_5_small.gif") ?>" alt="<?php zm_l10n("Five Stars") ?>" title=" <?php zm_l10n("Five Stars") ?> " /></label>

        </div>

        <label for="review_text"><?php zm_l10n("Please tell us what you think and share your opinions with others. Be sure to focus your comments on the product.") ?></label>
        <textarea id="review_text" name="review_text" cols="60" rows="5"><?php $html->encode(ZMRequest::getParameter("review_text")) ?></textarea>
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

