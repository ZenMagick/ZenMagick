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
<?php


    /**
     * Create a HTML <code>form</code> tag.
     *
     * <p>The passed parameters will be added to the action URL as well as
     * hidden form fields.
     * The reason for adding to the action is the weiryd way zen-card is looking at
     * <code>$_GET</code> and <code>$_POST</code>.</p>
     * 
     * @package net.radebatz.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_form($page, $params='', $id=null, $method='post', $onsubmit=null, $echo=true) {
        return _zm_build_form($page, $params, $id, $method, false, $onsubmit, $echo);
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
     * @package net.radebatz.zenmagick.html
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param string id Optional HTML id; defaults to <code>null</code>
     * @param string method Should be either <code>get</code> or <code>post</code>. Defaults to <code>get</code>.
     * @param string onsubmit Optional submit handler for form validation; defaults to <code>null</code>
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form tag plus optional hidden form fields.
     */
    function zm_secure_form($page, $params='', $id=null, $method='post', $onsubmit=null, $echo=true) {
        return _zm_build_form($page, $params, $id, $method, true, $onsubmit, $echo);
    }

    function _zm_build_form($page, $params='', $id=null, $method='post', $secure=false, $onsubmit=null, $echo=true) {
    global $zm_request;
        $html = '';
        if (zm_starts_with($page, "http")) {
            $action = $page;
        } else {
            $action = $secure ? zm_secure_href($page, $params, false) : zm_href($page, $params, false);
        }

        // parse all params 
        parse_str($params, $query);
        $aurl = parse_url($action);
        $aparams = zm_htmlurldecode($aurl['query']);
        parse_str($aparams, $aquery);
        $query = array_merge($aquery, $query);

        $html .= '<form action="' . $action . '"';
        if (null != $onsubmit) {
            $html .= ' onsubmit="' . $onsubmit . '"';
        }
        if (null != $id) {
            $html .= ' id="' . $id . '"';
        }
        $html .= ' method="' . $method . '">';

        // add hidden stuff
        $div = false;
        if (0 < count($query)) { $html .= '<div>'; $div = true; }
        foreach ($query as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
        }
        if (!array_key_exists('main_page', $aquery)) {
            if (!$div) { $html .= '<div>'; }
            $html .= '<input type="hidden" name="main_page" value="' . (null == $page ? $zm_request->getPageName() : $page) . '" />';
            if (!$div) { $html .= '</div>'; }
        }
        if (0 < count($query)) $html .= '</div>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Convenience function to open a form to add a given product to the shopping cart.
     *
     * <p>The calling page is responsible for adding a submit buttona and a closing <code>&lt;form&gt;</code>
     * tag.</p>
     * 
     * @package net.radebatz.zenmagick.html
     * @param int productId The product (id) to add.
     * @param int quantity Optional quantity; default to 0 which means that the card_quantity field will <strong>not</strong> be added
     * @param bool echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return A HTML form to add a given productId to the shopping cart.
     */
    function zm_add_product_form($productId, $quantity=0, $echo=true) {
    global $zm_request;
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


    // do/do not echo code for a selected radio button
    function zm_radio_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && zm_is_empty($value))) {
            echo ' checked="checked"';
        }
    }

    // do/do not echo code for a selected checkbox
    function zm_checkbox_state($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && zm_is_empty($value))) {
            echo ' checked="checked"';
        }
    }


    // create all required hidden fields for a shopping cart item
    function zm_sc_product_hidden($scItem, $echo=true) {
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

    // create form id for shipping method
    function zm_shipping_id($method, $echo=true) {
        $provider = $method->getProvider();
        $id = $provider->getId() . '_' . $method->getId();

        if ($echo) echo $id;
        return $id;
    }

?>
