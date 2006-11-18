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

<?php $currencyList = $zm_currencies->getCurrencies(); ?>
<?php if (0 < count($currencyList) && !zm_starts_with($zm_request->getPageName(), 'checkout')) { ?>
    <h3><?php zm_l10n("Currencies") ?></h3>
    <div id="sb_currencies" class="box">
        <?php zm_form(FILENAME_DEFAULT, '', null, "get") ?>
            <div>
                <?php zm_idp_select('currency', $currencyList, 1, $zm_request->getCurrencyId(), 'this.form.submit()') ?>
                <noscript>
                    <div><input type="submit" class="btn" value="<?php zm_l10n('Go') ?>" /></div>
                </noscript>
            </div>
        </form>
    </div>
<?php } ?>
