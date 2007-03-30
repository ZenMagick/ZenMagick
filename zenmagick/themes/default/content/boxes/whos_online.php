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

<?php $counts = zm_get_online_counts(); ?>
<?php if (0 < $counts[0]) { ?>
    <h3><?php zm_l10n("Who's Online") ?></h3>
    <div id="sb_whos_online" class="box">
        <?php $out = ''; ?>
        <?php if (1 == $counts[0]) { /* one session */ ?>
            <?php $out = zm_l10n_get("There currently is") ?>
        <?php } else { /* many sessions */ ?>
            <?php $out = zm_l10n_get("There currently are") ?>
        <?php } ?>

        <?php if (1 == $counts[1]) { /* one guest */ ?>
            <?php $out .= zm_l10n_get(" one guest") ?>
        <?php } else if (1 < $count[1]) { /* many guests */ ?>
            <?php $out .= zm_l10n_get(" %s guests", $counts[1]) ?>
        <?php } ?>

        <?php if (1 == $counts[2]) { ?>
            <?php $out .= zm_l10n_get(" and one member online.") ?>
        <?php } else if (1 < $count[2]) { ?>
            <?php $out .= zm_l10n_get(" and % members online.", $counts[2]) ?>
        <?php } else { ?>
            <?php $out .= zm_l10n_get(" online.", $counts[2]) ?>
        <?php } ?>
        <?php echo $out ?>
    </div>
<?php } ?>
