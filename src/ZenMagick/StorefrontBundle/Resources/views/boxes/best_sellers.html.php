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

<?php $products = $view->container->get('productService')->getBestSellers($view['request']->getParameter('categoryId'), null, $view['request']->getLocaleId()); ?>
<?php if (0 < count($products)) { ?>
    <h3><?php _vzm("Best Sellers") ?></h3>
    <div id="sb_bestsellers" class="box">
        <ol>
        <?php foreach ($products as $product) { ?>
            <li><a href="<?php echo $net->product($product->getId()) ?>"><?php echo $view->escape($html->more($product->getName(), 28)) ?></a></li>
        <?php } ?>
        </ol>
    </div>
<?php } ?>
