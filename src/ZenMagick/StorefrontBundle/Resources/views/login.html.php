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
<?php $view['slots']->set('crumbtrail', $crumbtrail->addCrumb(_zm('Login'))); ?>
<?php echo $form->open('login_check', '', true, array('id'=>'login')) ?>
  <fieldset>
    <legend><?php _vzm("Login") ?></legend>
    <div>
      <label for="email_address"><?php _vzm("E-Mail Address") ?></label>
      <input type="text" id="email_address" name="email_address" value="<?php echo $lastUsername; ?>" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> />
    </div>
    <div>
      <label for="password"><?php _vzm("Password") ?></label>
      <input type="password" id="password" name="password" <?php echo $form->fieldLength('customers', 'customers_password') ?> />
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
</form>

<p>
  <a href="<?php echo $view['router']->generate('password_forgotten') ?>"><?php _vzm("Lost your password?") ?></a><br />
  <a href="<?php echo $view['router']->generate('create_account'); ?>"><?php _vzm("Not registered yet?") ?></a>
</p>

<?php if ($view['settings']->get('isGuestCheckout') && !$view->container->get('shoppingCart')->isEmpty() && !$app->getUser()) { ?>
  <h3><?php _vzm("Don't need an account?") ?></h3>
  <?php echo $form->open('checkout_guest', '', true, array('id'=>'checkout_guest')) ?>
    <p><?php _vzm("Checkout without registering") ?></p>
    <div>
      <?php if ($view['settings']->get('isGuestCheckoutAskAddress')) { ?>
        <?php
          $guestAddressInfo = array(
              'address' => $guestCheckoutAddress,
              'customFields' => array(array(
                  'label'=>_zm("E-Mail Address").'<span>*</span>',
                  'field'=>'<input type="text" id="email_address_guest" name="email_address" '.$form->fieldLength('customers', 'customers_email_address').'/>'
              ))
          );
          echo $this->render('StorefrontBundle::address.html.php', $guestAddressInfo);
        ?>
      <?php } else { ?>
        <label for="email_address_guest"><?php _vzm("E-Mail Address") ?></label>
        <input type="text" id="email_address_guest" name="email_address" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> />
      <?php } ?>
    </div>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Checkout") ?>" /></div>
  </form>
<?php } ?>
