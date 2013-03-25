<?php
/*
 * ZenMagick - Another PHP framework.
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
namespace ZenMagick\Http\Widgets\Form;

use ZenMagick\Base\Runtime;
use ZenMagick\Base\Toolbox;
use ZenMagick\Http\Widgets\Widget;

/**
 * Form widget base class.
 *
 * <p>Form widgets are widgets that represent various HTML form input elements.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
abstract class FormWidget extends Widget
{
    protected static $NO_VAL_ATTR = array('multiple', 'readonly', 'disabled');
    protected $classes;
    protected $name;
    protected $value;
    protected $attributeNames;
    protected $encode;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->name = '';
        $this->classes = array();
        $this->value = null;
        $this->setAttributeNames(array('id'));
        $this->encode = true;
    }

    /**
     * Set the classes.
     *
     * @param string class The classes.
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
    }

    /**
     * Add a class or classes
     *
     * @param string class The classes.
     */
    public function addClasses($classes)
    {
        $this->classes = array_unique(array_merge($this->classes, (array) $classes));
    }

    /**
     * Get the classes.
     *
     * @return array The classes.
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * Get the class string (space seperated)
     *
     * @return string class.
     */
    public function getClass()
    {
        return implode(' ', $this->classes);
    }

    /**
     * Set the name.
     *
     * @param string name The name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name.
     *
     * @return string The name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value.
     *
     * @param mixed value The value.
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * Get the value.
     *
     * @return mixed The value.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Enable/disable encoding of value and attributes.
     *
     * @param boolean value The new value.
     */
    public function setEncode($value)
    {
        $this->encode = Toolbox::asBoolean($value);
    }

    /**
     * Indicate whether value and attributes will be encoded or not.
     *
     * @return boolean <code>true</code> if encoding will be done.
     */
    public function isEncode()
    {
        return $this->encode;
    }

    /**
     * Get a stringified version of the value suitable for storing.
     *
     * @return string The value as string.
     */
    public function getStringValue()
    {
        return $this->getValue();
    }

    /**
     * Set the list of supported attributes.
     *
     * @param array names The attribute names.
     */
    public function setAttributeNames($names)
    {
        $this->attributeNames = $names;
    }

    /**
     * Add to the list of supported attributes.
     *
     * @param array names The attribute names.
     */
    public function addAttributeNames($names)
    {
        $this->attributeNames = array_unique(array_merge($this->attributeNames, (array) $names));
    }

    /**
     * Get the list of supported attributes.
     *
     * @return array The attribute names.
     */
    public function getAttributeNames()
    {
        return $this->attributeNames;
    }

    /**
     * Check if this widget allows multiple values.
     *
     * @return boolean <code>true</code> if multiple values are supported.
     */
    public function isMultiValue()
    {
        return false;
    }

    /**
     * Get the formatted attribute string.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @param boolean addValue Optional flag to include/exclude the value; default is <code>true</code>.
     * @param boolean addName Optional flag to include/exclude the name; default is <code>true</code>.
     * @return string All set (and allowed) attributes as formatted HTML string.
     */
    public function getAttributeString($request, $addValue=true, $addName=true)
    {
        $html = Runtime::getContainer()->get('htmlTool');
        $attr = '';
        if ($addName) {
            $attr = ' name="'.$this->getName().($this->isMultiValue() ? '[]' : '').'"';
        }
        $class = $this->getClass();
        if (!empty($class)) {
            $attr .= ' class="'.$class.'"';
        }
        foreach ($this->getProperties() as $name => $value) {
            if (in_array($name, $this->attributeNames)) {
                if (in_array($name, self::$NO_VAL_ATTR)) {
                    if (Toolbox::asBoolean($this->get($name))) {
                        $attr .= ' '.$name.'="'.$name.'"';
                    }
                } else {
                    $value = $this->encode ? $html->encode($value) : $value;
                    $attr .= ' '.$name.'="'.$value.'"';
                }
            }
        }

        if ($addValue) {
            $value = $this->encode ? $html->encode($this->getValue()) : $this->getValue();
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
    public function compare($value)
    {
        return $value == $this->getValue();
    }

}
