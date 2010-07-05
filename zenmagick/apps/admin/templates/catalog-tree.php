<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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

<?php
    /**
     * Build category tree as simple unordered list.
     *
     * @param ZMRequest request The current request.
     * @param array categories List of root categories; default is <code>null</code>.
     * @param boolean start Flag to indicate start of recursion; default is <code>true</code>.
     * @return string The created HTML.
     */
    function _admin_category_tree($request, $categories=null, $start=true) {
        $admin2 = $request->getToolbox()->admin2;
        $path = $admin2->getCategoryPathArray();
        if ($start) { 
            ob_start(); 
            if (null === $categories) {
                $languageId = $request->getSelectedLanguage()->getId();
                $categories = ZMCategories::instance()->getCategoryTree($languageId);
            }
        }
        echo '<ul>';
        foreach ($categories as $category) {
            $active = in_array($category->getId(), $path);
            $cparams = $params.'&'.$category->getPath();
            echo '<li id="ct-'.$category->getId().'">';
            echo '<a href="'.$admin2->url('catalog', $category->getPath()).'">'.ZMHtmlUtils::encode($category->getName()).'</a>';
            if ($category->hasChildren()) {
                _admin_category_tree($request, $category->getChildren(), false);
            }
            echo '</li>';
        }
        echo '</ul>';

        if ($start) { 
            return ob_get_clean();
        }

        return '';
    }
?>

<?php $resources->cssFile('style/catalog-tree/style.css') ?>
<?php $resources->jsFile('js/jquery.jstree.min.js') ?>

<div id="category-tree">
  <?php echo _admin_category_tree($request); ?>
</div>

<script type="text/javascript">
$(function () {
	$("#category-tree").jstree({ 
    core: {
      animation: 200,
      initially_open: ["ct-3"]
    },
		plugins : ["themes", "html_data", "ui", "contextmenu"],
    themes: {
      dots: true
    },
    contextmenu: {
			show_at_node : false,
      items: function(node) {
        return {
          "create" : {
            "separator_before"	: false,
            "separator_after"	: true,
            "label"				: "Create",
            "action"			: function (obj) { this.create(obj); }
          },
          "rename" : {
            "separator_before"	: false,
            "separator_after"	: false,
            "label"				: "Rename",
            "action"			: function (obj) { this.rename(obj); }
          }
        }
      }
    }
	});
	$("#category-tree a").click(function(elem) {
      window.location = this.href;
	});
});
</script>
