<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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

<?php if (false === strpos($request->getRequestId(), 'search')) { ?>
    <h3><?php _vzm("Quick Search") ?></h3>
    <div id="sb_search" class="box">
        <?php echo $form->open('search', '', $request->isSecure(), array('method' => 'get')) ?>
            <div>
                <input type="submit" class="btn" value="<?php _vzm("Go") ?>" />
                <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
                <input type="text" id="keywords" name="keywords" value="<?php echo $view->escape($request->query->get('keywords', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
            </div>
        </form>
        <a class="clear" href="<?php echo $view['router']->generate('advanced_search') ?>"><?php _vzm("Advanced Search") ?></a>
    </div>
<?php } ?>
