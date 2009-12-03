<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 zenmagick.org
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

<?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
<?php $form->open('search', '', false, array('method' => 'get', 'id' => 'searchCriteria')) ?>
    <fieldset id="term">
        <legend><?php zm_l10n("Search For..."); ?></legend>
        <div id="help">
            <a href="javascript:popupWindow('<?php $net->url(FILENAME_POPUP_SEARCH_HELP) ?>')"><?php zm_l10n("Search Help [?]")?></a></div>
        <div>
            <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
            <input type="text" id="askeyword" name="keywords" value="<?php $html->encode($searchCriteria->getKeywords(KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
            <?php $checked = $searchCriteria->isIncludeDescription() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="includeDescription" name="includeDescription" value="1" <?php echo $checked?>/>
            <label class="checkboxLabel" for="includeDescription"><?php zm_l10n("Search in product descriptions"); ?></label>
        </div>
    </fieldset>

    <fieldset id="asfilter">
        <legend><?php zm_l10n("Restrict Search By...")?></legend>
        <fieldset>
            <legend><?php zm_l10n("Category")?></legend>
            <?php $categories = ZMCategories::instance()->getCategories(null, $session->getLanguageId()); ?>
            <?php $form->idpSelect('categoryId', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("All Categories"))), $categories), $searchCriteria->getCategoryId()) ?>
            <?php $checked = $searchCriteria->isIncludeSubcategories() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="includeSubcategories" name="includeSubcategories" value="1" <?php echo $checked?>/>
            <label for="includeSubcategories"><?php zm_l10n("Include subcategories"); ?></label>
        </fieldset>

        <fieldset>
            <legend><?php zm_l10n("Manufacturer"); ?></legend>
            <?php $manufacturers = ZMManufacturers::instance()->getManufacturers($session->getLanguageId()); ?>
            <?php $form->idpSelect('manufacturerId', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("All Manufacturers"))), $manufacturers), $searchCriteria->getManufacturerId()) ?>
        </fieldset>

        <fieldset>
            <legend><?php zm_l10n("Price Range"); ?></legend>
            <input type="text" id="priceFrom" name="priceFrom" value="<?php $html->encode($searchCriteria->getPriceFrom()) ?>" />
            <input type="text" id="priceTo" name="priceTo" value="<?php $html->encode($searchCriteria->getPriceTo()) ?>" />
        </fieldset> 

        <fieldset> 
            <legend><?php zm_l10n("Date"); ?></legend>
            <?php /* UI_DATE_FORMAT is defined in the theme's i18n.php setup */ ?>
            <?php $onfocus = "if(this.value=='" . UI_DATE_FORMAT . "') this.value='';" ?>
            <input type="text" id="dateFrom" name="dateFrom" value="<?php $html->encode($searchCriteria->getDateFrom(UI_DATE_FORMAT)) ?>" onfocus="<?php echo $onfocus ?>" />
            <input type="text" id="dateTo" name="dateTo" value="<?php $html->encode($searchCriteria->getDateTo(UI_DATE_FORMAT)) ?>" onfocus="<?php echo $onfocus ?>" />
        </fieldset> 
    </fieldset> 

    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Search") ?>" /></div>
</form>
