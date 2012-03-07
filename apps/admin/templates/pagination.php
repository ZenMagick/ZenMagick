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
 */ if (1 < $resultList->getNumberOfPages()) { ?>
    <div class="rnav">
        <span class="pno"><?php _vzm("Page %s/%s", $resultList->getPageNumber(), $resultList->getNumberOfPages()) ?></span>
        <?php if ($resultList->hasPreviousPage()) { ?>
            <a href="<?php echo $net->resultListBack($resultList, null, array('orderStatusId')) ?>"><?php _vzm("Previous") ?></a>&nbsp;
        <?php } else { ?>
            <span class="nin"><?php _vzm("Previous") ?></span>&nbsp;
        <?php } ?>
        <?php if ($resultList->hasNextPage()) { ?>
            <a href="<?php echo $net->resultListNext($resultList, null, array('orderStatusId')) ?>"><?php _vzm("Next") ?></a>
        <?php } else { ?>
            <span class="nin"><?php _vzm("Next") ?></span>
        <?php } ?>
    </div>
<?php } ?>
