<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php if (false === strpos($request->getRequestId(), 'advanced_search')) { ?>
    <h3><?php _vzm("Quick Search") ?></h3>
    <div id="sb_search" class="box">
        <?php echo $form->open('search', '', false, array('method' => 'get')) ?>
            <div>
                <input type="hidden" name="search_in_description" value="1" />
                <?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
                <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
                <input type="text" id="keyword" name="keyword" value="<?php echo $html->encode($request->getParameter('keyword', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
                <input type="submit" class="btn" value="<?php _vzm("Go") ?>" /><br />
                <a class="clear" href="<?php echo $net->url(FILENAME_ADVANCED_SEARCH) ?>"><?php _vzm("Advanced Search") ?></a>
            </div>
        </form>
    </div>
<?php } ?>
