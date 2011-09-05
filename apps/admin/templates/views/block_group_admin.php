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
    <?php foreach ($allBlocks as $def => $title) { ?>
      <li data-block-def="<?php echo $def ?>"><span class="clean"><?php echo $title ?></span><div class="icons"></div></li>
    <?php } ?>
  </ul>
</div>

<div class="col3">
  <h2>Block Group Setup: <?php echo $groupName ?></h2>
  <ul id="groupBlockList" class="sortable">
    <?php foreach ($blocks as $block) { ?>
      <li data-block-def="<?php echo $block->getBlockId().'@'.$block->getDefinition() ?>">
        <span><?php echo $block->getName() ?></span>
        <div class="icons">
          <!-- TODO: icons -->
          <span class="ui-icon ui-icon-circle-close"></span>
        </div>
      </li>
    <?php } ?>
  </ul>
</div>

<script>
$(function() {
  $("#groupBlockList").sortable({
    receive: function(evt, ui) {
      var span = $('#groupBlockList span.clean');
      var iconContainer = $('#groupBlockList span.clean + div.icons');

      var throbber = '<span class="throbber"></span>';
      iconContainer.html(throbber);
      span.removeClass('clean');

      // get def
      var def = ui.item[0].getAttribute('data-block-def');

      // TODO
      var groupName = '<?php echo $groupName ?>';
      //var data = '{"groupName":"'+groupName+'", "def":"'+def+'"}';

      var blocks = new Array();
      $('#groupBlockList li').each(function(index) {
        blocks.push($(this).attr('data-block-def'));
      });
      var groupBlockList = '["'+blocks.join('","')+'"]';

      var data = '{"groupName":"'+groupName+'", "groupBlockList":'+groupBlockList+'}';
      ZenMagick.rpc('block_group_admin', 'addBlockToGroup', data, {
        success: function(result) {
          // grab from the receiving list
          var icons = '';
          // TODO: if has options
          icons += '<span class="ui-icon ui-icon-wrench"></span>';
          icons += '<span class="ui-icon ui-icon-circle-close"></span>';
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
        }
      });
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
