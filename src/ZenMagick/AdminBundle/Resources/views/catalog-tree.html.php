<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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

    $initially_open = '';
    foreach ((array) $view['request']->getParameter('categoryIds') as $categoryId) {
        if (!empty($initially_open)) {
            $initially_open .= ',';
        }
        $initially_open .= '"ct-'.$categoryId.'"';
    }
?>

<div id="category-tree">
  <?php echo $admin->categoryTree(); ?>
</div>

<script type="text/javascript">
$(function () {
  $("#category-tree").jstree({
    core: {
      animation: 200,
      initially_open: [<?php echo $initially_open ?>]
    },
    plugins : ["html_data", "ui", "contextmenu", "themeroller"],
    themeroller: {
      opened: "ui-icon-circle-minus",
      closed: "ui-icon-circle-plus",
      item: "ui-icon-empty",
      leaf_icon: "ui-icon-empty",
      item_icon: "ui-icon-empty"
    },
    contextmenu: {
      show_at_node : false,
      items: function(node) {
        return {
          "create": {
            "separator_before": false,
            "separator_after": true,
            "icon": "ui-icon ui-icon-plusthick",
            "label": "Create",
            "action": function (obj) { this.create(obj); }
          },
          "rename": {
            "separator_before": false,
            "separator_after": false,
            "icon": "ui-icon ui-icon-pencil",
            "label": "Rename",
            "action": function (obj) { this.rename(obj); }
          },
          "remove": {
            "separator_before": false,
            "separator_after": false,
            "icon": "ui-icon ui-icon-scissors",
            "label": "Remove",
            "action": function (obj) { this.rename(obj); }
          }
        }
      }
    }
  })
.delegate('a', 'click', function (event, data) { window.location = this; })
})
;
</script>
