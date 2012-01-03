<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2011 zenmagick.org
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
namespace zenmagick\http\widgets\form;

use zenmagick\base\Runtime;
use zenmagick\base\Toolbox;
use zenmagick\http\widgets\Widget;

/**
 * Form widget base class.
 *
 * <p>Form widgets are widgets that represent various HTML form input elements.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 * @package zenmagick.http.widgets.form
 */
abstract class FormWidget extends Widget {
    private static $NO_VAL_ATTR = array('multiple', 'readonly', 'disabled');
    private $name_;
    private $value_;
    private $attributeNames_;
    private $encode_;


    /**
     * Create new instance.
     */
    public function __construct() {
        parent::__construct();
        $this->name_ = '';
        $this->value_ = null;
        $this->attributeNames_ = array();
        $this->encode_ = true;
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
     * Enable/disable encoding of value and attributes.
     *
     * @param boolean value The new value.
     */
    public function setEncode($value) {
        $this->encode_ = Toolbox::asBoolean($value);
    }

    /**
     * Indicate whether value and attributes will be encoded or not.
     *
     * @return boolean <code>true</code> if encoding will be done.
     */
    public function isEncode() {
        return $this->encode_;
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
        $isXhtml = Runtime::getSettings()->get('zenmagick.http.html.xhtml');

        $attr = '';
        if ($addName) {
            $attr = ' name="'.$this->getName().($this->isMultiValue() ? '[]' : '').'"';
        }

        foreach ($this->properties_ as $name => $value) {
            if (in_array($name, $this->attributeNames_)) {
                if (in_array($name, self::$NO_VAL_ATTR)) {
                    if (Toolbox::asBoolean($this->get($name))) {
                        // only add if true
                        if ($isXhtml) {
                            $attr .= ' '.$name.'="'.$name.'"';
                        } else {
                            $attr .= ' '.$name;
                        }
                    }
                } else {
                    $value = $this->encode_ ? \ZMHtmlUtils::encode($value) : $value;
                    $attr .= ' '.$name.'="'.$value.'"';
                }
            }
        }

        if ($addValue) {
            $value = $this->encode_ ? \ZMHtmlUtils::encode($this->getValue()) : $this->getValue();
            $attr .= ' value="'.$value.'"';
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
