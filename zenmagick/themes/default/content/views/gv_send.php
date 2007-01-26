<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

<p><?php zm_l10n("Current available balance: <strong>%s</strong>", zm_format_currency($zm_account->getVoucherBalance(), false)) ?></p>

<?php zm_form(FILENAME_GV_SEND, 'action=send') ?>
    <fieldset>
        <legend><?php zm_l10n("EMail Gift Certificate") ?></legend>
        <label for="to_name"><?php zm_l10n("Receiver Name") ?></label><br />
        <input type="text" id="to_name" name="to_name" size="40" value="<?php echo $zm_gvreceiver->getName() ?>" /><br />
        <label for="email"><?php zm_l10n("Receiver EMail Address") ?><span>*</span></label><br />
        <input type="text" id="email" name="email" size="40" value="<?php echo $zm_gvreceiver->getEmail() ?>" /><br />
        <label for="amount"><?php zm_l10n("Gift Certificate Amount") ?><span>*</span></label><br />
        <input type="text" id="amount" name="amount" value="<?php zm_format_currency($zm_gvreceiver->getAmount()) ?>" /><br />
        <label for="message"><?php zm_l10n("Message Text") ?></label><br />
        <textarea id="message" name="message" cols="50" rows="8"><?php echo $zm_gvreceiver->getMessage() ?></textarea><br />
        <p class="man"><?php zm_l10n("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send Gift Certificate") ?>" /></div>
</form>

<p class="rclear">
    <strong><?php zm_l10n("This message is included with all emails sent from this site:") ?></strong><br />
    <?php zm_l10n('<strong>IMPORTANT:</strong> For your protection and to prevent malicious use, all emails sent via this web site are logged and the contents recorded and available to the store owner. If you feel that you have received this email in error, please send an email to %s', zm_setting('storeEmail')) ?>
</p>
