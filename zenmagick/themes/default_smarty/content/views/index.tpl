{* 
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
*}

{$zm_theme->staticPageContent("main_page")}
{assign var=running_total value=`$running_total+$some_array[row].some_value`}
{assign var=featured value=$zm_products->getFeaturedProducts(0, 4)}
<h3>Featured Products</h3>
<div id="featured">
  {foreach from=$featured item=product}
    <div>
      <p>{$zm->product_image_link($product)}</p>
      <p><a href="{$zm->product_href($product->getId())}">{$product->getName()}</a></p>
      {assign var=offers value=$product->getOffers()}
      <p>{$zm->format_currency($offers->getCalculatedPrice())}</p>
    </div>
  {/foreach}
</div>
