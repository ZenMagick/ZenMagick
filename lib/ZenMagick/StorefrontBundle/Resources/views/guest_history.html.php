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
<?php $crumbtrail->addCrumb(_zm('Guest Order Status')) ?>
<?php echo $form->open('guest_history', '', true, array('id'=>'guest_history')) ?>
  <fieldset>
    <legend><?php _vzm("Find Guest Order") ?></legend>
    <div>
      <label for="email"><?php _vzm("E-Mail Address") ?></label>
      <input type="text" id="email" name="email" value="<?php echo $html->encode($request->request->get('email', '')) ?>" />
    </div>
    <div>
      <label for="orderId"><?php _vzm("Order Number") ?></label>
      <input type="text" id="orderId" name="orderId" value="<?php echo $html->encode($request->request->get('orderId', '')) ?>" />
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
</form>
