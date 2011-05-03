<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

<?php if (false === strpos($request->getRequestId(), 'advanced_search')) { ?>
    <h1>Search Box</h1>	
    <?php echo $form->open('search', '', false, array('method' => 'get', 'class' => 'searchform')) ?>
      <p>
        <input type="hidden" name="search_in_description" value="1" />
        <?php define('KEYWORD_DEFAULT', _zm("enter search")); ?>
        <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
        <input name="keywords" class="textbox" type="text" value="<?php echo $html->encode($request->getParameter('keywords', KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
        <input name="search" class="button" value="Search" type="submit" /><br />
        <a href="<?php echo $net->url(FILENAME_ADVANCED_SEARCH) ?>"><?php _vzm("Advanced Search") ?></a>
      </p>			
    </form>			
<?php } ?>
