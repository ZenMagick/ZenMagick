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
<?php $admin2->title() ?>

<div class="col3" style="float:left;width:32%;border:1px solid gray;padding:5px;margin:1px;">
  <h2>Blocks</h2>
  <ul id="blockList">
    <?php foreach ($blocks as $def => $title) { ?>
      <li><span class="clean <?php echo $def ?>"><?php echo $title ?></span></li>
    <?php } ?>
  </ul>
</div>

<div class="col3" style="float:left;width:32%;border:1px solid gray;padding:5px;margin:1px;">
  <h2>Block Group Setup: <?php echo $groupId ?></h2>
  <ul id="groupBlockList" class="sortable" style="min-height:5em;min-width:25em;border:1px solid gray;">
  </ul>
</div>



<script>
$(function() {
		$("#groupBlockList").sortable({
			revert: true
		});
		$("#blockList li").draggable({
			connectToSortable: "#groupBlockList",
			helper: "clone",
      snap: true,
			revert: "invalid"
		});
		$("ul, li").disableSelection();
	});
$( ".draggable" ).draggable({
  stop: function(event, ui) { 
    if (this.parentNode && this.parentNode.id == 'blockList') {
        //alert('yup');
    }
  }
});
$("#groupBlockList").droppable({
  drop: function(event, ui) {
   var span = $('span.clean', ui.draggable);
   if (span) {
      // TODO: first show spinner until ajax call is done
      // TODO: move style in css file
      var icons = '<div class="icons" style="float:right"><span class="ui-icon ui-icon-circle-close"></span></div>';
      $(icons).insertAfter(span);
      span.removeClass('clean');
      // add close handler
      $('span.ui-icon-circle-close', ui.draggable).click(function() {
          $(this.parentNode.parentNode).remove();
      });

      // TODO: ajax call to create blocks_to_groups entry
      // once complete, add edit icon
      return span;
    }
  }
});

</script>
