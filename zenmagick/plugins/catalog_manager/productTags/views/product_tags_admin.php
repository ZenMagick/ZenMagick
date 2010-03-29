<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 *
 * $Id$
 */
?>

<script>
function addTag(tag) {
  var tags = document.getElementById('productTags');
  if ('' != tags.value) {
      tags.value += ', ';
  }
  tags.value += tag;
}
</script>

<form action="<?php echo $toolbox->admin->url(null, $defaultUrlParams) ?>" method="POST">
    <fieldset>
        <legend>Manage Product Tags</legend>
        <p>
            <label for="productTags">Current Tags</label>
            <textarea id="productTags" name="productTags"><?php echo $html->encode(implode(', ', $productTags)) ?></textarea>
        </p>
        <p>
            <h3>All Tags</h3>
            <div>
                <?php foreach ($allTags as $tag) { ?>
                    <a href="#" onclick="addTag('<?php echo $tag ?>'); return false;"><?php echo $html->encode($tag) ?></a>
                <?php } ?>
            </div>
        </p>
    </fieldset>
    <p><input type="submit" value="Update"></p>
</form>
