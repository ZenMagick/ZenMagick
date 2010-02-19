<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006,2009 ZenMagick
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
 * Form widget base class.
 *
 * <p>Form widgets are widgets that represent various HTML form input elements.</p>
 *
 * @author DerManoMann
 * @package org.zenmagick.mvc.widgets.form
 * @version $Id$
 */
abstract class ZMFormWidget extends ZMWidget {
    private static $NO_VAL_ATTR = array('multiple');
    private $name_;
    private $value_;
    private $attributeNames_;


    /**
     * Create new instance.
     */
    function __construct() {
        parent::__construct();
        $this->name_ = '';
        $this->value_ = null;
        $this->attributeNames_ = array();
    }

    /**
     * Destruct instance.
     */
    function __destruct() {
        parent::__destruct();
    }


    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name) {
        $this->name_ = $name;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName() {
        return $this->name_;
    }

    /**
     * Set the value.
     *
     * @param mixed value The value.
     */
    public function setValue($value) {
        $this->value_ = $value;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function getValue() {
        return $this->value_;
    }

    /**
     * Get a stringified version of the value suitable for storing.
     *
     * @return string The value as string.
     */
    public function getStringValue() {
        return $this->getValue();
    }

    /**
     * Set the list of supported attributes.
     *
     * @param array names The attribute names.
     */
    public function setAttributeNames($names) {
        $this->attributeNames_ = $names;
    }

    /**
     * Get the list of supported attributes.
     *
     * @return array The attribute names.
     */
    public function getAttributeNames() {
        return $this->attributeNames_;
    }

    /**
     * Check if this widget allows multiple values.
     *
     * @return boolean <code>true</code> if multiple values are supported.
     */
    public function isMultiValue() {
        return false;
    }

    /**
     * Get the formatted attribute string.
     *
     * @param ZMRequest request The current request.
     * @param boolean addValue Optional flag to include/exclude the value; default is <code>true</code>.
     * @param boolean addName Optional flag to include/exclude the name; default is <code>true</code>.
     * @return string All set (and allowed) attributes as formatted HTML string.
     */
    public function getAttributeString($request, $addValue=true, $addName=true) {
        $isXhtml = ZMSettings::get('zenmagick.mvc.html.xhtml');

        $attr = '';
        if ($addName) {
            $attr = ' name="'.$this->getName().($this->isMultiValue() ? '[]' : '').'"';
        }

        $html = $request->getToolbox()->html;
        foreach ($this->properties_ as $name => $value) {
            if (in_array($name, $this->attributeNames_)) {
                if (in_array($name, self::$NO_VAL_ATTR)) {
                    if ($isXhmtl) {
                        $attr .= ' '.$name.'="'.$name.'"';
                    } else {
                        $selected = ' selected';
                        $attr .= ' '.$name;
                    }
                } else {
                    $attr .= ' '.$name.'="'.$html->encode($value).'"';
                }
            }
        }

        if ($addValue) {
            $attr .= ' value="'.$html->encode($this->getValue()).'"';
        }

        return $attr;
    }


    /**
     * Compare the given value with the widget value.
     *
     * @param string value A string value.
     * @return boolean <code>true</code> if the given value evaluates to the
     *  same value as the widget value.
     */
    public function compare($value) {
        return $value == $this->getValue();
    }

}
