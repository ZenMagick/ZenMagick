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
<?php

    /**
     * Build category tree as simple unordered list.
     *
     * <p>Requires jQuery and the jQuery tree view plugin.</p>
     *
     * @package net.zenmagick.admin
     * @param array categories List of start categories.
     * @param string urlBase Base url for category/product hrefs
     * @param string id The id of the wrapper div
     * @param boolean root Flag to indicate root level.
     */
    function zm_catalog_tree($categories=array(), $urlBase=null, $id='cat-tree', $root=true) {
    global $zm_request, $zm_categories;

        if ($root) { 
            echo <<<EOT
<script type="text/javascript" src="includes/jquery/jquery-1.2.1.pack.js"></script>
<script type="text/javascript" src="includes/jquery/jquery.treeview.pack.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
        $("#cat-tree").treeview({
          collapsed: true,
          unique: true,
          toggle: function() {
            $(".open");
          }
        });
    });
</script>
EOT;
            $urlBase = (null === $urlBase ? basename($PHP_SELF) : $urlBase);
            $zm_categories->setPath($zm_request->getCategoryPathArray());
            $categories = $zm_categories->getCategoryTree();
            ob_start(); 
            echo '<div id="'.$id.'">';
        }
        echo '<ul>';
        foreach ($categories as $category) {
            echo '<li class="'.($category->isActive() ? 'open' : '').'">';
            $url = $urlBase."?".$category->getPath();
            echo '<a href="'.$url.'">'.zm_htmlencode($category->getName(), false).'</a>';
            if ($category->hasChildren()) {
                zm_catalog_tree($category->getChildren(), $urlBase, $id, false);
            }
            echo '</li>';
        }
        echo '</ul>';

        if ($root) { 
            echo '</div>'; 
            echo ob_get_clean();
        }
    }

?>
