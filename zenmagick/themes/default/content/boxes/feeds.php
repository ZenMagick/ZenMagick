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
?>

<h3><?php zm_l10n("RSS Feeds") ?></h3>
<div id="sb_feeds" class="box">
    <a href="<?php $net->rssFeed('reviews') ?>"><img src="<?php $zm_theme->themeURL('images/feed-icon-12x12.gif')?>" alt="<?php zm_l10n_get("RSS") ?>" /><?php zm_l10n("Product Reviews") ?></a>
    <a href="<?php $net->rssFeed('products', 'new') ?>"><img src="<?php $zm_theme->themeURL('images/feed-icon-12x12.gif')?>" alt="<?php zm_l10n_get("RSS") ?>" /><?php zm_l10n("New Products") ?></a>
    <a href="<?php $net->rssFeed('chapter', '10') ?>"><img src="<?php $zm_theme->themeURL('images/feed-icon-12x12.gif')?>" alt="<?php zm_l10n_get("RSS") ?>" /><?php zm_l10n("Chapter 10") ?></a>
</div>
