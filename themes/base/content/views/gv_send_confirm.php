<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
?>

<?php $crumbtrail->addCrumb(_zm('Account'), $request->url('account', '', true))->addCrumb(_zm('Send Gift Certificate')) ?>
<?php echo $form->open('gv_send_confirm', '', true, array('onsubmit'=>null)) ?>
    <div>
        <input type="hidden" name="name" value="<?php echo $html->encode($gvReceiver->getName()) ?>" />
        <input type="hidden" name="email" value="<?php echo $html->encode($gvReceiver->getEmail()) ?>" />
        <input type="hidden" name="amount" value="<?php echo $html->encode($gvReceiver->getAmount()) ?>" />
        <input type="hidden" name="message" value="<?php echo $html->encode($gvReceiver->getMessage()) ?>" />
    </div>
    <fieldset>
        <legend><?php _vzm("Confirm Send Gift Certificate") ?></legend>

        <p class="note"><?php _vzm("You are about to post a Gift Certificate worth %s to %s whose email address is %s.",
          $utils->formatMoney($gvReceiver->getAmount(), false), $gvReceiver->getName(), $gvReceiver->getEmail()) ?>
        </p>

        <fieldset>
            <legend><?php _vzm("Your message") ?></legend>
            <?php echo $html->encode($gvReceiver->getMessage()) ?>
        </fieldset>

        <p class="note"><?php _vzm("If these details are not correct, you may edit your message by clicking the edit button.") ?></p>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send Gift Certificate") ?>" /></div>
    <div><input type="submit" class="btn" name="edit" value="<?php _vzm("Edit") ?>" /></div>
</form>

<div class="advisory">
    <strong><?php _vzm("The following message is included with all emails sent from this site:") ?></strong><br />
    <?php echo $utils->staticPageContent('email_advisory') ?>
</div>
