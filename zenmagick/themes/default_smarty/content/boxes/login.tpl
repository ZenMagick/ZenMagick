{* 
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
*}

{if $zm->ZMRequest->isAnonymous() && 'login' != $zm->ZMRequest->getPageName() && 'time_out' != $zm->ZMRequest->getPageName()}
    <h3>{$zm->l10n("Login")}</h3>
    <div id="sb_login" class="box">
        {$zm->secure_form($smarty.const.FILENAME_LOGIN, 'action=process')}
            <div>
                <label for="email_address">{$zm->l10n('E-Mail Address')}</label>
                <input type="text" id="email_address" name="email_address"> 
            </div>
            <div>
                <label for="password">{$zm->l10n('Password')}</label>
                <input type="submit" class="btn" value="{$zm->l10n('Login')}">
                <input type="password" id="password" name="password"> 
            </div>
          </form>
          <div>
              <a href="{$zm->secure_href($smarty.const.FILENAME_PASSWORD_FORGOTTEN)}">{$zm->l10n("Lost your password?")}</a>
              <a href="{$zm->secure_href($smarty.const.FILENAME_CREATE_ACCOUNT)}">{$zm->l10n("Not registered yet?")}</a>
          </div>
    </div>
{/if}
