<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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
<?php $resources->cssFile('style/views/block_group_admin.css') ?>
<?php $admin2->title() ?>

<div class="col3">
  <h2>Blocks</h2>
  <ul id="blockList" class="ui-sortable">
    <?php foreach ($blocks as $def => $title) { ?>
      <li><span class="clean <?php echo $def ?>"><?php echo $title ?></span><div class="icons"></div></li>
    <?php } ?>
  </ul>
</div>

<div class="col3">
  <h2>Block Group Setup: <?php echo $groupId ?></h2>
  <ul id="groupBlockList" class="sortable">
  </ul>
</div>

<script>
$(function() {
		$("#groupBlockList").sortable({
      receive: function(event, ui) {
        var span = $('#groupBlockList span.clean');
        var iconContainer = $('#groupBlockList span.clean + div.icons');

        var throbber = '<span class="throbber"></span>';
        iconContainer.html(throbber);
        span.removeClass('clean');

        // pretend this is a ajax success callback for creating a new group/block mapping
        window.setTimeout(function() {
            // grab from the receiving list
            var icons = '<span class="ui-icon ui-icon-wrench"></span><span class="ui-icon ui-icon-circle-close"></span>';
            iconContainer.html(icons);

            // TODO: once complete, add edit icon
            $('span.ui-icon-wrench', span.parentNode).click(function() {
              alert('configure...');
              // TODO: do something
            });
            // add close handler
            $('span.ui-icon-circle-close', span.parentNode).click(function() {
              // remove again
              $(this.parentNode.parentNode).remove();
              // TODO: ajax call to remove block from group
            });
        }, 1300);
      }
		});
		$("#blockList li").draggable({
			connectToSortable: "#groupBlockList",
			helper: "clone",
      cursor: "move",
      snap: true,
			revert: "invalid"
		});
	});
</script>
