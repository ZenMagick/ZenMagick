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
<?php
    $toolbox = ZMRequest::instance()->getToolbox();
?>
  <?php $toolbox->form->open('', $zm_nav_params, false, array('method'=>'get')) ?>
    <h2>Group Pricing ( <?php $toolbox->form->idpSelect('groupId', $priceGroups, ZMRequest::instance()->getParameter('groupId'), array('size'=>1, 'onchange'=>'this.form.submit()')) ?> )</h2>
  </form>

  <?php $toolbox->form->open('', $zm_nav_params) ?>
    <fieldset>
      <legend>Discount</legend>
      <div>
        <?php $groupId = ZMRequest::instance()->getParameter('groupId', $priceGroups[0]->getId()); ?>
        <input type="hidden" name="groupId" value="<?php echo $groupId ?>">
        <input type="hidden" name="groupPricingId" value="<?php echo ZMRequest::instance()->getParameter('groupPricingId') ?>">
      </div>
      <p>
        <label for="discount">Discount</label> 
        <input type="text" id="discount" name="discount" value="<?php echo ZMRequest::instance()->getParameter('discount') ?>">

        <?php $type = ZMRequest::instance()->getParameter('type'); ?>
        <label for="type">Type</label> 
        <select id="type" name="type">
          <option value="%"<?php if ('%' == $type) { echo ' selected'; } ?>>Percent</option>
          <option value="$"<?php if ('$' == $type) { echo ' selected'; } ?>>Amount</option>
        </select>
      </p>
      <p>
        <input type="checkbox" id="regularPriceOnly" name="regularPriceOnly" value="1"<?php $toolbox->form->checked(ZMRequest::instance()->getParameter('regularPriceOnly')) ?>>
        <label for="regularPriceOnly">Do not allow discount on sale/special</label>
      </p>
      <p>
        <label for="startDate">Start Date</label> 
        <input type="text" id="startDate" name="startDate" value="<?php $toolbox->locale->shortDate(ZMRequest::instance()->getParameter('startDate')) ?>">
        <label for="endDate">End Date</label> 
        <input type="text" id="endDate" name="endDate" value="<?php $toolbox->locale->shortDate(ZMRequest::instance()->getParameter('endDate')) ?>">
        <?php echo UI_DATE_FORMAT ?>, for example: <?php echo UI_DATE_FORMAT_SAMPLE ?>
      </p>
    </fieldset>
    <p>
      <input type="hidden" name="fkt" value="zm_group_pricing_admin">
      <?php if (0 < ZMRequest::instance()->getParameter('groupPricingId')) { ?>
          <input type="submit" name="update" value="Update">
          <a href="<?php $toolbox->net->url('', $zm_nav_params.'&groupPricingId='.ZMRequest::instance()->getParameter('groupPricingId').'&delete=true') ?>">Delete</a>
      <?php } else { ?>
          <input type="submit" name="create" value="Create">
      <?php } ?>
    </p>
  </form>
