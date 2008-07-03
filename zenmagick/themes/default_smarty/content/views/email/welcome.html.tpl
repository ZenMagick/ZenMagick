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
*}{assign var=language value=$zm_runtime->getlanguage()}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="{$language->getCode()}">
<head>
<title>{zms_l10n text="Welcome to %s" 1=$zm_setting.storeName}</title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p>{zms_l10n text="Dear %s %s," 1=$zm_account->getFirstName() 2=$zm_account->getLastName()}</p>

<p>{zms_l10n text="We wish to welcome you to %s." 1=$zm_setting.storeName}</p>
<div>{zms_l10n_chunk text='email_welcome' 1=$zm_setting.storeName 2=$zm_setting.storeEmail 3=$zm_setting.storeOwner}</div>
<p>{zms_l10n text="Sincerely, %s" 1=$zm_setting.storeOwner}</p>

{zms_l10n_chunk text='email_advisory' 1=$zm_setting.storeEmail}
{$office_only_html}
</div>
</body>
</html>
