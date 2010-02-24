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

<?php $toc = ZMEZPages::instance()->getPagesForChapterId($request->getParameter("chapter"), $session->getLanguageId()); ?>
<?php if (1 < count($toc)) { ?>
    <div id="eztoc">
        <h4><?php zm_l10n("Table of Contents") ?></h4>
        <ul>
        <?php foreach ($toc as $page) { $active = $page->getId() == $ezPage->getId() ? ' class="act"' : ''; ?>
            <li<?php echo $active ?>><a href="<?php echo $net->ezPage($page) ?>"><?php echo $html->encode($page->getTitle()) ?></a></li>
        <?php } ?>
        </ul>
        <?php
            // find pref next
            $prev = null;
            $next = null;
            $size = count($toc);
            for ($ii=0; $ii < $size; $ii++) {
                if ($toc[$ii]->getId() == $ezPage->getId()) {
                    // got the current page
                    if (0 == $ii) {
                        // first
                        $prev = $toc[$size-1];
                        if (1 < $size) $next = $toc[1];
                    } else if ($size-1 == $ii) {
                        // last
                        if (0 < $ii) $prev = $toc[$ii-1];
                        $next = $toc[0];
                    } else {
                        $prev = $toc[$ii-1];
                        $next = $toc[$ii+1];
                    }
                    break;
                }
            }
        ?>
        <?php if (null != $prev && null != $next) { ?>
        <p>
            <a href="<?php echo $net->ezPage($prev) ?>"><?php zm_l10n("&lt; Prev") ?></a>
            <a href="<?php echo $net->ezPage($next) ?>"><?php zm_l10n("Next &gt;") ?></a>
        </p>
        <?php } ?>
    </div>
<?php } ?>
<h2><?php echo $html->encode($ezPage->getTitle()) ?></h2>
<?php echo eval('?>'.$ezPage->getHTMLText()) ?>
