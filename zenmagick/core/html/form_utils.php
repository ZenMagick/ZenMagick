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
     * Create a HTML <code>form</code> tag.
     *
     * <p>The passed parameters will be added to the action URL as well as
     * hidden form fields.
     * The reason for adding to the action is the weiryd way zen-card is looking at
     * <code>$_GET</code> and <code>$_POST</code>.</p>
     * 
     * @package org.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_form($page=null, $params='', $id=null, $method='post', $onsubmit=null, $echo=ZM_ECHO_DEFAULT) {
        return _zm_build_form($page, $params, $id, $method, false, $onsubmit, '', $echo);
    }

    /**
     * Secure version of <code>zm_form</code> to create a HTML <code>form</code> tag.
     *
     * <p>The passed parameters will be added to the action URL as well as
     * hidden form fields.
     * The reason for adding to the action is the weiryd way zen-card is looking at
     * <code>$_GET</code> and <code>$_POST</code>.</p>
     *
     * <p>Query parameters in the <code>$page</code> url will be added (merged) too.</p>
     * 
     * @package org.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param string excludes Optional array/list of query parameters to be excluded from URL/hidden parameters.
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_secure_form($page=null, $params='', $id=null, $method='post', $onsubmit=null, $echo=ZM_ECHO_DEFAULT) {
        return _zm_build_form($page, $params, $id, $method, true, $onsubmit, '', $echo);
    }

    function _zm_build_form($page=null, $params='', $id=null, $method='post', $secure=false, $onsubmit=null, $excludes='', $echo=ZM_ECHO_DEFAULT) {
        $excludes = explode(',', $excludes);
        if (ZMValidator::instance()->hasRuleSet($id) && zm_setting('isAutoJSValidation')) {
            // create JS validation code
            ZMValidator::instance()->insertJSValidation($id);
        }

        $html = '';
        if (zm_starts_with($page, "http")) {
            $action = $page;
        } else {
            $action = _zm_build_href($page, '', $secure, false);
        }

        parse_str($params, $query);
        // exclude excludes
        foreach ($excludes as $exclude) {
            if (isset($query[$exclude])) {
                unset($query[$exclude]);
            }
        }

        $html .= '<form action="' . $action . '"';
        if (null != $onsubmit) {
            $html .= ' onsubmit="' . $onsubmit . '"';
        }
        if (null != $id) {
            $html .= ' id="' . $id . '"';
        }
        $html .= ' method="' . $method . '">';

        // add hidden stuff (but not to action any more)
        $div = false;
        if (0 < count($query)) { $html .= '<div>'; $div = true; }
        foreach ($query as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        if (!array_key_exists('main_page', $query)) {
            $page = null == $page ? ZMRequest::getPageName() : $page;
            if (null !== $page) {
                if (!$div) { $html .= '<div>'; $div = true; }
                $html .= '<input type="hidden" name="main_page" value="' . $page . '" />';
            }
        }
        if ($div) $html .= '</div>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Convenience function to open a form to add a given product to the shopping cart.
     *
     * <p>The calling page is responsible for adding a submit buttona and a closing <code>&lt;form&gt;</code>
     * tag.</p>
     * 
     * @package org.zenmagick.html
     * @param int productId The product (id) to add.
     * @param int quantity Optional quantity; default to 0 which means that the card_quantity field will <strong>not</strong> be added
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form to add a given productId to the shopping cart.
     */
    function zm_add_product_form($productId, $quantity=0, $echo=ZM_ECHO_DEFAULT) {
        $html = '';
        $params = 'action=add_product&products_id='.$productId;
        $html .= '<form action="' . zm_secure_href(zen_get_info_page($productId), $params, false) . '"';
        $html .= ' method="post" enctype="multipart/form-data">';
        $html .= '<div>';
        $html .= '<input type="hidden" name="action" value="add_product" />';
        $html .= '<input type="hidden" name="products_id" value="'.$productId.'" />';
        if (0 < $quantity) {
            $html .= '<input type="hidden" name="cart_quantity" value="'.$quantity.'" />';
        }
        $html .= '</div>';

        echo $html;
        return $html;
    }


    /**
     * Convenience function to create a result list options form.
     *
     * <p>The calling page is responsible for adding a submit buttona and a closing <code>&lt;form&gt;</code>
     * tag.</p>
     * 
     * @package org.zenmagick.html
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form to handle result list options.
     */
    function zm_result_list_form($echo=ZM_ECHO_DEFAULT) {
        return _zm_build_form(null, null, null, 'get', false, null, 'sort_id', $echo);
    }


    // do/do not echo code for a selected radio button
    function zm_radio_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && empty($value))) {
            echo ' checked="checked"';
        }
    }

    // do/do not echo code for a selected checkbox
    function zm_checkbox_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && empty($value))) {
            echo ' checked="checked"';
        }
    }


    // create all required hidden fields for a shopping cart item
    function zm_sc_product_hidden($scItem, $echo=ZM_ECHO_DEFAULT) {
        $html = '<input type="hidden" name="products_id[]" value="' . $scItem->getId() . '" />';
        if ($scItem->hasAttributes()) {
            foreach ($scItem->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $attributeValue) {
                    $html .= '<input type="hidden" name="id[' . $scItem->getId() . '][' . $attribute->getId() . ']" value="' . $attributeValue->getId() . '" />';
                }
            }
        }

        if ($echo) echo $html;
        return $html;
    }

    /**
     * create form id for shipping method
     * @deprecated
     */
    function zm_shipping_id($method, $echo=ZM_ECHO_DEFAULT) {
        $provider = $method->getProvider();
        $id = $provider->getId() . '_' . $method->getId();

        if ($echo) echo $id;
        return $id;
    }

?>
