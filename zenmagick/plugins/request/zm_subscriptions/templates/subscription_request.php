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

<h3><?php zm_l10n("Subscription Enqiries") ?></h3>

<?php $form->open('subscription_request', null, true, array('method' => 'post', 'id' => 'subscription_request')) ?>
    <fieldset>
        <legend><?php zm_l10n("Subscription Enquiries") ?></legend>
        <label for="type"><?php zm_l10n("Request Type") ?><span>*</span></label>
        <select id="type" name="type">
            <?php foreach ($zm_subscriptions->getRequestTypes() as $type => $name) { ?>
                <?php $selected = $zm_subscriptionRequest->getType() == $type ? ' selected' : ''; ?>
                <option value="<?php echo $type ?>"<?php echo $selected ?>><?php $html->encode($name) ?>  </option>
            <?php } ?>
        </select><br />

        <label for="orderId"><?php zm_l10n("Order Number (optional)") ?></label>
        <input type="text" id="orderId" name="orderId" size="8" value="<?php $html->encode($zm_subscriptionRequest->getOrderId()) ?>" /><br />

        <label for="message"><?php zm_l10n("Message") ?><span>*</span></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php $html->encode($zm_subscriptionRequest->getMessage()) ?></textarea>
        <p class="legend"><?php zm_l10n("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Send") ?>" /></div>
</form>
