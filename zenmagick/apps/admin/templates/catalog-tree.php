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

<?php $resources->cssFile('style/catalog-tree/style.css') ?>
<?php $resources->jsFile('js/jquery.jstree.min.js') ?>

<div id="demo1" class="demo">
	<ul>
		<li id="phtml_1">
			<a href="#">Root node 1</a>
			<ul>
				<li id="phtml_2">
					<a href="#">Child node 1</a>
          <ul>
            <li id="phtml_5">
              <a href="#">Grandchild 5</a>
            </li>
            <li id="phtml_6">
              <a href="#">Grandchild 6 a bit longer</a>
            </li>
          </ul>
				</li>
				<li id="phtml_3">
					<a href="#">Child node 2</a>
				</li>
			</ul>
		</li>
		<li id="phtml_4">
			<a href="#">Root node 2</a>
		</li>
	</ul>
</div>
<script type="text/javascript">
$(function () {
	$("#demo1").jstree({ 
    core: {
      animation: 200,
      //initially_open: ["phtml_1", "phtml_3"]
      initially_open: []
    },
		plugins : ["themes", "html_data", "ui", "contextmenu"],
    themes: {
      dots: false
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
});
</script>
