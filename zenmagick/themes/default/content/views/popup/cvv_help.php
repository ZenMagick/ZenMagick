<?php
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
?>

<h1><?php zm_l10n("What is CVV?") ?></h1>

<div>
  <p><strong>Visa, Mastercard, Discover</strong> 3 Digit Card Verification Number:</p>
  <p>For your safety and security, we require that you enter your card's verification number.</p>
  <p>The verification number is a 3-digit number printed on the back of your card. It appears after and
   to the right of your card number.</p>
  <img src="<?php $zm_theme->themeURL("images/icons/cvv2visa.gif") ?>" alt="cvv sample 1" />
</div>

<div>
  <p><strong>American Express</strong> 4 Digit Card Verification Number:</p>
  <p>For your safety and security, we require that you enter your card's verification number.</p>
  <p>The American Express verification number is a 4-digit number printed on the front of your card.  It appears after and
   to the right of your card number.</p>
  <img src="<?php $zm_theme->themeURL("images/icons/cvv2amex.gif") ?>" alt="cvv sample 2" />
</div>

<div id="close"><a href="#" onclick="javascript:window.close()"><?php zm_l10n("Close Window [x]") ?></a></div>
