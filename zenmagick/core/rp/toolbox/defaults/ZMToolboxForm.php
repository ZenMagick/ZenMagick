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
 */
?>
<?php


/**
 * Form related functions.
 *
 * @author DerManoMann
 * @package org.zenmagick.rp.toolbox.defaults
 * @version $Id$
 */
class ZMToolboxForm extends ZMObject {

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
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A HTML form tag plus optional hidden form fields.
     */
    public function open($page=null, $params='', $secure=true, $attr=null, $echo=ZM_ECHO_DEFAULT) {
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
            $attr['action'] = ZMToolbox::instance()->net->url($page, '', $secure, false);
        }

        // parse params
        parse_str($params, $hidden);
        // set best main_page value
        if (!isset($hidden['main_page'])) {
            $page = null === $page ? ZMRequest::getPageName() : $page;
            if (null !== $page) {
                $hidden['main_page'] = $page;
            }
        }

        // add session token if configured
        if ($hasId && 'post' == strtolower($attr['method']) && ZMTools::inArray($attr['id'], ZMSettings::get('tokenSecuredForms'))) {
            $hidden[ZM_SESSION_TOKEN_NAME] = ZMRequest::getSession()->getToken();
        }

        ob_start();

        // create JS validation code if all go
        if ($hasId && ZMValidator::instance()->hasRuleSet($attr['id']) && ZMSettings::get('isAutoJSValidation')) {
            ZMValidator::instance()->insertJSValidation($attr['id']);
        }

        echo '<form';
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                echo ' '.$name.'="'.$value.'"';
            }
        }
        echo '>';

        // add hidden form fields if any params set
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        if (0 < count($hidden)) {
            echo '<div>';
            foreach ($hidden as $name => $value) {
                echo '<input type="hidden" name="'.$name.'" value="'.$value.'"'.$slash.'>';
            }
            echo '</div>';
        }

        $html = ob_get_clean();

        if ($echo) echo $html;
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
     * @param boolean echo If <code>true</code>, the URI will be echo'ed as well as returned.
     * @return string A HTML form to add the given productId to the shopping cart.
     */
    public function addProduct($productId, $quantity=0, $echo=ZM_ECHO_DEFAULT) {
        $params = 'action=add_product&products_id='.$productId;
        if (0 < $quantity) {
            $param .= 'cart_quantity='.$quantity;
        }

        // make multipart in case there are uploads
        return $this->open(null, $params, true, array('enctype' => 'multipart/form-data', 'onsubmit' => null), $echo);
    }

    /**
     * Create all required hidden form fields for a given shoppin cart item.
     *
     * @param ZMShoppingCartItem item The shopping cart item.
     * @param boolean echo If <code>true</code>, the HTML will be echo'ed as well as returned.
     * @return string HTML form to add a given productId to the shopping cart.
     */
    public function hiddenCartFields($item, $echo=ZM_ECHO_DEFAULT) {
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        $html = '<input type="hidden" name="products_id[]" value="' . $item->getId() . '"'.$slash.'>';
        if ($item->hasAttributes()) {
            foreach ($item->getAttributes() as $attribute) {
                foreach ($attribute->getValues() as $attributeValue) {
                    $html .= '<input type="hidden" name="id[' . $item->getId() . '][' . $attribute->getId() . ']" value="' . 
                      $attributeValue->getId() . '"'.$slash.'>';
                }
            }
        }

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Create size and maxlength attributes for input fields.
     *
     * @param string table The table name.
     * @param string col The column name.
     * @param int max The size attribute; default is <em>40</em>; use <code>0</code> to prevent a <em>size</em> attribute.
     * @param boolean echo If <code>true</code>, the attributes will be echo'ed as well as returned.
     * @return string The attributes.
     */
    public function fieldLength($table, $col, $max=40, $echo=ZM_ECHO_DEFAULT) {
        //TODO: convert from col to form field/model property
        $length = ZMLayout::instance()->getFieldLength($table, $col);
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

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Makes a checkbox or radio button checked.
     *
     * @param mixed setting The actual value.
     * @param mixed value The value for this radio button; default is <code>true</code>.
     * @param boolean default The default state; default is <code>false</code>.
     */
    public function checked($setting, $value=true, $default=false) {
        if ($setting == $value || ($default && empty($value))) {
            echo ZMSettings::get('isXHTML') ? ' checked="checked"' : ' checked';
        }
    }

    /**
     * Create a id/name pair based select box.
     *
     * <p>Helper function that can create a HTML <code>&lt;select&gt;</code> tag from 
     * any array that contains class instances that provide <code>getId()</code> and
     * <code>getName()</code> getter methods.</p>
     *
     * <p>Default attributes are:</p>
     * <ul>
     *  <li>id - <em>$name</em></li>
     *  <li>name - <em>$name</em></li>
     *  <li>size - <em>1</em></li>
     * </ul>
     *
     * @param string name The name.
     * @param array list A list of options.
     * @param array attr Optional HTML attribute map; default is <code>null</code>.
     * @param string selectedId Value of option to select; default is <code>null</code>.
     * @param boolean echo If <code>true</code>, the HTML will be echo'ed as well as returned.
     * @return string Complete HTML <code>&lt;select&gt;</code> tag.
     */
    public function idpSelect($name, $list, $selectedId=null, $attr=array(), $echo=ZM_ECHO_DEFAULT) {
        $defaults = array('id' => $name, 'name' => $name, 'size' => 1);
        if (null === $attr) {
            $attr = $defaults;
        } else {
            $attr = array_merge($defaults, $attr);
        }

        $html = '<select';
        foreach ($attr as $name => $value) {
            if (null !== $value) {
                $html .= ' '.$name.'="'.$value.'"';
            }
        }
        $html .= '>';
        foreach ($list as $item) {
            $selected = $item->getId() == $selectedId;
            $html .= '<option value="' . $item->getId() . '"';
            $html .= ($selected ? ' selected="selected"' : '');
            $html .= '>' . $item->getName() . '</option>';
        }
        $html .= '</select>';

        if ($echo) echo $html;
        return $html;
    }

    /**
     * Create a group of hidden form fields with a common name (ie. <code>someId[]</code>).
     *
     * @param string name The common name.
     * @param array values List of values.
     * @param boolean echo If <code>true</code>, the HTML will be echo'ed as well as returned.
     * @return string HTML formatted input fields of type <em>hidden</em>.
     */
    public function hiddenList($name, $values, $echo=ZM_ECHO_DEFAULT) {
        $slash = ZMSettings::get('isXHTML') ? '/' : '';
        $html = '';
        foreach ($values as $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '"'.$slash.'>';
        }

        if ($echo) echo $html;
        return $html;
    }

}

?>
