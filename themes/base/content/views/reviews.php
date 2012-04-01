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
<?php $crumbtrail->addCategoryPath($request->getCategoryPathArray())->addManufacturer($request->getManufacturerId())->addProduct($request->getProductId())->addCrumb(_zm('Reviews')) ?>
<?php if ($resultList->hasResults()) { ?>
    <div class="rnblk">
        <?php echo $this->fetch('views/resultlist/nav.php') ?>
    </div>

    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($resultList->getResults() as $review) { ?>
              <?php echo $this->fetch('views/resultlist/review.php', array('review' => $review, 'first' => $first, 'odd' => $odd)) ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
    <div class="rnblk">
        <?php echo $this->fetch('views/resultlist/nav.php') ?>
    </div>
<?php } else { ?>
    <h2><?php _vzm("There are no reviews available at this time") ?></h2>
    <p><?php _vzm("New reviews might need approval before they are listed.") ?></p>
<?php } ?>
