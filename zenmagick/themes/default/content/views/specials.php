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

<?php if ($zm_resultList->hasResults()) { ?>
    <div class="rlist">
        <table cellspacing="0" cellpadding="0"><tbody>
            <?php $first = true; $odd = true; foreach ($zm_resultList->getResults() as $product) { ?>
              <?php include('resultlist/product.php') ?>
            <?php $first = false; $odd = !$odd; } ?>
        </tbody></table>
    </div>
<?php } else { ?>
    <h2><?php zm_l10n("There are no products in this category") ?></h2>
<?php } ?>
