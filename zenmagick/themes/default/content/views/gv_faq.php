<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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

<h2><?php zm_l10n("Gift Certificate FAQ") ?></h2>

<?php /* the available FAQ entries */
    $faq_topics = array(
      'gv_purchase' => zm_l10n_get('Purchasing Gift Certificates'),
      'gv_send' => zm_l10n_get('How to send Gift Certificates'),
      'gv_use' => zm_l10n_get('Buying with Gift Certificates'),
      'gv_redeem' => zm_l10n_get('Redeeming Gift Certificates'),
      'gv_trouble' => zm_l10n_get('When problems occur...')
    ); 
?>

<ul>
<?php foreach ($faq_topics as $key => $title) { ?>
    <li><a href="<?php $net->url(FILENAME_GV_FAQ,'topic='.$key) ?>"><?php zm_l10n($title) ?></a></li>
<?php } ?>
</ul>

<?php if (null != ($topic = ZMRequest::getParameter('topic')) && array_key_exists($topic, $faq_topics)) { ?>
    <?php echo zm_l10n_chunk_get($topic, ZMSettings::get('storeEmail')); ?>
<?php } ?>

<?php $form->open(FILENAME_GV_REDEEM, '', true, array('id'=>'gv_redeem')) ?>
  <fieldset>
    <legend><?php zm_l10n("Redemption code details") ?></legend>
    <div>
      <label for="gvCode"><?php zm_l10n("Redemption Code") ?></label>
      <input type="text" id="gvCode" name="couponCode" value="<?php $html->encode($zm_gvredeem->getCode()) ?>" /> 
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Redeem") ?>" /></div>
</form>
