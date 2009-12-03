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

<?php $form->open(FILENAME_TELL_A_FRIEND, 'action=process&products_id=' . $request->getProductId(), true, array('id'=>'tellAFriend')) ?>
   <fieldset>
        <legend><?php zm_l10n("Tell a friend about '%s'", $zm_product->getName()); ?></legend>

        <label for="fromName"><?php zm_l10n("Your Name") ?><span>*</span></label>
        <input type="text" id="fromName" name="fromName" size="40" value="<?php $html->encode($tellAFriend->getFromName()) ?>" /><br />

        <label for="fromEmail"><?php zm_l10n("Your Email") ?><span>*</span></label>
        <input type="text" id="fromEmail" name="fromEmail" size="40" value="<?php $html->encode($tellAFriend->getFromEmail()) ?>" /><br />

        <label for="toName"><?php zm_l10n("Friend's Name") ?><span>*</span></label>
        <input type="text" id="toName" name="toName" size="40" value="<?php $html->encode($tellAFriend->getToName()) ?>" /><br />

        <label for="toEmail"><?php zm_l10n("Friend's Email") ?><span>*</span></label>
        <input type="text" id="toEmail" name="toEmail" size="40" value="<?php $html->encode($tellAFriend->getToEmail()) ?>" /><br />

        <label for="message"><?php zm_l10n("Message") ?></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php $html->encode($tellAFriend->getMessage()) ?></textarea>
        <p class="legend"><?php zm_l10n("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send") ?>" /></div>
</form>
<div class="advisory">
    <strong><?php zm_l10n("The following message is included with all emails sent from this site:") ?></strong><br />
    <?php echo zm_l10n_chunk_get('email_advisory', ZMSettings::get('storeEmail')) ?>
</div>
