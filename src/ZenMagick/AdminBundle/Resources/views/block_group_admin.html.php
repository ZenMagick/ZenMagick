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
?>
<?php $view->extend('AdminBundle::default_layout.html.twig'); ?>
<?php $admin->title() ?>

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
    // TODO: do not sort when receive
    xupdate: function(evt, ui) {
      // data
      var groupName = '<?php echo $groupName ?>';
      var blocks = new Array();
      $('#groupBlockList li').each(function(index) {
        blocks.push($(this).attr('data-block-def'));
      });
      var groupBlockList = '["'+blocks.join('","')+'"]';

      // TODO: flag progress
      var data = '{"groupName":"'+groupName+'", "groupBlockList":'+groupBlockList+'}';
      ZenMagick.rpc('block_group_admin', 'reorderBlockGroup', data, {
        success: function(result) {
          // TODO: flag saved
        }
      });
    },
    receive: function(evt, ui) {
      var span = $('#groupBlockList span.clean');
      var iconContainer = $('#groupBlockList span.clean + div.icons');

      var throbber = '<span class="throbber"></span>';
      iconContainer.html(throbber);
      span.removeClass('clean');

      // data
      var groupName = '<?php echo $groupName ?>';
      var blocks = new Array();
      $('#groupBlockList li').each(function(index) {
        blocks.push($(this).attr('data-block-def'));
      });
      var groupBlockList = '["'+blocks.join('","')+'"]';

      var data = '{"groupName":"'+groupName+'", "groupBlockList":'+groupBlockList+'}';
      ZenMagick.rpc('block_group_admin', 'addBlockToGroup', data, {
        success: function(result) {
          // grab from the receiving list
          var blockId = result.data['blockId'];
          var hasOptions = result.data['options'];

          // update id
          var def = $(span).parent().attr('data-block-def');
          $(span).parent().attr('data-block-def', blockId+'@'+def);

          var icons = '';
          if (hasOptions) {
            icons += '<span class="ui-icon ui-icon-wrench"></span>';
          }
          icons += '<span class="ui-icon ui-icon-circle-close"></span>';
          iconContainer.html(icons);

          $('span.ui-icon-wrench', span.parentNode).click(function() {
            // TODO: do something useful
            alert('configure not implemented yet...');
          });

          // add close handler
          $('span.ui-icon-circle-close', span.parentNode).click(remove_block);
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

// remove block
function remove_block() {
  var li = $(this).parent().parent();
  var block = li.attr('data-block-def');
  var data = '{"groupName":"<?php echo $groupName ?>", "block":"'+block+'"}';
  var iconContainer = $('div.icons', li);
  var throbber = '<span class="throbber"></span>';
  iconContainer.html(throbber);

  ZenMagick.rpc('block_group_admin', 'removeBlockFromGroup', data, {
    success: function(result) {
      li.remove();
    }
  });
}

// add close handler
$('span.ui-icon-circle-close').click(remove_block);

</script>
