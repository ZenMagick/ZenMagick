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

{assign var=manufacturer value=$zm_product->getManufacturer()}
<h2>{if $manufacturer}{$manufacturer->getName()}{/if} {$zm_product->getName()}</h2>
{$zm->add_product_form($zm_product->getId())}
  {assign var=imageInfo value=$zm_product->getImageInfo()}
  <div>
      {if $imageInfo->hasLargeImage()}
          <a href="{$zm->absolute_href($imageInfo->getLargeImage())}" onclick="productPopup(event, this); return false;">{$html->image($imageInfo, 'medium')}</a>
      {else}
          {$zm->image($imageInfo, PRODUCT_IMAGE_MEDIUM)}
      {/if}
      <div id="desc">{$zm_product->getDescription()}</div>
      {if $manufacturer}
        {$zm->l10n("Producer")}: {$zm->htmlencode($manufacturer->getName())}<br>
      {/if}
      <p id="price">{$zm->htmlencode($zm_product->getModel())}: {$zm->fmt_price($zm_product)}</p>
  </div>

  {assign var=attributes value=$zm->build_attribute_elements($zm_product)}
  {foreach from=$attributes item=attribute}
      <fieldset>
          <legend>{$attribute.name}</legend>
          {foreach from=$attribute.html item=option}
            <p>{$option}</p>
          {/foreach}
      </fieldset>
  {/foreach}

  {assign var=features value=$zm_product->getFeatures()}
  {if $features}
      <fieldset>
          <legend>{$zm->l10n("Features")}</legend>
          {foreach from=$features item=feature}
            {$feature->getName()}: {$zm->list_values($feature->getValues())} {$zm->htmlencode($feature->getDescription())}<br>
          {/foreach}
      </fieldset>
  {/if}

{*
  <fieldset>
      <legend>{$zm->l10n("Shopping Options")}</legend>
      <?php $minMsg = ""; if (1 < $zm_product->getMinOrderQty()) { $minMsg = zm_l10n_get(" (Order minimum: %s)", $zm_product->getMinOrderQty()); } ?>
      <label for="cart_quantity"><?php zm_l10n("Quantity") ?><?php echo $minMsg; ?></label>
      <input type="text" id="cart_quantity" name="cart_quantity" value="1" maxlength="6" size="4" />
      <input type="submit" class="btn" value="<?php zm_l10n("Add to cart") ?>" />
  </fieldset>

  <?php $addImgList = $zm_product->getAdditionalImages(); ?>
  <?php if (0 < count($addImgList)) { ?>
      <fieldset>
          <legend><?php zm_l10n("Additional Images") ?></legend>
          <?php foreach ($addImgList as $addImg) { ?>
              <?php if ($addImg->hasLargeImage()) { ?>
                  <a href="<?php zm_absolute_href($addImg->getLargeImage()) ?>" onclick="productPopup(event, this); return false;"><img src="<?php zm_absolute_href($addImg->getDefaultImage()) ?>" alt="" title="" /></a>
              <?php } else { ?>
                  <img src="<?php zm_absolute_href($addImg->getDefaultImage()) ?>" alt="" title="" />
              <?php } ?>
          <?php } ?>
      </fieldset>
  <?php } ?>
  <?php if ($zm_product->hasReviews() || $zm_product->getTypeSetting('reviews') || $zm_product->getTypeSetting('tell_a_friend')) { ?>
      <fieldset>
          <legend><?php zm_l10n("Other Options") ?></legend>
          <?php if ($zm_product->hasReviews()) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_PRODUCT_REVIEWS_INFO, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Read Reviews") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('reviews')) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_PRODUCT_REVIEWS_WRITE, null) ?>"><?php zm_l10n("Write a Review") ?></a>
          <?php } ?>
          <?php if ($zm_product->getTypeSetting('tell_a_friend')) { ?>
              <a class="btn" href="<?php zm_href(FILENAME_TELL_A_FRIEND, "products_id=".$zm_product->getId()) ?>"><?php zm_l10n("Tell a friend about this product") ?></a>
          <?php } ?>
      </fieldset>
  <?php } ?>
</form>
*}
