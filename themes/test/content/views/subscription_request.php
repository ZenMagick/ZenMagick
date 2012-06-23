<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

<h3><?php _vzm("Subscription Enqiries") ?></h3>

<?php echo $form->open('', null, true, array('method' => 'post', 'id' => 'subscription_request')) ?>
    <fieldset>
        <legend><?php _vzm("Subscription Enquiries") ?></legend>
        <label for="type"><?php _vzm("Request Type") ?><span>*</span></label>
        <select id="type" name="type">
            <?php foreach ($subscriptions->getRequestTypes() as $type => $name) { ?>
                <?php $selected = $subscriptionRequest->getType() == $type ? ' selected' : ''; ?>
                <option value="<?php echo $type ?>"<?php echo $selected ?>><?php echo $html->encode($name) ?>  </option>
            <?php } ?>
        </select><br />

        <label for="orderId"><?php _vzm("Order Number (optional)") ?></label>
        <input type="text" id="orderId" name="orderId" size="8" value="<?php echo $html->encode($subscriptionRequest->getOrderId()) ?>" /><br />

        <label for="message"><?php _vzm("Message") ?><span>*</span></label>
        <textarea id="message" name="message" cols="30" rows="7"><?php echo $html->encode($subscriptionRequest->getMessage()) ?></textarea>
        <p class="legend"><?php _vzm("<span>*</span> Mandatory fields") ?></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Send") ?>" /></div>
</form>
