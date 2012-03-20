<?php
/*
 * ZenMagick - Smart e-commerce
 * Copyright (C) 2006-2012 zenmagick.org
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
namespace zenmagick\apps\store\bundles\ZenCartBundle\controller;

use zenmagick\base\Runtime;

/**
 * ZenCart admin controller
 *
 * @author Johnny Robeson
 * @todo <johnny> we could try to untangle GET/POST mess, but is it really worth it?
 */
class AdminController extends \ZMController {
    /**
     * {@inheritDoc}
     */
    public function processGet($request) {
        // @todo remove once we rely on ZenCart 1.5.0
        if ($request->getMethod() == 'GET') { // from init_general_funcs
            foreach ($request->getParameterMap() as $k =>$v) {
                $request->setParameter($k, strip_tags($v));
            }
        }
        $session = $request->getSession();

        if (null == $session->getValue('securityToken')) {
            $session->setValue('securityToken', $session->getToken());
        }

        $language = $request->getSelectedLanguage();
        $session->setValue('language', $language->getDirectory());
        $session->setValue('languages_id', $language->getId());
        $session->setValue('languages_code', $language->getCode());

        // strangely whos_online is the only user. @todo test ZM version of whos_online
        $session->setValue('currency', Runtime::getSettings()->get('defaultCurrency'));

        if (null == $session->getValue('selected_box')) {
            $session->setValue('selected_box', 'configuration');
        }

        $selectedBox = $request->getParameter('selected_box');
        if (null != $selectedBox) {
            $session->setValue('selected_box', $selectedBox);
        }

        // @todo add option to store data in $_SESSION for zc admin too so the values can be used bidirectionally
        $_SESSION = $session->getData();

        $cPath = (string)$request->getCategoryPath();
        $current_category_id = $request->getCategoryId();
        $cPath_array = $request->getCategoryPathArray();

        $tpl = compact('current_category_id', 'cPath', 'cPath_array');
        $view = $this->findView('zc_admin', $tpl);
        // no layout for invoice/packaging slip
        if (in_array($request->getRequestId(), Runtime::getSettings()->get('apps.store.zencart.skipLayout', array()))) {
            $view->setLayout(null);
        }
        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function processPost($request) {
        if (!$this->validateSecurityToken($request)) {
            $this->messageService->error(_zm('Security token validation failed'));
            $request->redirect($_SERVER['HTTP_REFERER']);
        }
        return $this->processGet($request);
    }

    /**
     * Implementation of ZenCart's init_session securityToken checking code
     *
     * Most of this code is only useful for 1.3.9 and not 1.5.0
     *
     * @todo add option to change the token name from stoken to securityToken for these pages?
     *       or s/securityToken/stoken for admin pages. This probably won't work unless we also
     *       patch zen_draw_form to use stoken
     * @todo require 1.5.0? we could drop all of thise code if we implemented the above
     * @todo should we dynamically add to tokenSecuredForms instead and let ZMRequest handle it?
     */
    public function validateSecurityToken($request) {
        $action = $request->getParameter('action', '');
        $valid = true; // yuck. need 1.5.0 or all these options implemented ourselves
        if (in_array($action, array('copy_options_values', 'update_options_values', 'update_value', 'add_product_option_values', 'copy_options_values_one_to_another_options_id', 'delete_options_values_of_option_name', 'copy_options_values_one_to_another', 'copy_categories_products_to_another_category_linked', 'remove_categories_products_to_another_category_linked', 'reset_categories_products_to_another_category_master', 'update_counter', 'update_orders_id', 'locate_configuration_key', 'locate_configuration', 'update_categories_attributes', 'update_product', 'locate_configuration', 'locate_function', 'locate_class', 'locate_template', 'locate_all_files', 'add_product', 'add_category', 'update_product_attribute', 'add_product_attributes', 'update_attributes_copy_to_category', 'update_attributes_copy_to_product', 'delete_option_name_values','delete_all_attributes', 'save', 'layout_save', 'update', 'update_sort_order', 'update_confirm', 'copyconfirm', 'deleteconfirm', 'insert', 'move_category_confirm', 'delete_category_confirm', 'update_category_meta_tags', 'insert_category' ))) {
            if (!in_array($request->getRequestId(), array('products_price_manager', 'option_name', 'currencies', 'languages', 'specials', 'featured', 'salemaker'))) {
                $valid = false;
                if (null != ($token = $request->getParameter('securityToken'))) {
                    $valid = $request->getSession()->getToken() == $token;
                }
            }
        }
        return $valid;
    }
}
