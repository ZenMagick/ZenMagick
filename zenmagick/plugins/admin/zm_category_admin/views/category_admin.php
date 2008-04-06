<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2008 ZenMagick
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

  $currentLanguage = ZMRuntime::getLanguage();
  $selectedLanguageId = ZMRequest::getParameter('languageId', $currentLanguage->getId());

  $category = ZMCategories::instance()->getCategoryForId(ZMRequest::getCategoryId(), $selectedLanguageId);
  if (null === $category) {
      $category = ZMLoader::make("Category");
      $category->setName('** new category **');

      // set a few defaults from the default language category
      $defaultLanguage = ZMLanguages::instance()->getLanguageForCode(ZMSettings::get('defaultLanguageCode'));
      $defaultCategory = ZMCategories::instance()->getCategoryForId(ZMRequest::getCategoryId(), $defaultLanguage->getId());
      if (null != $defaultCategory) {
          // only if exist (might not be the case if category is all new)
          $category->setName($defaultCategory->getName());
          $category->setSortOrder($defaultCategory->getSortOrder());
          $category->setImage($defaultCategory->getImage());
      }
  }

?>

  <?php zm_form('', $zm_nav_params, '', 'get') ?>
    <div><input type="hidden" name="fkt" value="zm_category_admin"></div>
    <h2><?php echo $category->getName() ?> ( <select id="languageId" name="languageId" onChange="this.form.submit();">
                <?php foreach (ZMLanguages::instance()->getLanguages() as $language) { ?>
                  <?php $selected = $selectedLanguageId == $language->getId() ? ' selected="selected"' : ''; ?>
                  <option value="<?php echo $language->getId() ?>"<?php echo $selected ?>><?php echo $language->getName() ?></option>
                <?php } ?>
              </select> )</h2>
  </form>

  <?php zm_form('', $zm_nav_params, '', 'post') ?>
    <fieldset>
        <legend>Name and Description</legend>
        <label for="categoryName">Name</label>
        <input type="text" id="categoryName" name="categoryName" value="<?php echo htmlentities($category->getName()) ?>" size="30">
        <br>
        <label for="categoryDescription" style="display:block;">Description</label>
        <textarea id="categoryDescription" name="categoryDescription" rows="5" cols="80"><?php echo htmlentities($category->getDescription()) ?></textarea>
    </fieldset>

    <fieldset style="position:relative;">
        <legend>Image Options</legend>
        <div><input type="hidden" name="currentImage" value="<?php echo $category->getImage() ?>"></div>
        <?php zm_image($category->getImageInfo(), PRODUCT_IMAGE_SMALL, 'style=position:absolute;top:6px;right:30px;') ?>
        <p class="opt"><label for="categoryImage">Upload Image</label><input type="file" id="categoryImage" name="categoryImage"></p>
        <p class="opt">
          <label for="imgDir">... to directory</label><select id="imgDir" name="imgDir">
            <option value="">Main Directory</option>
            <option value="attributes/">attributes</option>
            <option value="uploads/">uploads</option>
          </select>
        </p>
        <p class="or">Or</p>
        <p class="opt"><label for="imageName">Select image on server</label><input type="text" id="imageName" name="imageName"></p>
        <p class="or">Or</p>
        <p class="opt"><input type="checkbox" id="imageDelete" name="imageDelete" value="1"> <label for="imageDelete">Clear image association</label></p>
    </fieldset>

    <fieldset>
        <legend>Other Options</legend>
        <p class="opt">
            <label for="sortOrder">Sort Order</label><input type="text" id="sortOrder" name="sortOrder" value="<?php echo $category->getSortOrder() ?>" size="4">
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
            <input type="radio" id="restrictTypeSingle" name="restrictTypeLevel" value="r"> <label for="restrictTypeAll">Category only</label>
            <input type="radio" id="restrictTypeAll" name="restrictTypeLevel" value="r"> <label for="restrictTypeAll">Include Subcategories</label>
        </p>
    </fieldset>

    <h3>Full update, move, delete, create coming ...</h3>
    <div class="btn">
        <input type="hidden" name="fkt" value="zm_category_admin">
        <input type="hidden" name="languageId" value="<?php echo $selectedLanguageId ?>">
        <?php if (0 < $category->getId()) { ?>
            <input type="submit" class="btn" name="update" value="Update">
        <?php } ?>
<!--
        <input type="submit" class="btn mod" value="Move">
        <input type="submit" class="btn del" value="Delete" onclick="return zm_user_confirm('Delete category \'<?php echo $category->getName() ?>\'?');">
-->
    </div>
</form>
