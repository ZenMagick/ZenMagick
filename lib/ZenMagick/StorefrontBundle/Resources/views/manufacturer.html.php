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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>
<?php $crumbtrail->addManufacturer()->addProduct() ?>
<?php if ($resultList->hasResults()) { ?>
    <div class="rnblk">
        <?php echo $this->fetch('resultlist/nav.html.php') ?>
        <?php echo $this->fetch('resultlist/options.html.php') ?>
    </div>

    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($resultList->getResults() as $product) { ?>
              <?php echo $this->fetch('resultlist/product.html.php', array('product' => $product, 'first' => $first, 'odd' => $odd)) ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
    <div class="rnblk">
        <?php echo $this->fetch('resultlist/nav.html.php') ?>
    </div>
<?php } else { ?>
    <h2><?php _vzm("There are no products for this manufacturer") ?></h2>
<?php } ?>
