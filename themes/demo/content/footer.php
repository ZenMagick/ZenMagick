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

<div id="footer">
    <p id="fpages">
        <?php foreach ($container->get('ezPageService')->getPagesForFooter($session->getLanguageId()) as $page) { ?>
            <?php echo $html->ezpageLink($page->getId()) ?>
        <?php } ?>
    </p>

    <div id="bannerFive"><?php echo $this->fetchBlockGroup('banners.footer2') ?></div>

    <p id="sview">
        <a href="<?php echo $net->url('source_view', 'layout_name='.$view->getLayout()) ?>">Layout: '<?php echo basename($view->getLayout()) ?>'</a>
        <a href="<?php echo $net->url('source_view', 'view_name='.$request->getRequestId()) ?>">View: '<?php echo basename($request->getRequestId()) ?>'</a>
    </p>
    <p>Powered by <a href="http://www.zen-cart.com">zen-cart</a> and <a href="http://www.zenmagick.org">ZenMagick</a></p>
    <p>&copy; 2006-2012  <a href="http://www.zenmagick.org">ZenMagick</a> |
      Design based on <a href="http://www.freecsstemplates.org/preview/convergence">Convergence</a> by
      <a href="http://www.freecsstemplates.org/">Free CSS Templates</a></p>
</div>
