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
use ZenMagick\Http\View\TemplateView;

/**
 * A select form widget.
 *
 * <p>Style can be: <em>select</em> (default) or <em>radio</em>.</p>
 *
 * @author DerManoMann <mano@zenmagick.org>
 */
class SelectFormWidget extends FormWidget
{
    private $options_;

    /**
     * Create new instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->addAttributeNames(array('size', 'multiple', 'title'));
        $this->options_ = array();
        // defaults
        $this->set('style', 'select');
    }

    /**
     * {@inheritDoc}
     */
    public function setValue($value)
    {
        if ($this->isMultiValue()) {
            $arr = @unserialize($value);
            if (is_array($arr)) {
                $value = $arr;
            }
        }
        parent::setValue($value);
    }

    /**
     * Set the multiple flag.
     *
     * @param boolean multiple New value.
     */
    public function setMultiple($multiple)
    {
        $this->set('multiple', Toolbox::asBoolean($multiple));
    }

    /**
     * {@inheritDoc}
     */
    public function isMultiValue()
    {
        return Toolbox::asBoolean($this->get('multiple'));
    }

    /**
     * Get the options map.
     *
     * @param ZenMagick\Http\Request request The current request.
     * @return array Map of value/name pairs.
     */
    public function getOptions($request)
    {
        return $this->options_;
    }

    /**
     * Add a single option.
     *
     * @param string name The option name.
     * @param string value The value; default is <code>null</code> to use the name.
     */
    public function addOption($name, $value=null)
    {
        $value = null === $value ? $name : $value;
        $this->options_[$value] = $name;
    }

    /**
     * Set the options map.
     *
     * @param mixed options Map of value/name pairs.
     */
    public function setOptions($options)
    {
        $this->options_ = Toolbox::toArray($options);
    }

    /**
     * {@inheritDoc}
     */
    public function getStringValue()
    {
        if ($this->isMultiValue()) {
            // only for multi values, to avoid serializing int values, etc...
            return serialize($this->getValue());
        }

        return parent::getStringValue();
    }

    /**
     * {@inheritDoc}
     */
    public function render($request, TemplateView $templateView)
    {
        if ($this->isMultiValue()) {
            Runtime::getLogging()->debug('multi-value: defaulting style to select');
            $this->set('style', 'select');
        }
        switch ($this->get('style')) {
            default:
                Runtime::getLogging()->debug('invalid style "'.$this->get('style').'" - using default');
            case 'select':
                return $this->renderSelect($request);
            case 'radio':
                return $this->renderRadio($request);
        }
    }

    /**
     * Render as seclect drop down.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function renderSelect($request)
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = array($values);
        }
        $html = Runtime::getContainer()->get('htmlTool');
        $output = '<select'.$this->getAttributeString($request, false).'>';
        foreach ($this->getOptions($request) as $oval => $name) {
            $selected = '';
            if (in_array($oval, $values)) {
                if (Runtime::getSettings()->get('zenmagick.http.html.xhtml')) {
                    $selected = ' selected="selected"';
                } else {
                    $selected = ' selected';
                }
            }
            $output .= '<option'.$selected.' value="'.$html->encode($oval).'">'.$html->encode($name).'</option>';
        }
        $output .= '</select>';

        return $output;
    }

    /**
     * Render as group of radio buttons.
     *
     * @param ZenMagick\Http\Request request The current request.
     */
    public function renderRadio($request)
    {
        $slash = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? '/' : '';
        $checked = Runtime::getSettings()->get('zenmagick.http.html.xhtml') ? ' checked="checked"' : ' checked';

        $values = $this->getValue();
        if (!is_array($values)) {
            $values = array($values);
        }
        $html = Runtime::getContainer()->get('htmlTool');
        $idBase = $html->encode($this->get('id'));
        if (empty($idBase)) {
            // default to name; we need this to make label work
            $idBase = $this->getName();
        }

        $value = $this->getValue();

        ob_start();
        $index = 0;
        foreach ($this->getOptions($request) as $oval => $name) {
            echo '<input type="radio" id="'.$idBase.'-'.$index.'" class="'.$this->getClass().'" name="'.$this->getName().'" value="'.$html->encode($oval).'"'.($oval==$value ? $checked : '').$slash.'>';
            echo ' <label for="'.$idBase.'-'.$index.'">'.$html->encode(_zm($name)).'</label>';
            ++$index;
        }

        return ob_get_clean();
    }

}
