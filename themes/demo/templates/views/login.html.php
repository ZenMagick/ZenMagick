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
<?php $crumbtrail->addCrumb(_zm('Login')) ?>

<?php $resourceManager->jsFile('js/jquery.js', $resourceManager::NOW) ?>
<?php $resourceManager->jsFile('js/jquery.form.js', $resourceManager::NOW) ?>

<div id="msgbox" style="height:1.8em;border:1px solid gray;margin:5px 0 12px;padding:3px;color:red"></div>

<script>
    var msgboxElem = document.getElementById('msgbox');

    function ajax_login(id) {
        var queryString = $('#'+id).formSerialize();

        msgboxElem.innerHTML = "Logging in... ";

        $.ajax({
            type: "POST",
            url: "<?php echo $view['router']->generate('login') ?>",
            data: queryString,
            success: function(msg) {
                var info =JSON.parse(msg);
                msgboxElem.innerHTML += "got response ... ";
                msgboxElem.innerHTML += ('success' == info) ? 'success' : 'failed';
                msgboxElem.innerHTML += " - done!";
            },
            error: function(msg) {
                msgboxElem.innerHTML += "failed!";
            }
        });
    }
</script>

<?php echo $form->open('login', '', true, array('id' => 'login', 'method' => 'post', 'onsubmit' => 'return zmFormValidation.validate(this);')) ?>
  <fieldset>
    <legend><?php _vzm("Login") ?></legend>
    <div>
      <label for="email_address"><?php _vzm("E-Mail Address") ?></label>
      <input type="text" id="email_address" name="email_address" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> />
      <?php echo $html->fieldMessages('email_address') ?>
    </div>
    <div>
      <label for="password"><?php _vzm("Password") ?></label>
      <input type="password" id="password" name="password" <?php echo $form->fieldLength('customers', 'customers_password') ?> />
      <?php echo $html->fieldMessages('password') ?>
    </div>
  </fieldset>
  <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Submit") ?>" /></div>
  <div class="btn"><a href="#" onclick="ajax_login('login'); return false;"><?php echo _zm("Login via Ajax") ?></a></div>
</form>

<p>
  <a href="<?php echo $view['router']->generate('password_forgotten') ?>"><?php _vzm("Lost your password?") ?></a><br />
  <a href="<?php echo $view['router']->generate('create_account'); ?>"><?php _vzm("Not registered yet?") ?></a>
</p>

<?php if ($settingsService->get('isGuestCheckout') && !$view->container->get('shoppingCart')->isEmpty()) { ?>
  <h3><?php _vzm("Don't need an account?") ?></h3>
  <?php echo $form->open('checkout_guest', '', true, array('id' => 'checkout_guest', 'method' => 'post', 'onsubmit' => 'return zmFormValidation.validate(this);')) ?>
    <fieldset>
      <legend><?php _vzm("Checkout without registering") ?></legend>
      <div>
        <label for="email_address_guest"><?php _vzm("E-Mail Address") ?></label>
        <input type="text" id="email_address_guest" name="email_address" <?php echo $form->fieldLength('customers', 'customers_email_address') ?> />
        <?php echo $html->fieldMessages('email_address') ?>
        <input type="submit" class="btn" value="<?php _vzm("Checkout") ?>" />
      </div>
    </fieldset>
  </form>
<?php } ?>
