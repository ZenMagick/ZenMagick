<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2012 zenmagick.org
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

<tr class="<?php echo ($odd?"odd":"even").($first?" first":" other") ?>">
    <td><?php echo $html->productImageLink($product, $request->getCategoryId()) ?></td>
    <td class="pinfo">
    	<div style="margin-left: 10px">
	    	<div class="productsName"><a href="<?php echo $net->product($product->getId(), $request->getCategoryId()) ?>"><?php echo $html->encode($product->getName()) ?></a></div>
	        <div class="productsPrice"><?php echo $utils->formatMoney($product->getPrice()) ?></div>
	        <div class="shortProductsDescription"><?php echo $html->more($product->getDescription(), 120) ?></div>
        </div>
    </td>
</tr>
