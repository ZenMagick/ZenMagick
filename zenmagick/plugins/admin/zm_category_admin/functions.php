<?php
/*
 * ZenMagick - Extensions for zen-cart
 * Copyright (C) 2006-2009 ZenMagick
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
     * Category admin page.
     *
     * @package org.zenmagick.plugins.zm_category_admin
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_category_admin() {
    global $zm_nav_params;

        if (null != ZMRequest::getParameter('update') && 'zm_category_admin' == ZMRequest::getParameter('fkt')) {
            // load from db to start with
            $languageId = ZMRequest::getParameter('languageId', Runtime::getLanguage()->getId());
            $category = ZMCategories::instance()->getCategoryForId(ZMRequest::getCategoryId(), $languageId);
            $category->setActive(ZMRequest::getParameter('status'), false);
            $category->setName(ZMRequest::getParameter('categoryName'));
            $category->setDescription(ZMRequest::getParameter('categoryDescription', '', false));
            $imageName = ZMRequest::getParameter('imageName');
            if (!empty($imageName)) {
                $category->setImage($imageName);
            }
            if (ZMRequest::getParameter('imageDelete')) {
                $category->setImage('');
            }
            $category->setSortOrder(ZMRequest::getParameter('sortOrder'));
            ZMCategories::instance()->updateCategory($category);
            ZMMessages::instance()->success(zm_l10n_get('Category updated'));
        }

        $plugin = ZMPlugins::instance()->getPluginForId('zm_category_admin');
        $template = file_get_contents($plugin->getPluginDir().'/views/category_admin.php');
        eval('?>'.$template);
        return new ZMPluginPage('zm_category_admin', zm_l10n_get('Category'));
    }

    /**
     * Category list page.
     *
     * @package org.zenmagick.plugins.zm_category_admin
     * @return ZMPluginPage A plugin page or <code>null</code>.
     */
    function zm_category_admin_list() {
        return new ZMPluginPage('zm_category_admin_list', zm_l10n_get('Products'), zm_product_resultlist('fkt=zm_category_admin_list'));
    }

?>
