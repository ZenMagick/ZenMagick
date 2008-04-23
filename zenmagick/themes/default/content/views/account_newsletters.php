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
<p><?php zm_l10n("Including store news, new products, special offers, and other promotional announcements.") ?></p>
<?php $form->open(FILENAME_ACCOUNT_NEWSLETTERS, "action=process", true, array('onsubmit'=>null)) ?>
    <fieldset>
        <legend><?php zm_l10n("Store Newsletter") ?></legend>
        <p><input type="checkbox" id="newsletter_general" name="newsletter_general" value="1"<?php $form->checked($zm_account->isNewsletterSubscriber(), true) ?> /><label for="newsletter_general"><?php zm_l10n("Receive Store Newsletter") ?></label></p>
    </fieldset>
    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Update") ?>" /></div>
</form>
