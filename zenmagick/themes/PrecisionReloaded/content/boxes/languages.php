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

<h2><?php zm_l10n("Languages") ?></h2>
<div id="sb_languages" class="box">
    <?php $ii = 0; foreach (ZMLanguages::instance()->getLanguages() as $language) { ?>
        <a href="<?php echo $net->url(null, "language=".$language->getCode()) ?>"><img src="<?php echo $this->asUrl("images/lang/" . $language->getDirectory() . "/" . $language->getImage()) ?>" alt="<?php echo $html->encode($language->getName()) ?>" title="<?php echo $html->encode($language->getName()) ?>" /></a>
        <?php if (0 == $ii%5 && 0 < $ii) { ?><br /><?php } ?>
    <?php ++$ii; } ?>
</div>
