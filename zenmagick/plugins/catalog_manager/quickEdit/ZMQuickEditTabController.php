<?php
/*
 * ZenMagick - Extensions for zen-cart
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
 * @version $Id$
 */
class ZMQuickEditTabController extends ZMPluginAdminController {
    const STALE_CHECK_FIELD_PREFIX = '@_';


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct('quick_edit_tab', _zm('Quick Edit'), 'quickEdit');
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
                array('name' => 'name', 'widget' => 'TextFormWidget#title=Name&name=name&size=35'),
                array('name' => 'model', 'widget' => 'TextFormWidget#title=Model&name=model&size=14'),
                array('name' => 'image', 'widget' => 'TextFormWidget#title=Image&name=image&size=24', 'property' => 'defaultImage'),
                array('name' => 'quantity', 'widget' => 'TextFormWidget#title=Quantity&name=quantity&size=4'),
                array('name' => 'productPrice', 'widget' => 'TextFormWidget#title=Product Price&name=productPrice&size=7'),
                array('name' => 'status', 'widget' => 'TextFormWidget#title=Status&name=status&size=2')
            );
        }

        // build map of field name = property name;
        // while doing that instantiate all widgets
        $fieldMap = array();
        foreach ($fieldList as $ii => $field) {
            $widget = ZMBeanUtils::getBean($field['widget']);
            $fieldList[$ii]['widget'] = $widget;
            $fieldMap[$field['name']] = isset($field['property']) ? $field['property'] : $field['name'];
        }

        $data['fieldList'] = $fieldList;
        $data['fieldMap'] = $fieldMap;

        $categoryId = $request->getCategoryId();
        $data['categoryId'] = $categoryId;

        $productList = ZMProducts::instance()->getProductsForCategoryId($categoryId, false);
        $data['productList'] = $productList;

        return $data;
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
        $data = $this->getViewData($request);
        $fieldList = $data['fieldList'];
        $fieldMap = $data['fieldMap'];

        $productIdList = ZMProducts::instance()->getProductIdsForCategoryId($request->getCategoryId(), false);
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
                    //$_formData[$fieldMap[$field['name']]] = $request->getParameter(self::STALE_CHECK_FIELD_PREFIX.$fieldName);
                }
            }
            // load product, convert to map and compare with the submitted form data
            $product = ZMProducts::instance()->getProductForId($productId);
            $productData = ZMBeanUtils::obj2map($product, $fieldMap);
            $isUpdate = false;
            foreach ($formData as $key => $value) {
                if (array_key_exists($key, $productData) && $value != $productData[$key]) {
                    if ($_formData[$key] == $productData[$key]) {
                        $isUpdate = true;
                    } else {
                        $isUpdate = false;
                        ZMMessages::instance()->warn('Found stale data ('.$key.') for productId '.$productId. ' - skipping update');
                    }
                    break;
                }
            }
            if ($isUpdate) {
                $product = ZMBeanUtils::setAll($product, $formData);
                ZMProducts::instance()->updateProduct($product);
            }
        }    

        // need to do this to for using PluginAdminView rather than SimplePluginFormView
        return $this->findView();
    }

}
