<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
 *
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
<?php $view->extend('StorefrontBundle::default_layout.html.php'); ?>
<?php $crumbtrail->addCrumb(_zm('Advanced Search')) ?>
<?php echo $form->open('search', '', false, array('method' => 'get', 'id' => 'searchCriteria')) ?>
    <fieldset id="term">
        <legend><?php _vzm("Search For..."); ?></legend>
        <div id="help">
            <a href="javascript:popupWindow('<?php echo $view['router']->generate('popup_search_help') ?>')"><?php _vzm("Search Help [?]")?></a></div>
        <div>
            <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
            <input type="text" id="askeyword" name="keywords" value="<?php echo $view->escape($searchCriteria->getKeywords(KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
            <?php $checked = $searchCriteria->isIncludeDescription() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="includeDescription" name="includeDescription" value="1" <?php echo $checked?>/>
            <label class="checkboxLabel" for="includeDescription"><?php _vzm("Search in product descriptions"); ?></label>
        </div>
    </fieldset>

    <fieldset id="asfilter">
        <legend><?php _vzm("Restrict Search By...")?></legend>
        <fieldset>
            <legend><?php _vzm("Category")?></legend>
            <?php $categories = $container->get('categoryService')->getCategories($session->getLanguageId()); ?>
            <?php echo $form->idpSelect('categoryId', array_merge(array(new ZMIdNamePair("", _zm("All Categories"))), $categories), $searchCriteria->getCategoryId()) ?>
            <?php $checked = $searchCriteria->isIncludeSubcategories() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="includeSubcategories" name="includeSubcategories" value="1" <?php echo $checked?>/>
            <label for="includeSubcategories"><?php _vzm("Include subcategories"); ?></label>
        </fieldset>

        <fieldset>
            <legend><?php _vzm("Manufacturer"); ?></legend>
            <?php $manufacturers = $container->get('manufacturerService')->getManufacturers($session->getLanguageId()); ?>
            <?php echo $form->idpSelect('manufacturerId', array_merge(array(new ZMIdNamePair("", _zm("All Manufacturers"))), $manufacturers), $searchCriteria->getManufacturerId()) ?>
        </fieldset>

        <fieldset>
            <legend><?php _vzm("Price Range"); ?></legend>
            <input type="text" id="priceFrom" name="priceFrom" value="<?php echo $view->escape($searchCriteria->getPriceFrom()) ?>" />
            <input type="text" id="priceTo" name="priceTo" value="<?php echo $view->escape($searchCriteria->getPriceTo()) ?>" />
        </fieldset>

        <fieldset>
            <legend><?php _vzm("Date"); ?></legend>
            <?php $defaultDateValue = $locale->getFormat('date', 'short-ui-format'); ?>
            <?php $onfocus = "if(this.value=='" . $defaultDateValue . "') this.value='';" ?>
            <input type="text" id="dateFrom" name="dateFrom" value="<?php echo $view->escape($searchCriteria->getDateFrom($defaultDateValue)) ?>" onfocus="<?php echo $onfocus ?>" />
            <input type="text" id="dateTo" name="dateTo" value="<?php echo $view->escape($searchCriteria->getDateTo($defaultDateValue)) ?>" onfocus="<?php echo $onfocus ?>" />
        </fieldset>
    </fieldset>

    <div class="btn"><input type="submit" class="btn" value="<?php _vzm("Search") ?>" /></div>
</form>
