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

<?php if (isset($whoIsOnline)) { ?>
    <?php $stats = $whoIsOnline->getStats(); ?>
    <?php if (0 < $stats['total']) { ?>
        <h3><?php _vzm("Who's Online") ?></h3>
        <div id="sb_whos_online" class="box">
            <?php _vzm("There are currently %s guests and %s registered users online.", $stats['anonymous'], $stats['registered']); ?>
        </div>
    <?php } ?>
<?php } ?>
