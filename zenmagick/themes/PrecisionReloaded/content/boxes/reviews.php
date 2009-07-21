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

<?php 

    // get review for product (if on product page)
    $reviews = ZMReviews::instance()->getRandomReviews($request->getProductId(), 1, $session->getLanguageId()); 

    if (0 == count($reviews) && 0 == $request->getProductId()) {
        // default to any random on non product pages
        $reviews = ZMReviews::instance()->getRandomReviews(null, 1, $session->getLanguageId()); 
    }
?>
<?php if (1 == count($reviews)) {
    $review = $reviews[0];
    $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId();
    $href = $net->url(FILENAME_PRODUCT_REVIEWS_INFO, $params, false, false);
    $rtext = zm_l10n_get("%s of 5 stars!", $review->getRating());
    $rimg = '<img src="'.$zm_theme->themeURL('images/stars_'.$review->getRating().'.gif', false).'" alt="'.$rtext.'" title="'.$rtext.'"/>';
    ?>
    <h2><a href="<?php $net->url(FILENAME_REVIEWS) ?>"><?php zm_l10n("[More]") ?></a><?php zm_l10n("Reviews") ?></h2>
    <div id="sb_reviews" class="box">
        <p><a href="<?php $net->product($review->getProductId()) ?>"><?php $html->image($review->getProductImageInfo()) ?></a></p>
        <p id="rtext"><a href="<?php echo $href ?>"><?php $html->more($review->getText(), 60) ?></a></p>
        <p><a href="<?php echo $href ?>"><?php echo $rimg ?></a></p>
    </div>
<?php } ?>
