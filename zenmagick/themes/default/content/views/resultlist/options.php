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

<?php if ($zm_resultList->hasFilters() || $zm_resultList->hasSorters()) { ?>
    <?php zm_form(null, '', null, "get") ?>
        <?php if ($zm_resultList->hasFilters()) { ?>
            <div class="rlf">
                <?php foreach($zm_resultList->getFilters() as $filter) { if (!$filter->isAvailable()) continue; ?>
                    <select id="<?php echo $filter->getId() ?>" name="<?php echo $filter->getId() ?>">
                        <option value=""><?php zm_l10n("Filter by ...") ?></option>
                        <?php foreach($filter->getOptions() as $option) { ?>
                            <?php $selected = $option->isActive() ? ' selected="selected"' : ''; ?>
                            <option value="<?php echo $option->getId() ?>"<?php echo $selected ?>><?php echo $option->getName() ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($zm_resultList->hasSorters()) { ?>
            <div>
                <input type="hidden" name="page" value="<?php echo $zm_resultList->getCurrentPageNumber() ?>" />
                <?php if ($zm_request->getCategoryPath()) { ?>
                    <input type="hidden" name="cPath" value="<?php echo $zm_request->getCategoryPath() ?>" />
                <?php } else if ($zm_request->getManufacturerId()) { ?>
                    <input type="hidden" name="manufacturers_id" value="<?php echo $zm_request->getManufacturerId() ?>" />
                <?php } else if (null != $zm_request->getRequestParameter("compareId")) { ?>
                    <?php zm_hidden_list('compareId[]', $zm_request->getRequestParameter("compareId")) ?>
                <?php } ?>

                <select id="sort" name="sort" onchange="this.form.submit()">
                    <option value=""><?php zm_l10n("Sort by ...") ?></option>
                    <?php foreach($zm_resultList->getSorters() as $sorter) { ?>
                        <?php foreach($sorter->getOptions() as $option) { ?>
                            <?php $selected = $option->isActive() ? ' selected="selected"' : ''; ?>
                            <option value="<?php echo $option->getId() ?>"<?php echo $selected ?>><?php echo $option->getName() ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
                <input type="submit" class="btn" value="<?php zm_l10n("Sort / Reverse") ?>" />
            </div>
        <?php } ?>
    </form>
<?php } ?>
