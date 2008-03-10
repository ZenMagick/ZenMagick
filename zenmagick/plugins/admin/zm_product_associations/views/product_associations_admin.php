<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

  $zm_product = ZMProducts::instance()->getProductForId(ZMRequest::getProductId());
?>

  <h2>Product Associations for &lsquo;<?php echo $zm_product->getName() ?>&rsquo;</h2>

  <a href="#TB_inline?height=355&amp;width=600&amp;inlineId=meddle&amp;modal=true" class="thickbox">Show hidden modal content.</a>

  <div id="meddle" style="display:none;">
    meddle associations...
    <?php echo zm_catalog_tree(ZMCategories::instance()->getCategoryTree(), '', false, true, 'pick-cat-tree'); ?>

    <a href="#" onclick="tb_remove();return false;" class="btn" style="color: #fff;">&raquo;&nbsp;Continue Shopping</a>
  </div>
