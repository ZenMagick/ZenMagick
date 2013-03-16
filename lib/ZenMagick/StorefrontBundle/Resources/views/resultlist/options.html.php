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

<?php if ($resultList->hasFilters() || $resultList->hasSorters()) { $hasOptions = false; ?>
    <?php echo $form->open(null, null, false, array('method'=>'get','class'=>'ropt','onsubmit'=>null)) ?>
        <?php if ($resultList->hasFilters()) { ?>
            <div class="rlf">
                <?php foreach ($resultList->getFilters() as $filter) { if (!$filter->isAvailable()) { continue; } $hasOptions = true; ?>
                    <?php /* if multi select do not auto submit */ ?>
                    <?php $opts = $filter->isMultiSelection() ? ' size="3" multiple="multiple"' : ' onchange="this.form.submit()"'; ?>
                    <select id="<?php echo str_replace('[]', '', $filter->getId()) ?>" name="<?php echo $filter->getId() ?>"<?php echo $opts ?>>
                        <option value=""><?php _vzm("Filter by '%s' ...", $filter->getName()) ?></option>
                        <?php foreach ($filter->getOptions() as $option) { ?>
                            <?php $selected = $option->isActive() ? ' selected="selected"' : ''; ?>
                            <option value="<?php echo $option->getId() ?>"<?php echo $selected ?>><?php echo $option->getName() ?></option>
                        <?php } ?>
                    </select>
                <?php } ?>
            </div>
        <?php } ?>
        <?php if ($resultList->hasSorters()) { $hasOptions = true; ?>
            <div class="rls">
                <?php if ($view['request']->getParameter('keywords')) { ?>
                    <input type="hidden" name="keywords" value="<?php echo $view['request']->getParameter('keywords') ?>" />
                <?php } ?>
                <input type="hidden" name="page" value="<?php echo $resultList->getPageNumber() ?>" />
                <?php if ($view['request']->getParameter('cPath')) { ?>
                    <input type="hidden" name="cPath" value="<?php echo $view['request']->getParameter('cPath') ?>" />
                <?php } elseif ($view['request']->getParameter('manufacturers_id')) { ?>
                    <input type="hidden" name="manufacturers_id" value="<?php echo $view['request']->getParameter('manufacturers_id') ?>" />
                <?php } ?>

                <select id="sort" name="sort_id" onchange="this.form.submit()">
                    <option value=""><?php _vzm("Sort by ...") ?></option>
                    <?php foreach ($resultList->getSorters() as $sorter) { ?>
                        <?php foreach ($sorter->getOptions() as $option) { ?>
                            <?php $selected = $option->isActive() ? ' selected="selected"' : ''; ?>
                            <?php $indicator = $option->isActive() ? ($option->isDecending() ? ' (-)' : ' (+)') : ''; ?>
                            <?php $id = $option->isActive() ? $option->getReverseId() : $option->getId(); ?>
                            <option value="<?php echo $id ?>"<?php echo $selected ?>><?php echo $option->getName().$indicator ?></option>
                        <?php } ?>
                    <?php } ?>
                </select>
            </div>
        <?php } ?>
        <div>
          <?php if ($hasOptions) { ?><input type="submit" class="btn" value="<?php _vzm("Sort / Reverse / Filter") ?>" /><?php } else { ?>&nbsp;<?php } ?>
        </div>
    </form>
<?php } ?>
