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

<h3><?php _vzm("Languages") ?></h3>
<div id="sb_languages" class="box">
    <?php $ii = 0; foreach ($container->get('languageService')->getLanguages() as $lang) { ?>
        <a href="<?php echo $net->url('set_language', array('language' => $lang->getCode())) ?>"><img src="<?php echo $this->asUrl("images/lang/" . $lang->getDirectory() . "/" . $lang->getImage()) ?>" alt="<?php echo $html->encode($lang->getName()) ?>" title="<?php echo $html->encode($lang->getName()) ?>" /></a>
        <?php if (0 == $ii%5 && 0 < $ii) { ?><br /><?php } ?>
    <?php ++$ii; } ?>
</div>
