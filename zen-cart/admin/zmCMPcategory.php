<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006 ZenMagick
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

    $langNav = array(
        'english' => ZM_ADMINFN_CATALOG_MANAGER.'?view=category&amp;lang=english'.$navParams,
        'deutsch' => ZM_ADMINFN_CATALOG_MANAGER.'?view=category&amp;lang=deutsch'.$navParams
    );
    $lang = $zm_request->getRequestParameter('lang', 'english');

    $languageId = $zm_request->getLanguageId();
    if ($lang == 'deutsch') {
        $languageId = 2;
    }

    $category = $zm_categories->getCategoryForId($zm_request->getCategoryId(), $languageId);
?>

<form action="<?php echo ZM_ADMINFN_CATALOG_MANAGER ?>" method="post">
    <input type="hidden" name="view" value="category">
    <input type="hidden" name="cPath" value="<?php echo $cPath ?>">
    <input type="hidden" name="categories_id" value="<?php echo $zm_request->getCategoryId() ?>">

    <div id="lnav">
        <ul class="hnav">
            <?php foreach ($langNav as $name => $url) { ?>
                <?php if ('' != $url) { $act = $lang == $name ? ' class="act"' : ''; ?>
                    <li><a <?php echo $act ?> href="<?php echo $url ?>"><?php echo $name ?></a></li>
                <?php } ?>
            <?php } ?>
        </ul>
    </div>
    <div id="lcont">
        <label for="categoryName">Name</label>
        <input type="text" id="categoryName" name="categoryName" value="<?php echo $category->getName() ?>">
        <label for="categoryDescription">Description</label>
        <textarea id="categoryDescription" name="categoryDescription" rows="5"><?php echo $category->getDescription() ?></textarea>
    </div>

    <fieldset>
        <legend>Image Options</legend>

        <img id="cimg" src="<?php zm_image_href($category->getImage())?>">

        <p class="opt"><label for="categoryImage">Upload Image</label><input type="file" id="categoryImage" name="categoryImage"></p>
        <p class="opt">
          <label for"imageDir">... to directory</label><select id="imgDir" name="imgDir">
            <option value="">Main Directory</option>
            <option value="attributes/">attributes</option>
            <option value="uploads/">uploads</option>
          </select>
        </p>
        <p class="or">Or</p>
        <p class="opt"><label for=imageName">Select image on server</label><input type="text" id="imageName" name="imageName"></p>
        <p class="or">Or</p>
        <p class="opt"><input type="checkbox" id="imageDelete" name="imageDelete" value="1"> <label for="imageDelete">Clear image association</label></p>
    </fieldset>

    <fieldset>
        <legend>Other Options</legend>
        <p class="opt">
            <label for="sortOrder">Sort Order</label><input type="text" name="sortOrder" value="<?php echo $category->getSortOrder() ?>1" size="4">
        </p>
        <hr>
        <p class="opt">
            <label for="restrictType">Restrict Product Type</label><select id="restrictType" name="restrictType">
              <option value="">-- No Restriction --</option>
              <option value="1">Product - General</option>
              <option value="2">Product - Music</option>
              <option value="3">Document - General</option>
              <option value="4">Document - Product</option>
              <option value="5">Product - Free Shipping</option>
            </select>
        </p>
        <p class="opt">
            <input type="radio" id="restrictTypeAll" name="restrictTypeLevel" value="r"><label for="restrictTypeAll">Include Subcategories</label>
            <input type="radio" id="restrictTypeSingle" name="restrictTypeLevel" value="r"><label for="restrictTypeAll">Category only</label>
        </p>
    </fieldset>

    <div class="btn">
        <input type="submit" class="btn" value="Save">
        <input type="submit" class="btn mod" value="Move">
        <input type="submit" class="btn del" value="Delete" onclick="return zm_user_confirm('Delete category \'<?php echo $category->getName() ?>\'?');">
    </div>
</form>
