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

    /**
     * Quick edit page.
     *
     * @package org.zenmagick.plugins.zm_quick_edit
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_quick_edit_admin() {
    global $zm_quick_edit, $zm_nav_params;

        if (ZMSettings::get('isLegacyAPI')) { eval(zm_globals()); }
        $template = file_get_contents($zm_quick_edit->getPluginDir().'views/quick_edit_admin.php');
        eval('?>'.$template);

        return new ZMPluginPage('zm_quick_edit_admin', zm_l10n_get('Quick Edit'));
    }


    /**
     * Quick edit form element function.
     *
     * @package org.zenmagick.plugins.zm_quick_edit
     * @return string Checkbox HTML.
     */
    function zm_quick_edit_checkbox_field($field, $id, $value, $product) {
        return '<input type="checkbox" name="'.$id.'" value="1"'.($value ? ' checked' : '').'>';
    }

    /**
     * Quick edit form element function.
     *
     * @package org.zenmagick.plugins.zm_quick_edit
     * @return string Dropdown HTML.
     */
    function zm_quick_edit_dropdown_field($field, $id, $value, $product, $options) {
        $html = '<select name="'.$id.'">';
        foreach ($options as $option) {
            $html .= '<option value="'.$option->getId().'"'.($value==$option->getId() ? ' selected' : '').'>'.$option->getName().'</option>';
        }
        $html .= '</select>';
        return $html;
    }

    /**
     * Quick edit form element function.
     *
     * @package org.zenmagick.plugins.zm_quick_edit
     * @return string Manufacturer id dropdown HTML.
     */
    function zm_quick_edit_manufacturer_id($field, $id, $value, $product) {
        $manufacturers = ZMManufacturers::instance()->getManufacturers();
        $options = array_merge(array(ZMLoader::make("IdNamePair", "", " --- ")), $manufacturers);
        return zm_quick_edit_dropdown_field($field, $id, $value, $product, $options);
    }

    /**
     * Quick edit form element function.
     *
     * <p>This is the default function used if none specified.</p>
     *
     * @package org.zenmagick.plugins.zm_quick_edit
     * @return string Input HTML.
     */
    function zm_quick_edit_input_field($field, $id, $value, $product) {
        return '<input type="text" name="'.$id.'" value="'.$value.'" size="'.$field['size'].'">';
    }


?>
