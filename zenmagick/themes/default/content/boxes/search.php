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

<h3><?php zm_l10n("Quick Search") ?></h3>
<div id="sb_search" class="box">
    <?php zm_form(FILENAME_ADVANCED_SEARCH_RESULT, '', null, "get") ?>
        <div>
            <input type="hidden" name="search_in_description" value="1" />
            <input type="submit" class="btn" value="<?php zm_l10n("Go") ?>" />
            <?php $onfocus = "if(this.value=='" . zm_l10n_get("enter search") . "') this.value='';" ?>
            <input type="text" id="keyword" name="keyword" value="<?php zm_l10n("enter search") ?>" onfocus="<?php echo $onfocus ?>" />
        </div>
    </form>
    <a class="clear" href="<?php zm_href(FILENAME_ADVANCED_SEARCH) ?>"><?php zm_l10n("Advanced Search") ?></a>
</div>
