<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2011 zenmagick.org
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

use zenmagick\base\Beans;

/**
 * Admin controller.
 *
 * @author DerManoMann
 * @package org.zenmagick.plugins.quickEdit
 */
class ZMQuickEditTabController extends ZMCatalogContentController {
    const STALE_CHECK_FIELD_PREFIX = '@_';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('quick_edit_tab', _zm('Quick Edit'), self::ACTIVE_CATEGORY);
    }


    /**
     * {@inheritDoc}
     */
    public function getViewData($request) {
        $data = array();

        if (null == ($fieldList = ZMSettings::get('plugins.quickEdit.fieldList', null))) {
            // use defaults
            $fieldList = array(
                // name, widget, propert is optional in case the fieldname and product proerty name do not match
                array('name' => 'name', 'widget' => 'ZMTextFormWidget#title=Name&name=name&size=35'),
                array('name' => 'model', 'widget' => 'ZMTextFormWidget#title=Model&name=model&size=14'),
                array('name' => 'image', 'widget' => 'ZMTextFormWidget#title=Image&name=image&size=24', 'property' => 'defaultImage'),
                array('name' => 'quantity', 'widget' => 'ZMTextFormWidget#title=Quantity&name=quantity&size=4'),
                array('name' => 'productPrice', 'widget' => 'ZMTextFormWidget#title=Product Price&name=productPrice&size=7'),
                array('name' => 'status', 'widget' => 'ZMTextFormWidget#title=Status&name=status&size=2')
            );
        }

        // build map of field name = property name;
        // while doing that instantiate all widgets
        $fieldMap = array();
        foreach ($fieldList as $ii => $field) {
            $widget = Beans::getBean($field['widget']);
            $fieldList[$ii]['widget'] = $widget;
            $fieldMap[$field['name']] = isset($field['property']) ? $field['property'] : $field['name'];
        }

        $data['fieldList'] = $fieldList;
        $data['fieldMap'] = $fieldMap;

        $categoryId = $request->getCategoryId();
        $data['categoryId'] = $categoryId;
        $data['category'] = $this->container->get('categoryService')->getCategoryForId($categoryId, $request->getSelectedLanguage()->getId());
        $productList = $this->container->get('productService')->getProductsForCategoryId($categoryId, false, $request->getSelectedLanguage()->getId());
        $data['productList'] = $productList;

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        $languageId = $request->getSelectedLanguage()->getId();
        $data = $this->getViewData($request);
        $fieldList = $data['fieldList'];
        $fieldMap = $data['fieldMap'];

        $productIdList = $this->container->get('productService')->getProductIdsForCategoryId($request->getCategoryId(), $languageId, false, false);
        foreach ($productIdList as $productId) {
            // build a data map for each submitted product
            $formData = array();
            // and one with the original value to compare and detect state data
            $_formData = array();
            foreach ($fieldList as $field) {
                $widget = $field['widget'];
                if ($widget instanceof ZMFormWidget) {
                    $fieldName = $field['name'].'_'.$productId;
                    // use widget to *read* the value to allow for optional conversions, etc
                    $widget->setValue($request->getParameter($fieldName, null, false));
                    $formData[$fieldMap[$field['name']]] = $widget->getStringValue();
                    $widget->setValue($request->getParameter(self::STALE_CHECK_FIELD_PREFIX.$fieldName, null, false));
                    $_formData[$fieldMap[$field['name']]] = $widget->getStringValue();
                }
            }
            // load product, convert to map and compare with the submitted form data
            $product = $this->container->get('productService')->getProductForId($productId, $languageId);
            $productData = Beans::obj2map($product, $fieldMap);
            $isUpdate = false;
            foreach ($formData as $key => $value) {
                if (array_key_exists($key, $productData) && $value != $productData[$key]) {
                    if ($_formData[$key] == $productData[$key]) {
                        $isUpdate = true;
                    } else {
                        $isUpdate = false;
                        $this->messageService->warn('Found stale data ('.$key.') for productId '.$productId. ' - skipping update');
                    }
                    break;
                }
            }
            if ($isUpdate) {
                $product = Beans::setAll($product, $formData);
                $this->container->get('productService')->updateProduct($product);
                $this->messageService->success('All changes saved');
            }
        }

        return $this->findView('catalog-redirect');
    }

}
