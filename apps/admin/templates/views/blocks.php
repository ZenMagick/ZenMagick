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
  <?php foreach ($blocks as $def => $title) { ?>
    <?php echo $title."<BR>" ?>
  <?php } ?>
</div>
<div class="col3" style="float:left;width:32%;border:1px solid gray;padding:5px;margin:1px;">
  <h2>Block Group Setup</h2>
</div>
<div class="col3" style="float:left;width:32%;border:1px solid gray;padding:5px;margin:1px;">
  <h2>Block Groups</h2>
  <?php foreach ($blockGroups as $groupId) { ?>
    <form action="<?php echo $admin2->url() ?>" method="POST"><?php echo $groupId ?><input type="hidden" name="groupId" value="<?php echo $groupId ?>"><input type="hidden" name="action" value="removeGroup"><input type="submit" value="Remove"></form>
  <?php } ?>
  <form action="<?php echo $admin2->url() ?>" method="POST">
    <h3>Create group</h3>
    <input type="text" id="groupId" name="groupId" value="">
    <input type="hidden" name="action" value="addGroup">
    <input type="submit" value="Add">
  </form>
</div>
<br clear="left">
