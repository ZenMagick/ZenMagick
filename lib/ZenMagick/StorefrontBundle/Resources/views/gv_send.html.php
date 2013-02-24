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
<?php $crumbtrail->addCrumb(_zm('Account'), $net->generate('account'))->addCrumb(_zm('Send Gift Certificate')) ?>
<p><?php _vzm("Current available balance: <strong>%s</strong>", $utils->formatMoney($app->getUser()->getVoucherBalance())) ?></p>
<?php echo $form->open('gv_send', '', true, array('id'=>'gvreceiverObject')) ?>
    <fieldset>
        <legend><?php _vzm("EMail Gift Certificate") ?></legend>
        <label for="name"><?php _vzm("Receiver Name") ?></label><br />
        <input type="text" id="name" name="name" size="40" value="<?php echo $view->escape($gvReceiver->getName()) ?>" /><br />
        <label for="email"><?php _vzm("Receiver EMail Address") ?><span>*</span></label><br />
        <input type="text" id="email" name="email" size="40" value="<?php echo $view->escape($gvReceiver->getEmail()) ?>" /><br />
        <label for="amount"><?php _vzm("Gift Certificate Amount") ?><span>*</span></label><br />
        <?php /* Do not convert the amout - either it is 0 or entered by the user; in either case it is fine as  is */ ?>
        <input type="text" id="amount" name="amount" value="<?php echo $utils->formatMoney($gvReceiver->getAmount(), false) ?>" /><br />
        <label for="message"><?php _vzm("Message Text") ?></label><br />
        <textarea id="message" name="message" cols="50" rows="8"><?php echo $view->escape($gvReceiver->getMessage()) ?></textarea><br />
        <p class="man"><?php _vzm("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send Gift Certificate") ?>" /></div>
</form>

<p class="rclear">
    <strong><?php _vzm("The following message is included with all emails sent from this site:") ?></strong><br />
    <?php echo $utils->staticPageContent('email_advisory') ?>
</p>
