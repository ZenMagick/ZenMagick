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
  <?php zm_form('', $zm_nav_params, '', 'get') ?>
    <h2>Group Pricing ( <?php zm_idp_select('groupId', $priceGroups, 1, $zm_request->getParameter('groupId'), 'this.form.submit()') ?> )</h2>
  </form>

  <?php zm_form('', $zm_nav_params, '', 'post') ?>
    <fieldset>
      <?php $groupId = $zm_request->getParameter('groupId', $priceGroups[0]->getId()); ?>
      <input type="hidden" name="groupId" value="<?php echo $groupId ?>">
      <input type="hidden" name="groupPricingId" value="<?php echo $zm_request->getParameter('groupPricingId') ?>">
      <legend>Discount</legend>
      <p>
        <label for="discount">Discount</label> 
        <input type="text" id="discount" name="discount" value="<?php echo $zm_request->getParameter('discount') ?>">

        <label for="type">Type</label> 
        <select id="type" name="type">
          <option value="%">Percent</option>
          <option value="$">Amount</option>
        </select>
      </p>
      <p>
        <input type="checkbox" id="regularPriceOnly" name="regularPriceOnly" value="1"<?php zm_checkbox_state($zm_request->getParameter('regularPriceOnly')) ?>>
        <label for="regularPriceOnly">Do not allow discount on sale/special</label>
      </p>
      <p>
        <label for="startDate">Start Date</label> 
        <input type="text" id="startDate" name="startDate" value="<?php echo $zm_request->getParameter('startDate') ?>">
        <label for="endDate">End Date</label> 
        <input type="text" id="endDate" name="endDate" value="<?php echo $zm_request->getParameter('endDate') ?>">
      </p>
    </fieldset>
    <p>
      <input type="hidden" name="fkt" value="zm_group_pricing_admin">
      <?php if (0 < $zm_request->getParameter('groupPricingId')) { ?>
          <input type="submit" name="update" value="Update">
          <a href="<?php zm_href('', $zm_nav_params.'&groupPricingId='.$zm_request->getParameter('groupPricingId').'&delete=true') ?>">Delete</a>
      <?php } else { ?>
          <input type="submit" name="create" value="Create">
      <?php } ?>
    </p>
  </form>
