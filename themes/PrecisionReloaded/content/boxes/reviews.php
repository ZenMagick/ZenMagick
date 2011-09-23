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

<?php

    // get review for product (if on product page)
    $reviews = $container->get('reviewService')->getRandomReviews($session->getLanguageId(), $request->getProductId(), 1);

    if (0 == count($reviews) && 0 == $request->getProductId()) {
        // default to any random on non product pages
        $reviews = $container->get('reviewService')->getRandomReviews($session->getLanguageId(), null, 1);
    }
?>
<?php if (1 == count($reviews)) {
    $review = $reviews[0];
    $params = 'products_id='.$review->getProductId().'&reviews_id='.$review->getId();
    $href = $net->url('product_reviews_info', $params);
    $rtext = sprintf(_zm("%s of 5 stars!"), $review->getRating());
    $rimg = '<img src="'.$this->asUrl('images/stars_'.$review->getRating().'.gif', false).'" alt="'.$rtext.'" title="'.$rtext.'"/>';
    ?>
    <h2><a href="<?php echo $net->url('reviews') ?>"><?php _vzm("[More]") ?></a><?php _vzm("Reviews") ?></h2>
    <div id="sb_reviews" class="box">
        <p><a href="<?php echo $net->product($review->getProductId()) ?>"><?php echo $html->image($review->getProductImageInfo()) ?></a></p>
        <p id="rtext"><a href="<?php echo $href ?>"><?php echo $html->more($review->getText(), 60) ?></a></p>
        <p><a href="<?php echo $href ?>"><?php echo $rimg ?></a></p>
    </div>
<?php } ?>
