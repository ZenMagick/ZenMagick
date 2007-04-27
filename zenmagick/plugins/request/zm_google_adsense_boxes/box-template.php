<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006,2007 ZenMagick
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
<?php

    /**
     * Box contents for Google AdSense.
     *
     * The box id is replaced with a *real* one during code generation.
     */

    $gjs = zm_google_adsense_get_js_for_box('BOX_ID', false);

?>
<?php if (!zm_is_empty($gjs) && !$zm_request->isSecure()) { ?>
    <h3></a><?php zm_l10n("Ads by Goooooogle") ?></h3>
    <div class="box google">
        <?php echo $gjs ?>
    </div>
<?php } ?>
