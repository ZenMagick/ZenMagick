{*
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
 * $Id: menu.php 299 2007-08-20 01:09:29Z DerManoMann $
*}

<div id="menu">
  <ul>
    <li class="first"><a href="{$zm->href($smarty.const.FILENAME_DEFAULT)}">{$zm->l10n("HOME")}</a></li>
    {if ($zm->ZMRequest->isAnonymous())}
        <li><a href="{$zm->secure_href($smarty.const.FILENAME_LOGIN)}">{$zm->l10n("LOGIN")}</a></li>
    {/if}
    
    {if ($zm->ZMRequest->isRegistered())}
        <li><a href="{$zm->secure_href($smarty.const.FILENAME_ACCOUNT)}">{$zm->l10n("ACCOUNT")}</a></li>
    {/if}
    {if (!$zm->ZMRequest->isAnonymous())}
        <li><a href="{$zm->secure_href($smarty.const.FILENAME_LOGOFF)}">{$zm->l10n("LOGOFF")}</a></li>
    {/if}
    {if (!$zm_cart->isEmpty() && !zm_is_checkout_page())}
        <li><a href="{$zm->secure_href($smarty.const.FILENAME_SHOPPING_CART)}">{$zm->l10n("SHOPPING CART")}</a></li>
        <li><a href="{$zm->secure_href($smarty.const.FILENAME_CHECKOUT_SHIPPING)}">{$zm->l10n("CHECKOUT")}</a></li>
    {/if}
    {if ($zm->ZMSettings->get('isShowEZHeaderNav'))}
        {assign var=pages value=$zm_pages->getPagesForHeader()}
        {foreach from=$pages item=page}
            <li>{$zm->ezpage_link($page->getId())}</li>
        {/foreach}
    {/if}
  </ul>
</div>
