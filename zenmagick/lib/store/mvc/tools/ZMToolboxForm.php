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
 * Form related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.store.mvc.tools
 * @version $Id$
 */
class ZMToolboxForm extends ZMToolboxTool {

    /**
     * Convenience method that will generate the JavaScript validation rules and
     * include the generic validation code.
     *
     * @param string id The id of the form to validate (the <code>ZMRuleSet</code> name).
     */
    public function insertJSValidation($id) {
        echo ZMValidator::instance()->toJSString($id);
        // inline JS to allow PHP
        include_once Runtime::getTheme()->themeFile("validation.js");
    }

    /**
     * Create a HTML <code>form</code> tag.
     *
     * <p>The mother of all form methods.</p>
     *
     * <p>Parameter (<code>$params</code>) will be added as hidden form fields, wrapped in a <em>&lt;div&gt;</em> tag.</p>
     *
     * <p>The default method value is <em>post</em>.</p>
     *
     * <p>This method will also add JavaScript validation code if the following conditions are met:</p>
     * <ol>
     *  <li>The setting <em>isAutoJSValidation</em> is set to <code>true</code></li>
     *  <li>A form id has been provided via <code>$attr</code>, as that will be used to lookup the validation rules.</li>
     * </ol>
     *
     * <p>Default attributes are:</p>
     * <ul>
     *  <li>method - <em>post</em></li>
     *  <li>onsubmit - <em>return validate(this);</em> (This will be applied only if the <em>id</em> attribute is also set)</li>
     * </ul>
     *
     * <p>To remove any default attributes, set a value of <code>null</code> in the <code>$attr</code> parameter.</p>
     *
     * <p>All attribute names are expected in lower case.</p>
     * 
     * @param string page The action page name.
     * @param string params Query string style parameter.
     * @param boolean secure Flag indicating whether to create a secure or non secure action URL; default is <code>true</code>.
     * @param array attr Optional HTML attribute map; default is <code>null</code>.
     * @return string A HTML form tag plus optional hidden form fields.
     */
    public function open($page=null, $params='', $secure=true, $attr=null) {
        $defaults = array('method' => 'post', 'onsubmit' => 'return validate(this);');
        $hasId = isset($attr['id']);
        $hasOnsubmit = isset($attr['onsubmit']);
        if (null === $attr) {
            $attr = $defaults;
        } else {
            $attr = array_merge($defaults, $attr);
        }
        // this will allow custom onsubmit code even without id
        if (!$hasId && !$hasOnsubmit) {
            unset($attr['onsubmit']);
        }

        // set action attr
        if (null !== $page && false !== strpos($page, '://')) {
            $attr['action'] = $page;
        } else {
            $attr['action'] = $this->getToolbox()->net->url($page, '', $secure);
        }

        // parse params
        parse_str($params, $hidden);
        // set best ZM_PAGE_KEY value
        if (!isset($hidden[ZM_PAGE_KEY])) {
            $page = null === $page ? $this->getRequest()->getRequestId() : $page;
            if (null !== $page) {
                $hidden[ZM_PAGE_KEY] = $page;
            }
        }

        // add session token if configured
        if ($hasId && 'post' == strtolower($attr['method']) && ZMLangUtils::inArray($attr['id'], ZMSettings::get('zenmagick.mvc.html.tokenSecuredForms'))) {
            $hidden[ZMRequest::SESSION_TOKEN_NAME] = $this->getRequest()->getSession()->getToken();
        }

        ob_start();

        // create JS validation code if all go
        if ($hasId && ZMValidator::instance()->hasRuleSet($attr['id']) && ZMSettings::get('isAutoJSValidation')) {
            $this->insertJSValidation($attr['id']);
        }

        echo '<form';
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                echo ' '.$name.'="'.$value.'"';
            }
        }
        echo '>';

        // add hidden form fields if any params set
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        if (0 < count($hidden)) {
            echo '<div>';
            foreach ($hidden as $name => $value) {
                echo '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$slash.'>';
            }
            echo '</div>';
        }

        $html = ob_get_clean();
        return $html;
    }

    /**
     * Convenience function to open a form to add a given product to the shopping cart.
     *
     * <p>The calling page is responsible for adding a submit button and a closing <code>&lt;form&gt;</code>
     * tag.</p>
     * 
     * @param int productId The product (id) to add.
     * @param int quantity Optional quantity; default to 0 which means that the card_quantity field will <strong>not</strong> be added
     * @param array attr Optional HTML attribute map; default is an empty array.
     * @return string A HTML form to add the given productId to the shopping cart.
     */
    public function addProduct($productId, $quantity=0, $attr=array()) {
        $params = 'action=add_product&products_id='.$productId;
        if (0 < $quantity) {
            $params .= '&cart_quantity='.$quantity;
        }
        // merge with defaults
        $attr = array_merge(array('enctype' => 'multipart/form-data', 'onsubmit' => null), $attr);

        // make multipart in case there are uploads
        return $this->open(null, $params, true, $attr);
    }

    /**
     * Create all required hidden form fields for a given shoppin cart item.
     *
     * @param ZMShoppingCartItem item The shopping cart item.
     * @return string HTML form to add a given productId to the shopping cart.
     */
    public function hiddenCartFields($item) {
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        $html = '<input type="hidden" name="products_id[]" value="' . $item->getId() . '"'.$slash.'>';
        if ($item->hasAttributes()) {
            foreach ($item->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $attributeValue) {
                    $html .= '<input type="hidden" name="id[' . $item->getId() . '][' . $attribute->getId() . ']" value="' . 
                      $attributeValue->getId() . '"'.$slash.'>';
                }
            }
        }

        return $html;
    }

    /**
     * Create size and maxlength attributes for input fields.
     *
     * @param string table The table name.
     * @param string col The column name.
     * @param int max The size attribute; default is <em>40</em>; use <code>0</code> to prevent a <em>size</em> attribute.
     * @return string The attributes.
     */
    public function fieldLength($table, $col, $max=40) {
        //TODO: convert from col to form field/model property
        $length = ZMTemplateManager::instance()->getFieldLength($table, $col);
        $html = '';
        switch (true) {
            case ($length > $max):
                $html = 'size="' . ($max+1) . '" maxlength="' . $length . '"';
                break;
            case (0 == $max):
                $html = '" maxlength="' . $length . '"';
                break;
            default:
                $html = 'size="' . ($length+1) . '" maxlength="' . $length . '"';
                break;
        }

        return $html;
    }

    /**
     * Makes a checkbox or radio button checked.
     *
     * @param boolean setting The actual value.
     * @param boolean value The value for this radio button; default is <code>true</code>.
     * @param boolean default The default state; default is <code>false</code>.
     */
    public function checked($setting, $value=true, $default=false) {
        if ($setting === $value || ($default && !isset($setting))) {
            echo ZMSettings::get('zenmagick.mvc.html.xhtml') ? ' checked="checked"' : ' checked';
        }
    }

    /**
     * Create a id/name pair based select box.
     *
     * <p>Helper function that can create a HTML <code>&lt;select&gt;</code> tag from 
     * any array that contains class instances that provide <code>getId()</code> and
     * <code>getName()</code> getter methods.</p>
     *
     * <p>Please note that there are two special attribute keys that can be used to control
     * the method names used to populate option value and text.</p>
     *
     * <p>Default attributes are:</p>
     * <ul>
     *  <li>id - <em>$name</em></li>
     *  <li>name - <em>$name</em></li>
     *  <li>size - <em>1</em></li>
     *  <li>oValue - <em>getId</em></li>
     *  <li>oText - <em>getName</em></li>
     * </ul>
     *
     * @param string name The name.
     * @param array list A list of options.
     * @param array attr Optional HTML attribute map; default is <code>null</code>.
     * @param string selectedId Value of option to select; default is <code>null</code>.
     * @return string Complete HTML <code>&lt;select&gt;</code> tag.
     */
    public function idpSelect($name, $list, $selectedId=null, $attr=array()) {
        $defaults = array('id' => $name, 'name' => $name, 'size' => 1, 'oValue' => 'getId', 'oText' => 'getName');
        if (null === $attr) {
            $attr = $defaults;
        } else {
            $attr = array_merge($defaults, $attr);
        }
        $oValue = $attr['oValue'];
        $oText = $attr['oText'];

        $html = '<select';
        foreach ($attr as $name => $value) {
            if (null !== $value && 'oValue' != $name && 'oText' != $name) {
                $html .= ' '.$name.'="'.$value.'"';
            }
        }
        $html .= '>';
        foreach ($list as $item) {
            $selected = $item->$oValue() === $selectedId;
            $html .= '<option value="' . $item->$oValue() . '"';
            $html .= ($selected ? ' selected="selected"' : '');
            $html .= '>' . $item->$oText() . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    /**
     * Create a group of hidden form fields with a common name (ie. <code>someId[]</code>).
     *
     * @param string name The common name.
     * @param array values List of values.
     * @return string HTML formatted input fields of type <em>hidden</em>.
     */
    public function hiddenList($name, $values) {
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        $html = '';
        foreach ($values as $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '"'.$slash.'>';
        }

        return $html;
    }

    /**
     * Create hidden elements from the given map or query args.
     *
     * @param mixed data Either a map or query arg style string.
     */
    public function hidden($data) {
        if (!is_array($data)) {
            parse_str($data, $tmp);
            $data = $tmp;
        }
        // add hidden form fields if any params set
        $slash = ZMSettings::get('zenmagick.mvc.html.xhtml') ? '/' : '';
        if (0 < count($data)) {
            echo '<div>';
            foreach ($data as $name => $value) {
                echo '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$slash.'>';
            }
            echo '</div>';
        }
    }

}
