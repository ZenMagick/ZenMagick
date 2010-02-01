<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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
  <form action="<?php $toolbox->admin->url() ?>" method="GET">
    <?php $toolbox->form->hidden($defaultUrlParams) ?>
    <input type="hidden" name="main_page" value="catalog_manager">
    <input type="hidden" name="fkt" value="GroupPricingTab">
    <h2>Group Pricing ( <?php echo $toolbox->form->idpSelect('groupId', $priceGroups, $groupId, array('size'=>1, 'onchange'=>'this.form.submit()')) ?> )</h2>
  </form>

  <form action="<?php $toolbox->admin->url(null, $defaultUrlParams) ?>" method="POST">
    <fieldset>
      <legend>Discount</legend>
      <div>
        <input type="hidden" name="groupId" value="<?php echo $groupId ?>">
        <input type="hidden" name="groupPricingId" value="<?php echo $request->getParameter('groupPricingId') ?>">
      </div>
      <p>
        <label for="discount">Discount</label> 
        <input type="text" id="discount" name="discount" value="<?php echo $request->getParameter('discount') ?>">

        <?php $type = $request->getParameter('type'); ?>
        <label for="type">Type</label> 
        <select id="type" name="type">
          <option value="%"<?php if ('%' == $type) { echo ' selected'; } ?>>Percent</option>
          <option value="$"<?php if ('$' == $type) { echo ' selected'; } ?>>Amount</option>
        </select>
      </p>
      <p>
        <input type="checkbox" id="regularPriceOnly" name="regularPriceOnly" value="1"<?php $toolbox->form->checked($request->getParameter('regularPriceOnly')) ?>>
        <label for="regularPriceOnly">Do not allow discount on sale/special</label>
      </p>
      <p>
        <label for="startDate">Start Date</label> 
        <input type="text" id="startDate" name="startDate" value="<?php echo $toolbox->locale->shortDate($request->getParameter('startDate')) ?>">
        <label for="endDate">End Date</label> 
        <input type="text" id="endDate" name="endDate" value="<?php echo $toolbox->locale->shortDate($request->getParameter('endDate')) ?>">
        <?php echo UI_DATE_FORMAT ?>, for example: <?php echo UI_DATE_FORMAT_SAMPLE ?>
      </p>
    </fieldset>
    <p>
      <input type="hidden" name="fkt" value="GroupPricingTab">
      <?php if (0 < $request->getParameter('groupPricingId')) { ?>
          <input type="submit" name="update" value="Update">
          <a href="<?php $toolbox->net->url('', $zm_nav_params.'&groupPricingId='.$request->getParameter('groupPricingId').'&delete=true') ?>">Delete</a>
      <?php } else { ?>
          <input type="submit" name="create" value="Create">
      <?php } ?>
    </p>
  </form>
