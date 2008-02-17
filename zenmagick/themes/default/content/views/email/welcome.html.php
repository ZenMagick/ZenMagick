<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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
?><?php
$language = $zm_runtime->getlanguage();
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="<?php echo $language->getCode() ?>">
<head>
<title><?php zm_l10n("Welcome to %s", zm_setting('storeName')) ?></title>
</head>
<body>
<body>
<div style="font-family:Verdana,Arial,Helvetica,sans-serif;font-size:10pt;">
<p><?php zm_l10n("Dear %s %s,", $zm_account->getFirstName(), $zm_account->getLastName()) ?></p>

<p><?php zm_l10n("We wish to welcome you to %s.", zm_setting('storeName')) ?></p>
<div><?php echo zm_l10n_chunk_get('email_welcome', zm_setting('storeName'), zm_setting('storeEmail'), zm_setting('storeOwner')) ?></div>
<p><?php zm_l10n("Sincerely, %s", zm_setting('storeOwner')) ?></p>

<?php echo zm_l10n_chunk_get('email_advisory', zm_setting('storeEmail')) ?>
<?php echo $office_only_html; ?>
</div>
</body>
</html>
