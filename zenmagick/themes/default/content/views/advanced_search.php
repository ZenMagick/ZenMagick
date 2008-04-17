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

<?php define('KEYWORD_DEFAULT', zm_l10n_get("enter search")); ?>
<?php zm_form(FILENAME_ADVANCED_SEARCH_RESULT, '', 'advanced_search', "get", "return validate(this);") ?>
    <fieldset id="term">
        <legend><?php zm_l10n("Search For..."); ?></legend>
        <div id="help">
            <a href="javascript:popupWindow('<?php zm_href(FILENAME_POPUP_SEARCH_HELP, false) ?>')"><?php zm_l10n("Search Help [?]")?></a></div>
        <div>
            <?php $onfocus = "if(this.value=='" . KEYWORD_DEFAULT . "') this.value='';" ?>
            <input type="text" id="askeyword" name="keyword" value="<?php $_t->html->encode($zm_search->getKeyword(KEYWORD_DEFAULT)) ?>" onfocus="<?php echo $onfocus ?>" />
            <?php $checked = $zm_search->getIncludeDescription() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="search_in_description" name="search_in_description" value="1" <?php echo $checked?>/>
            <label class="checkboxLabel" for="search_in_description"><?php zm_l10n("Search in product descriptions"); ?></label>
        </div>
    </fieldset>

    <fieldset id="asfilter">
        <legend><?php zm_l10n("Restrict Search By...")?></legend>
        <fieldset>
            <legend><?php zm_l10n("Category")?></legend>
            <?php $categories = ZMCategories::instance()->getCategories(); ?>
            <?php zm_idp_select('categories_id', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("All Categories"))), $categories), 1, $zm_search->getCategory()) ?>
            <?php $checked = $zm_search->getIncludeSubcategories() ? 'checked="checked" ' : ''; ?>
            <input type="checkbox" id="inc_subcat" name="inc_subcat" value="1" <?php echo $checked?>/>
            <label for="inc_subcat"><?php zm_l10n("Include subcategories"); ?></label>
        </fieldset>

        <fieldset>
            <legend><?php zm_l10n("Manufacturer"); ?></legend>
            <?php $manufacturers = ZMManufacturers::instance()->getManufacturers(); ?>
            <?php zm_idp_select('manufacturers_id', array_merge(array(ZMLoader::make("IdNamePair", "", zm_l10n_get("All Manufacturers"))), $manufacturers), 1, $zm_search->getManufacturer()) ?>
        </fieldset>

        <fieldset>
            <legend><?php zm_l10n("Price Range"); ?></legend>
            <input type="text" id="pfrom" name="pfrom" value="<?php $_t->html->encode($zm_search->getPriceFrom()) ?>" />
            <input type="text" id="pto" name="pto" value="<?php $_t->html->encode($zm_search->getPriceTo()) ?>" />
        </fieldset> 

        <fieldset> 
            <legend><?php zm_l10n("Date"); ?></legend>
            <?php /* UI_DATE_FORMAT is defined in the theme's i18n.php setup */ ?>
            <?php $onfocus = "if(this.value=='" . UI_DATE_FORMAT . "') this.value='';" ?>
            <input type="text" id="dfrom" name="dfrom" value="<?php $_t->html->encode($zm_search->getDateFrom(UI_DATE_FORMAT)) ?>" onfocus="<?php echo $onfocus ?>" />
            <input type="text" id="dto" name="dto" value="<?php $_t->html->encode($zm_search->getDateTo(UI_DATE_FORMAT)) ?>" onfocus="<?php echo $onfocus ?>" />
        </fieldset> 
    </fieldset> 

    <div class="btn"><input type="submit" class="btn" value="<?php zm_l10n("Search") ?>" /></div>
</form>
