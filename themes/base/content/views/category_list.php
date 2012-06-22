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

$crumbtrail->addCategoryPath($request->getCategoryPathArray())->addManufacturer($request->query->getInt('manufacturers_id'))->addProduct($request->getProductId());

if ($resultList->hasResults()) { ?>
    <div class="rnblk">
        <?php echo $this->fetch('views/resultlist/nav.php') ?>
        <?php echo $this->fetch('views/resultlist/options.php') ?>
    </div>

    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($resultList->getResults() as $product) { ?>
              <?php echo $this->fetch('views/resultlist/product.php', array('product' => $product, 'first' => $first, 'odd' => $odd)) ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
    <div class="rnblk">
        <?php echo $this->fetch('views/resultlist/nav.php') ?>
    </div>
<?php } else { ?>
    <?php if ($currentCategory->hasChildren()) { ?>
        <div class="subcats">
            <h3><?php _vzm("Available Sub-categories") ?></h3>
            <?php foreach ($currentCategory->getChildren() as $category) {
                $encName = $html->encode($category->getName());
                $catImage = $category->getImageInfo();
                $linkText = null == $catImage ? $encName : '<img src="'.$catImage->getDefaultImage().'" alt="'.$encName.'" title="'.$encName.'">';
                ?>
                <a href="<?php echo $net->url('category', 'cPath='.implode('_', $category->getPath())) ?>"><?php echo $linkText ?></a>
            <?php } ?>
        </div>
    <?php } ?>

    <h2><?php _vzm("There are no products in this category") ?></h2>
<?php } ?>
