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
<?php
$crumbtrail->addCategoryPath()->addManufacturer()->addProduct();

if ($resultList->hasResults()) { ?>
    <div class="rnblk">
        <?php echo $view->render('StorefrontBundle::resultlist/nav.html.php', array('resultList' => $resultList)) ?>
        <?php echo $view->render('StorefrontBundle::resultlist/options.html.php', array('resultList' => $resultList)) ?>
    </div>

    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($resultList->getResults() as $product) { ?>
              <?php echo $this->render('StorefrontBundle::resultlist/product.html.php', array('product' => $product, 'first' => $first, 'odd' => $odd)) ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
    <div class="rnblk">
        <?php echo $view->render('StorefrontBundle::resultlist/nav.html.php', array('resultList' => $resultList)) ?>
    </div>
<?php } else { ?>
    <?php if ($currentCategory->hasChildren()) { ?>
        <div class="subcats">
            <h3><?php _vzm("Available Sub-categories") ?></h3>
            <?php foreach ($currentCategory->getChildren() as $category) {
                $encName = $view->escape($category->getName());
                $catImage = $category->getImageInfo();
                $linkText = null == $catImage ? $encName : '<img src="'.$catImage->getDefaultImage().'" alt="'.$encName.'" title="'.$encName.'">';
                ?>
                <a href="<?php echo $view['router']->generate('category', array('cPath' => implode('_', $category->getPath()))) ?>"><?php echo $linkText ?></a>
            <?php } ?>
        </div>
    <?php } ?>

    <h2><?php _vzm("There are no products in this category") ?></h2>
<?php } ?>
