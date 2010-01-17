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

<?php $form->open('gv_send_confirm', null, true, array('onsubmit'=>null)) ?>
    <div>
        <input type="hidden" name="name" value="<?php $html->encode($gvReceiver->getName()) ?>" />
        <input type="hidden" name="email" value="<?php $html->encode($gvReceiver->getEmail()) ?>" />
        <input type="hidden" name="amount" value="<?php $html->encode($gvReceiver->getAmount()) ?>" />
        <input type="hidden" name="message" value="<?php $html->encode($gvReceiver->getMessage()) ?>" />
    </div>
    <fieldset>
        <legend><?php zm_l10n("Confirm Send Gift Certificate") ?></legend>

        <p class="note"><?php zm_l10n("You are about to post a Gift Certificate worth %s to %s whose email address is %s.",
          $utils->formatMoney($gvReceiver->getAmount(), false, false), $gvReceiver->getName(), $gvReceiver->getEmail()) ?>
        </p>

        <fieldset>
            <legend><?php zm_l10n("Your message") ?></legend>
            <?php echo zm_get_email_contents('email/gv_send', true, compact('currentAccount', 'gvReceiver', 'currentCoupon', array('isSupressDisclaimer' => false))) ?>
        </fieldset>

        <p class="note"><?php zm_l10n("If these details are not correct, you may edit your message by clicking the edit button.") ?></p>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send Gift Certificate") ?>" /></div>
    <div><input type="submit" class="btn" name="edit" value="<?php zm_l10n("Edit") ?>" /></div>
</form>

<div class="advisory">
    <strong><?php zm_l10n("The following message is included with all emails sent from this site:") ?></strong><br />
    <?php echo $zm_theme->staticPageContent('email_advisory') ?>
</div>
