<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
 */
?>

<?php if (isset($whoIsOnline)) { ?>
    <?php $stats = $whoIsOnline->getStats(); ?>
    <?php if (0 < $stats['total']) { ?>
        <h3><?php _vzm("Who's Online") ?></h3>
        <div id="sb_whos_online" class="box">
            <?php
                $out = '';
                if (1 == $stats['total']) {
                    // 1
                    $out .= _zm("There is currently");
                    if (1 == $stats['anonymous']) {
                        $out .= _zm(" one guest online.");
                    } else {
                        $out .= _zm(" one registered user online.");
                    }
                } else {
                    // >1
                    $out .= _zm("There are currently");
                    if (1 == $stats['anonymous']) {
                        $out .= _zm(" one guest");
                    } else if (1 < $stats['anonymous']) {
                        $out .= sprintf(_zm(" %s guests"), $stats['anonymous']);
                    }
                    if (0 < $stats['anonymous'] && 0 < $stats['registered']) {
                        $out .= _zm(" and");
                    }
                    if (1 == $stats['registered']) {
                        $out .= _zm(" one registered user");
                    } else if (1 < $stats['registered']) {
                        $out .= sprintf(_zm(" %s registered users"), $stats['registered']);
                    }
                    $out .= _zm(' online.');
                }
                echo $out;
            ?>
        </div>
    <?php } ?>
<?php } ?>
