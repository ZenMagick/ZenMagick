<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2010 zenmagick.org
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
 */
?>
<?php


/**
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.quickEdit
 */
class ZMCategoryAdminTabController extends ZMPluginAdminController {

    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('category_admin_tab', _zm('Category Admin'), 'categoryAdmin');
    }


    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView();
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $languageId = $request->getParameter('languageId', Runtime::getLanguage()->getId());
        $category = ZMCategories::instance()->getCategoryForId($request->getCategoryId(), $languageId);
        $category->setActive($request->getParameter('status'), false);
        $category->setName($request->getParameter('categoryName'));
        $category->setDescription($request->getParameter('categoryDescription', '', false));
        $imageName = $request->getParameter('imageName');
        if (!empty($imageName)) {
            $category->setImage($imageName);
        }
        if ($request->getParameter('imageDelete')) {
            $category->setImage('');
        }
        $category->setSortOrder($request->getParameter('sortOrder'));
        ZMCategories::instance()->updateCategory($category);
        ZMMessages::instance()->success(_zm('Category updated'));

        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView();
    }

}
